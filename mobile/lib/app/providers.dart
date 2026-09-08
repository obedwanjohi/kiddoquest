import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../core/audio/audio_director.dart';
import '../core/audio/speech_listener.dart';
import '../core/auth/auth_repository.dart';
import '../core/content/content_repository.dart';
import '../core/db/content_dao.dart';
import '../core/db/progress_dao.dart';
import '../core/models/app_config.dart';
import '../core/models/child.dart';
import '../core/notifications/practice_reminders.dart';
import '../core/models/extras.dart';
import '../core/models/pack.dart';
import '../core/models/parent_report.dart';
import '../core/models/snapshot.dart';
import '../core/network/api_client.dart';
import '../core/network/api_exception.dart';
import '../core/sync/outbox.dart';
import '../core/sync/sync_engine.dart';
import '../features/extras/extras_repository.dart';
import '../features/parent/parent_report_repository.dart';

/// Wiring. Each object is created once and handed to whoever asks for it.

final Provider<ApiClient> apiClientProvider = Provider<ApiClient>((ref) {
  // The token is read lazily on every request, which breaks what would
  // otherwise be a circle between the client and the thing that signs in.
  return ApiClient(tokenProvider: () => ref.read(authRepositoryProvider).token());
});

final Provider<AuthRepository> authRepositoryProvider = Provider<AuthRepository>((ref) {
  return AuthRepository(api: ref.watch(apiClientProvider));
});

final contentDaoProvider = Provider<ContentDao>((ref) => ContentDao());

final progressDaoProvider = Provider<ProgressDao>((ref) => ProgressDao());

final outboxProvider = Provider<Outbox>((ref) => Outbox());

final contentRepositoryProvider = Provider<ContentRepository>((ref) {
  return ContentRepository(api: ref.watch(apiClientProvider), dao: ref.watch(contentDaoProvider));
});

final syncEngineProvider = Provider<SyncEngine>((ref) {
  final engine = SyncEngine(
    api: ref.watch(apiClientProvider),
    outbox: ref.watch(outboxProvider),
    progress: ref.watch(progressDaoProvider),
  );

  ref.onDispose(engine.dispose);

  return engine;
});

/// Server-driven settings. Falls back to sensible defaults when the device has
/// never been online, so a fresh install is still usable on a plane.
final appConfigProvider = FutureProvider<AppConfig>((ref) async {
  try {
    final response = await ref.watch(apiClientProvider).get('/config');
    return AppConfig.fromJson(response);
  } on ApiException {
    return const AppConfig();
  }
});

/// What the server currently offers for a level.
final catalogProvider = FutureProvider.family<List<PackSummary>, String?>((ref, level) async {
  return ref.watch(contentRepositoryProvider).catalog(level: level);
});

/// Badges this child has earned, as the server last reported them.
final badgesProvider = FutureProvider.family<List<EarnedBadge>, int>((ref, childId) async {
  return ref.watch(progressDaoProvider).badges(childId);
});

/// Packs already on this device.
final installedPacksProvider = FutureProvider<List<Map<String, Object?>>>((ref) async {
  return ref.watch(contentRepositoryProvider).installed();
});

// ── Session ──────────────────────────────────────────────────────────────────

class SessionState {
  const SessionState({
    this.loading = true,
    this.guardian,
    this.children = const [],
    this.activeChild,
    this.error,
  });

  final bool loading;
  final Guardian? guardian;
  final List<Child> children;
  final Child? activeChild;
  final String? error;

  bool get isSignedIn => guardian != null;

  SessionState copyWith({
    bool? loading,
    Guardian? guardian,
    List<Child>? children,
    Child? activeChild,
    String? error,
    bool clearError = false,
    bool clearChild = false,
    bool signedOut = false,
  }) =>
      SessionState(
        loading: loading ?? this.loading,
        guardian: signedOut ? null : (guardian ?? this.guardian),
        children: signedOut ? const [] : (children ?? this.children),
        activeChild: (signedOut || clearChild) ? null : (activeChild ?? this.activeChild),
        error: clearError ? null : (error ?? this.error),
      );
}

/// Who is signed in and who is playing.
class SessionNotifier extends Notifier<SessionState> {
  @override
  SessionState build() {
    Future.microtask(bootstrap);
    return const SessionState();
  }

  AuthRepository get _auth => ref.read(authRepositoryProvider);

  ProgressDao get _progress => ref.read(progressDaoProvider);

  /// Restore the signed-in parent from storage, then refresh from the server if
  /// it happens to be reachable. Never blocks on the network.
  Future<void> bootstrap() async {
    await _auth.restore();

    if (!_auth.isSignedIn) {
      state = const SessionState(loading: false);
      return;
    }

    final cached = await _progress.children();
    state = SessionState(loading: false, guardian: _auth.guardian, children: cached);

    try {
      final children = await _auth.refreshChildren();
      await _progress.replaceChildren(children, guardianId: _auth.guardian?.id);
      state = state.copyWith(guardian: _auth.guardian, children: children, clearError: true);
    } on ApiException catch (error) {
      if (error.isUnauthorized) {
        await signOut();
      }
      // Offline is fine: the cached children are already on screen.
    }
  }

