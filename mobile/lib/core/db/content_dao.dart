import 'dart:convert';
import 'dart:math';

import 'package:sqflite/sqflite.dart';

import '../models/pack.dart';
import 'local_database.dart';

/// Reads and writes downloaded content.
///
/// The interesting method here is [drawSession]: it is the website's question
/// draw, ported to run on the device so a retest brings fresh questions with no
/// network at all.
class ContentDao {
  ContentDao([LocalDatabase? database]) : _database = database ?? LocalDatabase.instance;

  final LocalDatabase _database;

  Future<Database> get _db async => _database.database;

  /// Store a downloaded pack, replacing any previous version of it.
  Future<void> savePack(ContentPack pack, {int bytes = 0}) async {
    final db = await _db;

    await db.transaction((txn) async {
      final previous = await txn.query('worlds', columns: ['id'], where: 'pack_id = ?', whereArgs: [pack.packId]);

      for (final row in previous) {
        final worldId = row['id'] as int;
        final missions = await txn.query('missions', columns: ['id'], where: 'world_id = ?', whereArgs: [worldId]);

        for (final mission in missions) {
          final missionId = mission['id'] as int;
          final questions = await txn.query('questions', columns: ['id'], where: 'mission_id = ?', whereArgs: [missionId]);

          for (final question in questions) {
            await txn.delete('options', where: 'question_id = ?', whereArgs: [question['id']]);
          }

          await txn.delete('questions', where: 'mission_id = ?', whereArgs: [missionId]);
        }

        await txn.delete('missions', where: 'world_id = ?', whereArgs: [worldId]);
      }

      await txn.delete('worlds', where: 'pack_id = ?', whereArgs: [pack.packId]);
      await txn.delete('media_files', where: 'pack_id = ?', whereArgs: [pack.packId]);

      await txn.insert(
        'packs',
        {
          'pack_id': pack.packId,
          'version': pack.version,
          'level': pack.level,
          'subject_code': pack.subjectCode,
          'world_slug': pack.world.slug,
          'name': pack.world.name,
          'icon': pack.world.icon,
          'theme_color': pack.world.themeColor,
          'is_free': pack.world.isFree ? 1 : 0,
          'sort_order': pack.world.sortOrder,
          'status': 'ready',
          'bytes': bytes,
          'downloaded_at': DateTime.now().toIso8601String(),
        },
        conflictAlgorithm: ConflictAlgorithm.replace,
      );

      await txn.insert(
        'worlds',
        {
          'id': pack.world.id,
          'pack_id': pack.packId,
          'slug': pack.world.slug,
          'name': pack.world.name,
          'icon': pack.world.icon,
          'description': pack.world.description,
          'theme_color': pack.world.themeColor,
          'subject_code': pack.subjectCode,
          'category': pack.subjectCategory,
          'level': pack.level,
          'is_free': pack.world.isFree ? 1 : 0,
          'sort_order': pack.world.sortOrder,
        },
        conflictAlgorithm: ConflictAlgorithm.replace,
      );

      for (final mission in pack.missions) {
        await txn.insert(
          'missions',
          {
            'id': mission.id,
            'world_id': pack.world.id,
            'pack_id': pack.packId,
            'slug': mission.slug,
            'title': mission.title,
            'display_title': mission.displayTitle,
            'description': mission.description,
            'intro_text': mission.introText,
            'intro_audio': mission.introAudio,
            'outro_text': mission.outroText,
            'video_path': mission.videoPath,
            'questions_per_session': mission.questionsPerSession,
            'pass_threshold': mission.passThresholdPercent,
            'stars_reward': mission.starsReward,
            'estimated_minutes': mission.estimatedMinutes,
            'sort_order': mission.sortOrder,
          },
          conflictAlgorithm: ConflictAlgorithm.replace,
        );

        for (final question in mission.questions) {
          await txn.insert(
            'questions',
            {
              'id': question.id,
              'mission_id': mission.id,
              'bank_id': question.bankId,
              'type': question.type,
              'prompt': question.prompt,
              'narration_text': question.narrationText,
              'narration_audio': question.narrationAudio,
              'image': question.image,
              'audio': question.audio,
              'hint': question.hint,
              'explanation': question.explanation,
              'points': question.points,
              'difficulty': question.difficulty,
              'scoring_config': jsonEncode(question.scoringConfig),
              'metadata': jsonEncode(question.metadata),
              'sort_order': 0,
            },
            conflictAlgorithm: ConflictAlgorithm.replace,
          );

          for (final option in question.options) {
            await txn.insert(
              'options',
              {
                'id': option.id,
                'question_id': question.id,
                'text': option.text,
                'image': option.image,
                'audio': option.audio,
                'is_correct': option.isCorrect ? 1 : 0,
                'content_type': option.contentType,
                'match_key': option.matchKey,
                'sort_order': option.sortOrder,
              },
              conflictAlgorithm: ConflictAlgorithm.replace,
            );
          }
        }
      }

      for (final media in pack.media) {
        await txn.insert(
          'media_files',
          {
            'path': media.path,
            'pack_id': pack.packId,
            'url': media.url,
            'sha256': media.sha256,
            'bytes': media.bytes,
            'kind': media.kind,
            'required': media.required ? 1 : 0,
            'status': 'pending',
          },
          conflictAlgorithm: ConflictAlgorithm.replace,
        );
      }
    });
  }

