import 'dart:convert';

import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:sqflite/sqflite.dart';

import '../../app/providers.dart';
import '../../core/db/local_database.dart';
import '../../core/models/child.dart';
import '../../core/models/snapshot.dart';
import '../../core/network/api_exception.dart';

/// One mission on the map: where the child stands with it.
class SiteMission {
  const SiteMission({required this.id, required this.title, this.status, this.stars = 0});

  final int id;
  final String title;
  final String? status;
  final int stars;

  bool get isCompleted => status == 'completed';

  factory SiteMission.fromJson(Map<String, dynamic> json) => SiteMission(
        id: (json['id'] as num).toInt(),
        title: json['title'] as String? ?? 'Mission',
        status: json['status'] as String?,
        stars: (json['stars'] as num?)?.toInt() ?? 0,
      );

  Map<String, dynamic> toJson() => {'id': id, 'title': title, 'status': status, 'stars': stars};

  SiteMission withProgress(ProgressEntry? local) {
    if (local == null) return this;

    // Whatever the device already knows wins where it is ahead: a mission
    // finished offline shows as finished before the server has heard of it.
    return SiteMission(
      id: id,
      title: title,
      status: (isCompleted || local.isCompleted) ? 'completed' : (status ?? local.status),
      stars: local.starsEarned > stars ? local.starsEarned : stars,
    );
  }
}

/// One world on the map, exactly as the website lists it.
class SiteWorld {
  const SiteWorld({
    required this.id,
    required this.name,
    required this.missions,
    this.slug,
    this.icon,
    this.subjectName,
    this.subjectCategory = 'math',
    this.packId,
    this.packVersion,
    this.packSha256,
  });

  final int id;
  final String name;
  final List<SiteMission> missions;
  final String? slug;
  final String? icon;
  final String? subjectName;
  final String subjectCategory;
  final String? packId;
  final int? packVersion;
  final String? packSha256;

  int get completedCount => missions.where((m) => m.isCompleted).length;

  factory SiteWorld.fromJson(Map<String, dynamic> json) {
    final pack = (json['pack'] as Map?)?.cast<String, dynamic>();

    return SiteWorld(
      id: (json['id'] as num).toInt(),
      name: json['name'] as String? ?? 'World',
      slug: json['slug'] as String?,
      icon: json['icon'] as String?,
      subjectName: json['subject_name'] as String?,
      subjectCategory: json['subject_category'] as String? ?? 'math',
      packId: pack?['pack_id'] as String?,
      packVersion: (pack?['version'] as num?)?.toInt(),
      packSha256: pack?['sha256'] as String?,
      missions: ((json['missions'] as List?) ?? const [])
          .whereType<Map>()
          .map((e) => SiteMission.fromJson(e.cast<String, dynamic>()))
          .toList(),
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        'slug': slug,
        'icon': icon,
        'subject_name': subjectName,
        'subject_category': subjectCategory,
        'pack': packId == null ? null : {'pack_id': packId, 'version': packVersion, 'sha256': packSha256},
        'missions': missions.map((m) => m.toJson()).toList(),
      };

  SiteWorld withProgress(Map<int, ProgressEntry> local) => SiteWorld(
        id: id,
        name: name,
        slug: slug,
        icon: icon,
        subjectName: subjectName,
        subjectCategory: subjectCategory,
        packId: packId,
        packVersion: packVersion,
        packSha256: packSha256,
        missions: [for (final mission in missions) mission.withProgress(local[mission.id])],
      );
}

/// The whole map.
class SiteMap {
  const SiteMap({
    this.worlds = const [],
    this.songsUnlocked = false,
    this.remainingMinutes,
    this.offline = false,
  });

  final List<SiteWorld> worlds;
  final bool songsUnlocked;
  final int? remainingMinutes;

  /// True when this is the copy the device kept, because the server could not
  /// be reached.
  final bool offline;

  /// `activeSubject === 'all' || activeSubject === world.subject_category`.
  List<SiteWorld> forSubject(String subject) =>
      subject == 'all' ? worlds : worlds.where((w) => w.subjectCategory == subject).toList();

  factory SiteMap.fromJson(Map<String, dynamic> json, {bool offline = false}) => SiteMap(
        offline: offline,
        songsUnlocked: json['songs_unlocked'] == true,
        remainingMinutes: (json['remaining_minutes'] as num?)?.toInt(),
        worlds: ((json['worlds'] as List?) ?? const [])
            .whereType<Map>()
            .map((e) => SiteWorld.fromJson(e.cast<String, dynamic>()))
            .toList(),
      );