  Future<bool> signIn({required String email, required String password}) async {
    state = state.copyWith(loading: true, clearError: true);

    try {
      final result = await _auth.signIn(email: email, password: password);
      await _progress.replaceChildren(result.children, guardianId: result.guardian.id);

      state = SessionState(loading: false, guardian: result.guardian, children: result.children);
      return true;
    } on ApiException catch (error) {
      state = state.copyWith(loading: false, error: error.message);
      return false;
    }
  }

  Future<bool> register({
    required String name,
    required String email,
    required String password,
    String? phone,
  }) async {
    state = state.copyWith(loading: true, clearError: true);

    try {
      final result = await _auth.register(name: name, email: email, password: password, phone: phone);
      await _progress.replaceChildren(result.children, guardianId: result.guardian.id);

      state = SessionState(loading: false, guardian: result.guardian, children: result.children);
      return true;
    } on ApiException catch (error) {
      state = state.copyWith(loading: false, error: error.message);
      return false;
    }
  }

  Future<Child?> addChild({
    required String name,
    required String avatar,
    String? birthdate,
    String? favoriteColor,
  }) async {
    try {
      final child = await _auth.addChild(
        name: name,
        avatar: avatar,
        birthdate: birthdate,
        favoriteColor: favoriteColor,
      );

      final children = [...state.children, child];
      await _progress.replaceChildren(children, guardianId: state.guardian?.id);
      state = state.copyWith(children: children, clearError: true);

      return child;
    } on ApiException catch (error) {
      state = state.copyWith(error: error.message);
      return null;
    }
  }

  /// Choose who is playing. Everything after this point is about that child.
  void selectChild(Child child) {
    ref.read(apiClientProvider).activeChildId = child.id;
    state = state.copyWith(activeChild: child, clearError: true);
  }

  void leaveChild() {
    ref.read(apiClientProvider).activeChildId = null;
    state = state.copyWith(clearChild: true);
  }

  /// Replace the active child with a fresher copy, after a sync or a mission.
  void updateActiveChild(Child child) {
    final children = [
      for (final existing in state.children) existing.id == child.id ? child : existing,
    ];

    state = state.copyWith(
      children: children,
      activeChild: state.activeChild?.id == child.id ? child : state.activeChild,
    );
  }

  Future<void> signOut() async {
    await _auth.signOut();
    ref.read(apiClientProvider).activeChildId = null;
    state = const SessionState(loading: false);
  }
}

final sessionProvider = NotifierProvider<SessionNotifier, SessionState>(SessionNotifier.new);

/// Lets go_router re-evaluate its redirect when someone signs in or out.
final routerRefreshProvider = Provider<ChangeNotifier>((ref) {
  final notifier = ChangeNotifier();

  ref.listen<SessionState>(sessionProvider, (previous, next) {
    if (previous?.isSignedIn != next.isSignedIn ||
        previous?.activeChild?.id != next.activeChild?.id ||
        previous?.loading != next.loading) {
      // ignore: invalid_use_of_protected_member, invalid_use_of_visible_for_testing_member
      notifier.notifyListeners();
    }
  });

  ref.onDispose(notifier.dispose);

  return notifier;
});

// ── Parent zone ──────────────────────────────────────────────────────────────

final parentReportRepositoryProvider = Provider<ParentReportRepository>((ref) {
  return ParentReportRepository(api: ref.watch(apiClientProvider));
});

/// A report plus whether it is the copy we already had.
///
/// The screen says so out loud rather than pretending a week-old report is
/// tonight's, which is the difference between a stale number and a lie.
class ParentReportView {
  const ParentReportView({required this.report, this.fromCache = false});

  final ParentReport report;
  final bool fromCache;
}

/// The parent dashboard's data, keyed by child and range ('7d', '30d', '90d').
final parentReportProvider =
    FutureProvider.family<ParentReportView, (int, String)>((ref, key) async {
  final repository = ref.watch(parentReportRepositoryProvider);
  final (childId, range) = key;

  try {
    return ParentReportView(report: await repository.refresh(childId, range));
  } on ApiException {
    final cached = await repository.cached(childId, range);
    if (cached != null) {
      return ParentReportView(report: cached, fromCache: true);
    }
    rethrow;
  }
});

// ── Sound, and the content that is not missions ──────────────────────────────

/// Reads prompts, verses and stories out loud. One per app: two voices talking
/// over each other is worse than none.
final audioDirectorProvider = Provider<AudioDirector>((ref) {
  final director = AudioDirector();

  ref.onDispose(director.dispose);

  return director;
});

/// The daily practice nudge, scheduled on the device.
final practiceRemindersProvider = Provider<PracticeReminders>((ref) => PracticeReminders());

/// Hears a child say a word, where the device can hear at all.
final speechListenerProvider = Provider<SpeechListener>((ref) => SpeechListener());

final extrasRepositoryProvider = Provider<ExtrasRepository>((ref) {
  return ExtrasRepository(api: ref.watch(apiClientProvider));
});

/// The devotional list and the songs hub, server copy or cached copy.
final extrasProvider = FutureProvider<Extras>((ref) async {
  return ref.watch(extrasRepositoryProvider).load();
});