  Future<List<Map<String, Object?>>> installedPacks() async {
    final db = await _db;

    return db.query('packs', orderBy: 'subject_code, sort_order');
  }

  Future<int?> installedVersion(String packId) async {
    final db = await _db;
    final rows = await db.query('packs', columns: ['version'], where: 'pack_id = ?', whereArgs: [packId]);

    return rows.isEmpty ? null : rows.first['version'] as int;
  }

  Future<List<Map<String, Object?>>> worldsForLevel(String? levelCode) async {
    final db = await _db;

    if (levelCode == null) {
      return db.query('worlds', orderBy: 'sort_order');
    }

    return db.query(
      'worlds',
      where: 'level IS NULL OR upper(level) = ?',
      whereArgs: [levelCode.toUpperCase()],
      orderBy: 'sort_order',
    );
  }

  Future<List<Map<String, Object?>>> missionsForWorld(int worldId) async {
    final db = await _db;

    return db.query('missions', where: 'world_id = ?', whereArgs: [worldId], orderBy: 'sort_order, id');
  }

  Future<Map<String, Object?>?> mission(int missionId) async {
    final db = await _db;
    final rows = await db.query('missions', where: 'id = ?', whereArgs: [missionId], limit: 1);

    return rows.isEmpty ? null : rows.first;
  }

  /// Draw the questions for one attempt.
  ///
  /// Same rules as `QuestionBank::drawQuestions` on the server:
  ///  1. leave out anything this child has seen for this mission in the last
  ///     seven days, unless that would leave too few to play with,
  ///  2. take an even share from each question type present,
  ///  3. fill any remaining slots from what is left, then shuffle.
  Future<List<PackQuestion>> drawSession({
    required int missionId,
    required int childId,
    int? count,
    int exclusionWindowDays = 7,
    Random? random,
  }) async {
    final db = await _db;
    final rng = random ?? Random();

    final missionRow = await mission(missionId);
    final wanted = count ?? (missionRow?['questions_per_session'] as int? ?? 6);

    final cutoff = DateTime.now().subtract(Duration(days: exclusionWindowDays)).toIso8601String();
    final recent = await db.query(
      'question_attempt_log',
      columns: ['DISTINCT question_id'],
      where: 'child_id = ? AND mission_id = ? AND attempted_at >= ?',
      whereArgs: [childId, missionId, cutoff],
    );
    final seen = recent.map((row) => row['question_id'] as int).toSet();

    final all = await _questionsFor(missionId);
    if (all.isEmpty) return const [];

    var candidates = all.where((q) => !seen.contains(q.id)).toList();

    // Everything has been seen recently: fall back to the full pool rather than
    // telling a child there is nothing to play.
    if (candidates.length < wanted) {
      final missing = all.where((q) => !candidates.any((c) => c.id == q.id));
      candidates = [...candidates, ...missing];
    }

    final buckets = <String, List<PackQuestion>>{};
    for (final question in candidates) {
      buckets.putIfAbsent(question.type, () => []).add(question);
    }

    if (buckets.length <= 1) {
      final pool = [...candidates]..shuffle(rng);
      return pool.take(wanted).toList();
    }

    final drawn = <PackQuestion>[];
    final quota = wanted ~/ buckets.length;

    for (final bucket in buckets.values) {
      final pool = [...bucket]..shuffle(rng);
      drawn.addAll(pool.take(quota));
    }

    if (drawn.length < wanted) {
      final chosen = drawn.map((q) => q.id).toSet();
      final leftovers = candidates.where((q) => !chosen.contains(q.id)).toList()..shuffle(rng);
      drawn.addAll(leftovers.take(wanted - drawn.length));
    }

    return (drawn..shuffle(rng)).take(wanted).toList();
  }