  Map<String, dynamic> toJson() => {
        'songs_unlocked': songsUnlocked,
        'remaining_minutes': remainingMinutes,
        'worlds': worlds.map((w) => w.toJson()).toList(),
      };
}

/// Where the map comes from, in order of preference.
///
/// 1. The server — the website's own world list for this child.
/// 2. The copy kept from the last time the server answered.
/// 3. Worlds already downloaded, for a device that has never been online with
///    this child.
///
/// In every case the device's own progress is laid over the top, so a mission
/// finished on a bus shows its stars on the map straight away.
class SiteMapRepository {
  SiteMapRepository(this.ref, {LocalDatabase? database}) : _database = database ?? LocalDatabase.instance;

  final Ref ref;
  final LocalDatabase _database;

  static String cacheKey(int childId) => 'site_map:$childId';

  Future<SiteMap> load(Child child) async {
    final local = await ref.read(progressDaoProvider).progressFor(child.id);

    try {
      final json = await ref.read(apiClientProvider).get('/children/${child.id}/map');
      await _remember(child.id, json);

      return _overlay(SiteMap.fromJson(json), local);
    } on ApiException {
      final cached = await _cached(child.id);
      if (cached != null) return _overlay(cached, local);

      return _overlay(await _fromDownloads(), local);
    }
  }

  SiteMap _overlay(SiteMap map, Map<int, ProgressEntry> local) => SiteMap(
        offline: map.offline,
        songsUnlocked: map.songsUnlocked,
        remainingMinutes: map.remainingMinutes,
        worlds: [for (final world in map.worlds) world.withProgress(local)],
      );

  Future<void> _remember(int childId, Map<String, dynamic> json) async {
    final db = await _database.database;

    await db.insert(
      'settings',
      {'key': cacheKey(childId), 'value': jsonEncode(json)},
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  Future<SiteMap?> _cached(int childId) async {
    final db = await _database.database;
    final rows = await db.query('settings', where: 'key = ?', whereArgs: [cacheKey(childId)], limit: 1);

    if (rows.isEmpty) return null;

    try {
      final json = (jsonDecode(rows.first['value'] as String) as Map).cast<String, dynamic>();
      return SiteMap.fromJson(json, offline: true);
    } catch (_) {
      return null;
    }
  }

  Future<SiteMap> _fromDownloads() async {
    final dao = ref.read(contentDaoProvider);
    final rows = await dao.worldsForLevel(null);
    final worlds = <SiteWorld>[];

    for (final row in rows) {
      final missionRows = await dao.missionsForWorld(row['id'] as int);

      worlds.add(
        SiteWorld(
          id: row['id'] as int,
          name: row['name'] as String? ?? 'World',
          slug: row['slug'] as String?,
          icon: row['icon'] as String?,
          subjectCategory: _categoryFor(row),
          packId: row['pack_id'] as String?,
          missions: [
            for (final mission in missionRows)
              SiteMission(
                id: mission['id'] as int,
                title: (mission['display_title'] as String?)?.isNotEmpty == true
                    ? mission['display_title'] as String
                    : mission['title'] as String? ?? 'Mission',
              ),
          ],
        ),
      );
    }

    return SiteMap(worlds: worlds, offline: true);
  }

  /// A downloaded world only knows its subject code; map it onto the website's
  /// subject tabs.
  static String _categoryFor(Map<String, Object?> row) {
    final text = '${row['category'] ?? ''} ${row['subject_code'] ?? ''} ${row['slug'] ?? ''}'.toLowerCase();

    if (text.contains('speak') || text.contains('repeat')) return 'speak';
    if (text.contains('trac') || text.contains('writing') || text.contains('pattern')) return 'tracing';
    if (text.contains('english') || text.contains('phonic') || text.contains('letter')) return 'english';
    if (text.contains('cre') || text.contains('values') || text.contains('jesus') || text.contains('creation')) return 'cre';

    return 'math';
  }
}

final siteMapRepositoryProvider = Provider<SiteMapRepository>((ref) => SiteMapRepository(ref));

/// The adventure map for one child.
final siteMapProvider = FutureProvider.family<SiteMap, Child>((ref, child) async {
  return ref.watch(siteMapRepositoryProvider).load(child);
});
