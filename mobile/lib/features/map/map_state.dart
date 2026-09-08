import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../app/providers.dart';
import '../../core/models/child.dart';
import '../../core/models/pack.dart';
import '../../core/models/snapshot.dart';
import '../../core/network/api_exception.dart';
import '../../design/components/world_card.dart';
import '../../design/tokens.dart';

/// One world as the map needs it: what it looks like, what is inside it, and
/// whether it is on this device yet.
class MapWorld {
  const MapWorld({
    required this.packId,
    required this.name,
    required this.missions,
    this.worldId,
    this.icon,
    this.themeColor,
    this.category,
    this.subjectCode,
    this.isFree = false,
    this.sortOrder = 0,
    this.installedVersion,
    this.availableVersion,
    this.availablePack,
    this.bytesCore = 0,
  });

  final String packId;
  final String name;
  final List<MissionNodeData> missions;
  final int? worldId;
  final String? icon;
  final String? themeColor;
  final String? category;
  final String? subjectCode;
  final bool isFree;
  final int sortOrder;
  final int? installedVersion;
  final int? availableVersion;
  final PackSummary? availablePack;
  final int bytesCore;

  bool get isInstalled => installedVersion != null;

  bool get hasUpdate => installedVersion != null && availableVersion != null && availableVersion! > installedVersion!;

  WorldDownloadState get downloadState {
    if (!isInstalled) return WorldDownloadState.notDownloaded;
    if (hasUpdate) return WorldDownloadState.updateAvailable;
    return WorldDownloadState.ready;
  }

  WorldPalette get palette => WorldPalette.resolve(themeColor: themeColor, slug: packId, index: sortOrder);
}

/// Everything the adventure map draws.
class MapData {
  const MapData({
    this.worlds = const [],
    this.subjects = const [],
    this.offline = false,
    this.entitlement = const Entitlement(),
  });

  final List<MapWorld> worlds;
  final List<String> subjects;
  final bool offline;
  final Entitlement entitlement;

  List<MapWorld> forSubject(String? code) {
    if (code == null || code == 'all') return worlds;

    return worlds.where((world) => (world.subjectCode ?? world.category) == code).toList();
  }
}

/// Builds the map from what is on the device, then decorates it with whatever
/// the server can add. The device always wins for content that is already
/// downloaded, so the map draws instantly and offline.
final mapDataProvider = FutureProvider.family<MapData, Child>((ref, child) async {
  final contentDao = ref.watch(contentDaoProvider);
  final progressDao = ref.watch(progressDaoProvider);
  final repository = ref.watch(contentRepositoryProvider);

  final installed = await contentDao.installedPacks();
  final progress = await progressDao.progressFor(child.id);

  List<PackSummary> catalog = const [];
  var offline = false;

  try {
    catalog = await repository.catalog(level: child.levelCode);
  } on ApiException {
    offline = true;
  }

  final catalogByPack = {for (final pack in catalog) pack.packId: pack};
  final worlds = <MapWorld>[];
  final seenPacks = <String>{};

  // Read the world rows once rather than once per pack: the map is drawn on
  // every return to it, and a child with a full level has a dozen packs.
  final worldRows = await contentDao.worldsForLevel(null);
  final worldByPack = {
    for (final row in worldRows) row['pack_id'] as String: row,
  };

  // Worlds already on the device, with their missions and this child's stars.
  for (final row in installed) {
    final packId = row['pack_id'] as String;
    seenPacks.add(packId);

    final worldRow = worldByPack[packId];

    if (worldRow == null) continue;

    final worldId = worldRow['id'] as int;
    final missionRows = await contentDao.missionsForWorld(worldId);

    worlds.add(
      MapWorld(
        packId: packId,
        worldId: worldId,
        name: worldRow['name'] as String? ?? packId,
        icon: worldRow['icon'] as String?,
        themeColor: worldRow['theme_color'] as String?,
        category: worldRow['category'] as String?,
        subjectCode: worldRow['subject_code'] as String?,
        isFree: (worldRow['is_free'] as int? ?? 0) == 1,
        sortOrder: worldRow['sort_order'] as int? ?? 0,
        installedVersion: row['version'] as int?,
        availableVersion: catalogByPack[packId]?.version,
        availablePack: catalogByPack[packId],
        bytesCore: row['bytes'] as int? ?? 0,
        missions: _nodesFor(missionRows, progress),
      ),
    );
  }

  // Worlds the server offers that are not here yet.
  for (final pack in catalog) {
    if (seenPacks.contains(pack.packId)) continue;

    worlds.add(
      MapWorld(
        packId: pack.packId,
        worldId: pack.worldId,
        name: pack.name,
        icon: pack.icon,
        themeColor: pack.themeColor,
        subjectCode: pack.subjectCode,
        isFree: pack.isFree,
        sortOrder: pack.sortOrder,
        availableVersion: pack.version,
        availablePack: pack,
        bytesCore: pack.bytesCore,
        missions: const [],
      ),
    );
  }

  worlds.sort((a, b) {
    final subject = (a.subjectCode ?? '').compareTo(b.subjectCode ?? '');
    return subject != 0 ? subject : a.sortOrder.compareTo(b.sortOrder);
  });

  final subjects = <String>{
    for (final world in worlds)
      if ((world.subjectCode ?? world.category) != null) (world.subjectCode ?? world.category)!,
  }.toList()
    ..sort();

  return MapData(worlds: worlds, subjects: subjects, offline: offline);
});

/// Turn mission rows plus the child's progress into nodes the map can draw.
///
/// Unlocking is sequential within a world: the first unfinished mission is the
/// one that pulses, everything after it waits. The website disabled this for
/// testing; the app puts it back because a three-year-old handed twenty open
/// missions does not choose, they wander.
List<MissionNodeData> _nodesFor(
  List<Map<String, Object?>> missionRows,
  Map<int, ProgressEntry> progress, {
  bool sequential = true,
}) {
  final nodes = <MissionNodeData>[];
  var reachedActive = false;

  for (final row in missionRows) {
    final id = row['id'] as int;
    final entry = progress[id];
    final completed = entry?.isCompleted ?? false;

    MissionNodeState state;

    if (completed) {
      state = MissionNodeState.completed;
    } else if (!sequential || !reachedActive) {
      state = MissionNodeState.active;
      reachedActive = true;
    } else {
      state = MissionNodeState.locked;
    }

    nodes.add(
      MissionNodeData(
        id: id,
        title: (row['display_title'] as String?)?.isNotEmpty == true
            ? row['display_title'] as String
            : (row['title'] as String? ?? 'Mission'),
        stars: entry?.starsEarned ?? 0,
        state: state,
      ),
    );
  }

  return nodes;
}