  Future<List<PackQuestion>> _questionsFor(int missionId) async {
    final db = await _db;

    final questionRows = await db.query('questions', where: 'mission_id = ?', whereArgs: [missionId]);
    if (questionRows.isEmpty) return const [];

    final ids = questionRows.map((row) => row['id'] as int).toList();
    final placeholders = List.filled(ids.length, '?').join(',');
    final optionRows = await db.query(
      'options',
      where: 'question_id IN ($placeholders)',
      whereArgs: ids,
      orderBy: 'sort_order',
    );

    final optionsByQuestion = <int, List<PackOption>>{};
    for (final row in optionRows) {
      optionsByQuestion.putIfAbsent(row['question_id'] as int, () => []).add(
            PackOption(
              id: row['id'] as int,
              text: row['text'] as String?,
              image: row['image'] as String?,
              audio: row['audio'] as String?,
              isCorrect: (row['is_correct'] as int? ?? 0) == 1,
              contentType: row['content_type'] as String?,
              matchKey: row['match_key'] as String?,
              sortOrder: row['sort_order'] as int? ?? 0,
            ),
          );
    }

    return questionRows.map((row) {
      final id = row['id'] as int;

      return PackQuestion(
        id: id,
        type: row['type'] as String? ?? 'multiple_choice',
        bankId: row['bank_id'] as int?,
        prompt: row['prompt'] as String?,
        narrationText: row['narration_text'] as String?,
        narrationAudio: row['narration_audio'] as String?,
        image: row['image'] as String?,
        audio: row['audio'] as String?,
        hint: row['hint'] as String?,
        explanation: row['explanation'] as String?,
        points: row['points'] as int? ?? 1,
        difficulty: row['difficulty'] as String?,
        scoringConfig: _decodeMap(row['scoring_config']),
        metadata: _decodeMap(row['metadata']),
        options: optionsByQuestion[id] ?? const [],
      );
    }).toList();
  }

  /// Where a media path was downloaded to, if it was.
  Future<Map<String, String>> localMediaPaths(String packId) async {
    final db = await _db;
    final rows = await db.query(
      'media_files',
      columns: ['path', 'local_path'],
      where: 'pack_id = ? AND local_path IS NOT NULL',
      whereArgs: [packId],
    );

    return {
      for (final row in rows) row['path'] as String: row['local_path'] as String,
    };
  }

  /// Everything a pack's media keys can resolve to: the downloaded file when
  /// there is one, and the URL to fall back on when there is not.
  ///
  /// Questions and options carry media *keys*, not URLs, so that one picture
  /// shared by twenty questions is stored and downloaded once. Something has to
  /// turn a key back into an image, and this is it.
  /// Every mission on this device, with the world it belongs to.
  ///
  /// The sticker book is drawn from this joined with the child's progress: a
  /// sticker is a mission they have finished, so there is nothing to award, to
  /// sync, or to get out of step with the missions themselves.
  Future<List<StickerSlot>> stickerSlots() async {
    final db = await _db;

    final rows = await db.rawQuery('''
      SELECT m.id AS mission_id,
             COALESCE(NULLIF(m.display_title, ""), m.title) AS title,
             w.name AS world_name,
             w.icon AS world_icon,
             w.theme_color AS theme_color,
             w.slug AS world_slug,
             w.sort_order AS world_order,
             m.sort_order AS mission_order
      FROM missions m
      JOIN worlds w ON w.id = m.world_id
      ORDER BY w.sort_order, m.sort_order
    ''');

    return rows
        .map((row) => StickerSlot(
              missionId: row['mission_id'] as int,
              title: row['title'] as String? ?? 'Mission',
              worldName: row['world_name'] as String? ?? '',
              worldIcon: row['world_icon'] as String?,
              worldSlug: row['world_slug'] as String?,
              themeColor: row['theme_color'] as String?,
            ))
        .toList();
  }

