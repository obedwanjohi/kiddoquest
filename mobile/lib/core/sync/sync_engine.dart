import 'dart:async';

import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:flutter/foundation.dart';

import '../db/progress_dao.dart';
import '../models/learning_event.dart';
import '../models/snapshot.dart';
import '../network/api_client.dart';
import '../network/api_exception.dart';
import 'outbox.dart';

enum SyncStatus { idle, syncing, offline, failed }

class SyncState {
  const SyncState({
    this.status = SyncStatus.idle,
    this.pending = 0,
    this.lastSuccessAt,
    this.lastError,
  });

  final SyncStatus status;
  final int pending;
  final DateTime? lastSuccessAt;
  final String? lastError;

  bool get isOffline => status == SyncStatus.offline;

  SyncState copyWith({
    SyncStatus? status,
    int? pending,
    DateTime? lastSuccessAt,
    String? lastError,
    bool clearError = false,
  }) =>
      SyncState(
        status: status ?? this.status,
        pending: pending ?? this.pending,
        lastSuccessAt: lastSuccessAt ?? this.lastSuccessAt,
        lastError: clearError ? null : (lastError ?? this.lastError),
      );
}

/// Moves the outbox to the server and brings the server's answer back.
///
/// It never blocks anything a child is doing. If it fails, it backs off and
/// tries again; the work is already safe on disk either way.
class SyncEngine extends ChangeNotifier {
  SyncEngine({
    required ApiClient api,
    Outbox? outbox,
    ProgressDao? progress,
    Connectivity? connectivity,
  })  : // ignore: prefer_initializing_formals — the public name reads better as `api`.
        _api = api,
        _outbox = outbox ?? Outbox(),
        _progress = progress ?? ProgressDao(),
        _connectivity = connectivity ?? Connectivity();

  final ApiClient _api;
  final Outbox _outbox;
  final ProgressDao _progress;
  final Connectivity _connectivity;

  SyncState _state = const SyncState();
  SyncState get state => _state;

  Timer? _timer;
  StreamSubscription<List<ConnectivityResult>>? _connectivitySubscription;
  bool _running = false;
  int _consecutiveFailures = 0;

  /// Backoff between 2 seconds and 5 minutes, as the plan specifies.
  Duration get _retryDelay {
    final seconds = (2 << _consecutiveFailures.clamp(0, 7)).clamp(2, 300);
    return Duration(seconds: seconds);
  }

  /// Start the periodic push and react to the network coming back.
  void start({Duration interval = const Duration(minutes: 5)}) {
    _timer?.cancel();
    _timer = Timer.periodic(interval, (_) => syncNow(reason: 'periodic'));

    _connectivitySubscription?.cancel();
    _connectivitySubscription = _connectivity.onConnectivityChanged.listen((results) {
      final online = results.any((r) => r != ConnectivityResult.none);
      if (online) {
        syncNow(reason: 'network-regained');
      } else {
        _update(_state.copyWith(status: SyncStatus.offline));
      }
    });

    unawaited(refreshPendingCount());
  }

  @override
  void dispose() {
    _timer?.cancel();
    _connectivitySubscription?.cancel();
    super.dispose();
  }

  Future<void> refreshPendingCount({int? childId}) async {
    final pending = await _outbox.pendingCount(childId: childId);
    _update(_state.copyWith(pending: pending));
  }

  /// Push whatever is waiting. Safe to call as often as you like: it does
  /// nothing when there is nothing to send or a run is already in flight.
  Future<ChildSnapshot?> syncNow({int? childId, String reason = 'manual', int maxEvents = 200}) async {
    if (_running) return null;

    final events = await _outbox.pending(limit: maxEvents, childId: childId);
    await refreshPendingCount(childId: childId);

    if (events.isEmpty && childId == null) {
      _update(_state.copyWith(status: SyncStatus.idle, clearError: true));
      return null;
    }

    _running = true;
    _update(_state.copyWith(status: SyncStatus.syncing));

    try {
      if (childId != null) {
        _api.activeChildId = childId;
      }

      final body = {
        'events': events.map((event) => event.toJson()).toList(),
      };

      final response = await _api.post('/sync', body: body);
      final result = SyncResult.fromJson(response);

      // The server acknowledges an event it could not use as well as one it
      // could, so the device stops retrying either way. Mark the unusable ones
      // before clearing the rest, or they would be deleted before anyone could
      // see why they failed.
      await _outbox.quarantine(result.rejected);

      final rejectedIds = result.rejected.map((event) => event.id).toSet();
      await _outbox.acknowledge(result.accepted.where((id) => !rejectedIds.contains(id)));

      ChildSnapshot? snapshot;
      if (result.snapshotJson != null) {
        snapshot = ChildSnapshot.fromJson(result.snapshotJson!);
        await _progress.applySnapshot(snapshot);
      }

      _consecutiveFailures = 0;
      await refreshPendingCount(childId: childId);
      _update(_state.copyWith(
        status: SyncStatus.idle,
        lastSuccessAt: DateTime.now(),
        clearError: true,
      ));

      return snapshot;
    } on ApiException catch (error) {
      await _outbox.recordFailure(events.map((e) => e.id), error.code);
      _consecutiveFailures++;

      _update(_state.copyWith(
        status: error.isOffline ? SyncStatus.offline : SyncStatus.failed,
        lastError: error.message,
      ));

      // Try again on our own schedule rather than hammering a bad connection.
      if (_consecutiveFailures < 12) {
        Timer(_retryDelay, () => syncNow(childId: childId, reason: 'retry'));
      }

      return null;
    } finally {
      _running = false;
    }
  }

  void _update(SyncState next) {
    _state = next;
    notifyListeners();
  }
}
