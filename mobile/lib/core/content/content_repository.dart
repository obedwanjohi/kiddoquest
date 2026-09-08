import 'dart:convert';

import 'package:crypto/crypto.dart';
import 'package:flutter/foundation.dart';

import '../db/content_dao.dart';
import '../models/pack.dart';
import '../network/api_client.dart';
import '../network/api_exception.dart';
import '../platform/local_files.dart';

/// How a download is going, for the world card and the downloads screen.
class PackProgress {
  const PackProgress({
    required this.packId,
    required this.state,
    this.received = 0,
    this.total = 0,
    this.message,
  });

  final String packId;
  final PackState state;
  final int received;
  final int total;
  final String? message;

  double? get fraction => total <= 0 ? null : (received / total).clamp(0.0, 1.0);
}

enum PackState { queued, downloading, ready, failed }

/// Gets worlds onto the device and keeps them current.
///
/// Downloads are verified against the checksum the server published, because a
/// truncated pack that half-parses is far worse than one that failed honestly.
class ContentRepository {
  ContentRepository({required ApiClient api, ContentDao? dao})
      : _api = api,
        _dao = dao ?? ContentDao();

  final ApiClient _api;
  final ContentDao _dao;

  final ValueNotifier<Map<String, PackProgress>> progress = ValueNotifier({});

  /// What the server has for this child's level.
  Future<List<PackSummary>> catalog({String? level, String? etag}) async {
    final result = await _api.getCached('/content/catalog', query: {'level': ?level}, etag: etag);

    if (result.notModified || result.body == null) return const [];

    return ((result.body!['packs'] as List?) ?? const [])
        .map((e) => PackSummary.fromJson((e as Map).cast<String, dynamic>()))
        .toList();
  }

  Future<List<Map<String, Object?>>> installed() => _dao.installedPacks();

  Future<int?> installedVersion(String packId) => _dao.installedVersion(packId);

  /// Fetch a pack and store it. Media is fetched afterwards so a child can start
  /// as soon as the questions are there.
  Future<ContentPack> download(PackSummary summary, {bool withMedia = true}) async {
    _publish(PackProgress(packId: summary.packId, state: PackState.downloading, message: 'Packing your adventure…'));

    try {
      final bytes = await _api.getBytes('/content/packs/${summary.packId}/v${summary.version}/download');

      if (summary.sha256 != null && summary.sha256!.isNotEmpty) {
        final digest = sha256.convert(bytes).toString();

        if (digest != summary.sha256) {
          throw const ApiException(
            code: 'pack_corrupt',
            message: 'That download did not arrive in one piece. Try again.',
          );
        }
      }

      final document = jsonDecode(utf8.decode(bytes)) as Map<String, dynamic>;
      final pack = ContentPack.fromJson(document);

      await _dao.savePack(pack, bytes: bytes.length);

      if (withMedia) {
        await downloadMedia(pack.packId);
      }

      _publish(PackProgress(packId: summary.packId, state: PackState.ready, received: bytes.length, total: bytes.length));

      return pack;
    } on ApiException catch (error) {
      _publish(PackProgress(packId: summary.packId, state: PackState.failed, message: error.message));
      rethrow;
    }
  }

  /// Pull down the images and audio a pack refers to. Video is left alone: it
  /// is large, optional, and a parent decides when to spend the bundle on it.
  Future<void> downloadMedia(String packId, {bool includeVideo = false}) async {
    // A browser has no application directory to write into, and it does not
    // need one: the pack keeps the URL for every file and the browser caches it.
    if (!supportsLocalFiles) return;

    final pending = await _dao.pendingMedia(packId, requiredOnly: !includeVideo);

    if (pending.isEmpty) return;

    final directoryPath = await packMediaDirectory(packId);
    var done = 0;

    for (final row in pending) {
      final url = row['url'] as String? ?? '';
      final path = row['path'] as String? ?? '';

      if (url.isEmpty || path.isEmpty) continue;
      if (!includeVideo && (row['kind'] as String?) == 'video') continue;

      try {
        final bytes = await _api.getBytes(url);
        final saved = await writeMediaFile(directoryPath, path.replaceAll('/', '_'), bytes);
        await _dao.markMediaDownloaded(path, saved);
      } catch (_) {
        // One missing picture must never stop a world from being playable; the
        // renderer falls back to text and the file is retried next time.
        continue;
      }

      done++;

      _publish(PackProgress(
        packId: packId,
        state: PackState.downloading,
        received: done,
        total: pending.length,
        message: 'Collecting pictures and sounds…',
      ));
    }
  }

  /// Map the pack's media keys to files on this device.
  Future<Map<String, String>> mediaPaths(String packId) => _dao.localMediaPaths(packId);

  Future<void> remove(String packId) async {
    await _dao.deletePack(packId);

    if (supportsLocalFiles) {
      await deletePackMediaDirectory(packId);
    }

    final next = Map<String, PackProgress>.from(progress.value)..remove(packId);
    progress.value = next;
  }

  /// Which installed packs the server has a newer version of.
  Future<List<PackSummary>> updatesAvailable(List<PackSummary> catalog) async {
    final updates = <PackSummary>[];

    for (final pack in catalog) {
      final installed = await _dao.installedVersion(pack.packId);

      if (installed != null && installed < pack.version) {
        updates.add(pack);
      }
    }

    return updates;
  }

  void _publish(PackProgress value) {
    progress.value = {...progress.value, value.packId: value};
  }
}