  /// One media key, from whichever pack happens to carry it.
  ///
  /// Narration and songs are addressed by key alone: the caller knows it wants
  /// `audio/leo/well-done.mp3`, not which world shipped it. Media is
  /// content-addressed, so the same key in two packs is the same file.
  Future<ResolvedMedia?> media(String key) async {
    if (key.isEmpty) return null;

    final db = await _db;
    final rows = await db.query(
      'media_files',
      columns: ['url', 'local_path'],
      where: 'path = ?',
      whereArgs: [key],
      // A downloaded copy beats one that would need the network.
      orderBy: 'local_path IS NULL',
      limit: 1,
    );

    if (rows.isEmpty) return null;

    return ResolvedMedia(
      url: rows.first['url'] as String? ?? '',
      localPath: rows.first['local_path'] as String?,
    );
  }

  Future<Map<String, ResolvedMedia>> mediaFor(String packId) async {
    final db = await _db;
    final rows = await db.query(
      'media_files',
      columns: ['path', 'url', 'local_path'],
      where: 'pack_id = ?',
      whereArgs: [packId],
    );

    return {
      for (final row in rows)
        row['path'] as String: ResolvedMedia(
          url: row['url'] as String? ?? '',
          localPath: row['local_path'] as String?,
        ),
    };
  }

  Future<void> markMediaDownloaded(String path, String localPath) async {
    final db = await _db;

    await db.update(
      'media_files',
      {'local_path': localPath, 'status': 'ready'},
      where: 'path = ?',
      whereArgs: [path],
    );
  }

  Future<List<Map<String, Object?>>> pendingMedia(String packId, {bool requiredOnly = true}) async {
    final db = await _db;

    return db.query(
      'media_files',
      where: requiredOnly
          ? 'pack_id = ? AND status != ? AND required = 1'
          : 'pack_id = ? AND status != ?',
      whereArgs: [packId, 'ready'],
    );
  }

  Future<void> deletePack(String packId) async {
    final db = await _db;
    final worlds = await db.query('worlds', columns: ['id'], where: 'pack_id = ?', whereArgs: [packId]);

    await db.transaction((txn) async {
      for (final world in worlds) {
        final missions = await txn.query('missions', columns: ['id'], where: 'world_id = ?', whereArgs: [world['id']]);

        for (final mission in missions) {
          final questions = await txn.query('questions', columns: ['id'], where: 'mission_id = ?', whereArgs: [mission['id']]);

          for (final question in questions) {
            await txn.delete('options', where: 'question_id = ?', whereArgs: [question['id']]);
          }

          await txn.delete('questions', where: 'mission_id = ?', whereArgs: [mission['id']]);
        }

        await txn.delete('missions', where: 'world_id = ?', whereArgs: [world['id']]);
      }

      await txn.delete('worlds', where: 'pack_id = ?', whereArgs: [packId]);
      await txn.delete('media_files', where: 'pack_id = ?', whereArgs: [packId]);
      await txn.delete('packs', where: 'pack_id = ?', whereArgs: [packId]);
    });
  }

  static Map<String, dynamic> _decodeMap(Object? value) {
    if (value is! String || value.isEmpty) return const {};

    try {
      final decoded = jsonDecode(value);
      return decoded is Map ? decoded.cast<String, dynamic>() : const {};
    } catch (_) {
      return const {};
    }
  }
}

/// One space in the sticker book: a mission, and the world it came from.
class StickerSlot {
  const StickerSlot({
    required this.missionId,
    required this.title,
    required this.worldName,
    this.worldIcon,
    this.worldSlug,
    this.themeColor,
  });

  final int missionId;
  final String title;
  final String worldName;
  final String? worldIcon;
  final String? worldSlug;
  final String? themeColor;
}
