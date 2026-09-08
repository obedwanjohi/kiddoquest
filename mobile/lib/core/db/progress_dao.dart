import 'dart:convert';

import 'package:sqflite/sqflite.dart';

import '../models/child.dart';
import '../models/snapshot.dart';
import 'local_database.dart';

/// The child's own copy of where they are.
///
/// Written the moment a mission ends so the map updates instantly, then
/// corrected by the server's snapshot when a network turns up. Stars never go
/// down in either direction, which is the one merge rule a child would notice.
class ProgressDao {
  ProgressDao([LocalDatabase? database]) : _database = database ?? LocalDatabase.instance;

  final LocalDatabase _database;

  Future<Database> get _db async => _database.database;

  Future<void> saveChild(Child child, {int? guardianId}) async {
    final db = await _db;

    await db.insert(
      'children',
      {
        'id': child.id,
        'guardian_id': guardianId,
        'payload': jsonEncode(child.toJson()),
        'snapshot_at': DateTime.now().toIso8601String(),
      },
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  Future<List<Child>> children() async {
    final db = await _db;
    final rows = await db.query('children');

    return rows
        .map((row) => Child.fromJson(jsonDecode(row['payload'] as String) as Map<String, dynamic>))
        .toList();
  }

  Future<Child?> child(int id) async {
    final db = await _db;
    final rows = await db.query('children', where: 'id = ?', whereArgs: [id], limit: 1);

    if (rows.isEmpty) return null;

    return Child.fromJson(jsonDecode(rows.first['payload'] as String) as Map<String, dynamic>);
  }

  Future<void> replaceChildren(List<Child> children, {int? guardianId}) async {
    final db = await _db;

    await db.transaction((txn) async {
      await txn.delete('children');

      for (final child in children) {
        await txn.insert('children', {
          'id': child.id,
          'guardian_id': guardianId,
          'payload': jsonEncode(child.toJson()),
          'snapshot_at': DateTime.now().toIso8601String(),
        });
      }
    });
  }

  Future<Map<int, ProgressEntry>> progressFor(int childId) async {
    final db = await _db;
    final rows = await db.query('child_progress', where: 'child_id = ?', whereArgs: [childId]);

    return {
      for (final row in rows)
        row['mission_id'] as int: ProgressEntry(
          missionId: row['mission_id'] as int,
          status: row['status'] as String? ?? 'in_progress',
          starsEarned: row['stars_earned'] as int? ?? 0,
          completedAt: DateTime.tryParse(row['completed_at'] as String? ?? ''),
        ),
    };
  }

  /// Record a finished mission locally. Stars only ever move up.
  Future<void> recordProgress({
    required int childId,
    required int missionId,
    required bool passed,
    required int stars,
    DateTime? completedAt,
  }) async {
    final db = await _db;
    final existing = await db.query(
      'child_progress',
      where: 'child_id = ? AND mission_id = ?',
      whereArgs: [childId, missionId],
      limit: 1,
    );

    final previousStars = existing.isEmpty ? 0 : (existing.first['stars_earned'] as int? ?? 0);
    final wasCompleted = existing.isNotEmpty && existing.first['status'] == 'completed';

    await db.insert(
      'child_progress',
      {
        'child_id': childId,
        'mission_id': missionId,
        'status': (passed || wasCompleted) ? 'completed' : 'in_progress',
        'stars_earned': stars > previousStars ? stars : previousStars,
        'completed_at': (completedAt ?? DateTime.now()).toIso8601String(),
      },
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  /// Badges the server has awarded, kept so the map can show them offline.
  ///
  /// These live in the settings table rather than a table of their own: they are
  /// a small, server-owned list that is replaced wholesale on every sync, so a
  /// schema change would buy nothing.
  Future<void> saveBadges(int childId, List<EarnedBadge> badges) async {
    final db = await _db;

    await db.insert(
      'settings',
      {'key': 'badges:$childId', 'value': jsonEncode(badges.map((b) => b.toJson()).toList())},
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  Future<List<EarnedBadge>> badges(int childId) async {
    final db = await _db;
    final rows = await db.query('settings', where: 'key = ?', whereArgs: ['badges:$childId'], limit: 1);

    if (rows.isEmpty) return const [];

    try {
      final decoded = jsonDecode(rows.first['value'] as String? ?? '[]');

      return (decoded as List)
          .map((e) => EarnedBadge.fromJson((e as Map).cast<String, dynamic>()))
          .toList();
    } catch (_) {
      return const [];
    }
  }

  /// A small named value kept on this device only.
  ///
  /// Used for choices that are about the device rather than the account — when
  /// the practice reminder should fire, for instance. A second phone in the
  /// same family reasonably has its own answer.
  Future<String?> setting(String key) async {
    final db = await _db;
    final rows = await db.query('settings', where: 'key = ?', whereArgs: [key], limit: 1);

    return rows.isEmpty ? null : rows.first['value'] as String?;
  }

  Future<void> putSetting(String key, String value) async {
    final db = await _db;

    await db.insert(
      'settings',
      {'key': key, 'value': value},
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  Future<void> clearSetting(String key) async {
    final db = await _db;
    await db.delete('settings', where: 'key = ?', whereArgs: [key]);
  }

  /// Has this child already finished this mission?
  ///
  /// Asked before a completion is written, because a treasure chest is for a
  /// mission finished for the first time.
  Future<bool> isCompleted(int childId, int missionId) async {
    final db = await _db;
    final rows = await db.query(
      'child_progress',
      where: 'child_id = ? AND mission_id = ? AND status = ?',
      whereArgs: [childId, missionId, 'completed'],
      limit: 1,
    );

    return rows.isNotEmpty;
  }

  /// How many missions this child has finished, for the chest count and the
  /// sticker book.
  Future<int> completedCount(int childId) async {
    final db = await _db;
    final rows = await db.rawQuery(
      'SELECT COUNT(*) AS total FROM child_progress WHERE child_id = ? AND status = ?',
      [childId, 'completed'],
    );

    return (rows.first['total'] as num?)?.toInt() ?? 0;
  }

  /// A once-only marker, like "the devotional was shown today".
  ///
  /// Deliberately device-local and never synced: whether this tablet has
  /// already shown today's verse is nobody else's business, and a child who
  /// moves to the family phone at lunchtime may reasonably see it again.
  Future<bool> flag(String key) async {
    final db = await _db;
    final rows = await db.query('settings', where: 'key = ?', whereArgs: [key], limit: 1);

    return rows.isNotEmpty;
  }

  Future<void> setFlag(String key) async {
    final db = await _db;

    await db.insert(
      'settings',
      {'key': key, 'value': DateTime.now().toIso8601String()},
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  /// Fold the server's view into the local one.
  Future<void> applySnapshot(ChildSnapshot snapshot) async {
    final db = await _db;

    await saveChild(snapshot.child);
    await saveBadges(snapshot.child.id, snapshot.badges);

    await db.transaction((txn) async {
      for (final entry in snapshot.progress) {
        final existing = await txn.query(
          'child_progress',
          where: 'child_id = ? AND mission_id = ?',
          whereArgs: [snapshot.child.id, entry.missionId],
          limit: 1,
        );

        final localStars = existing.isEmpty ? 0 : (existing.first['stars_earned'] as int? ?? 0);
        final localCompleted = existing.isNotEmpty && existing.first['status'] == 'completed';

        await txn.insert(
          'child_progress',
          {
            'child_id': snapshot.child.id,
            'mission_id': entry.missionId,
            'status': (entry.isCompleted || localCompleted) ? 'completed' : entry.status,
            'stars_earned': entry.starsEarned > localStars ? entry.starsEarned : localStars,
            'completed_at': entry.completedAt?.toIso8601String() ??
                (existing.isEmpty ? null : existing.first['completed_at'] as String?),
          },
          conflictAlgorithm: ConflictAlgorithm.replace,
        );
      }
    });
  }

  /// Log which questions were served, so the seven-day exclusion filter works
  /// while the device is offline.
  Future<void> logQuestionAttempts({
    required int childId,
    required int missionId,
    required Map<int, bool> results,
    DateTime? at,
  }) async {
    final db = await _db;
    final timestamp = (at ?? DateTime.now()).toIso8601String();

    final batch = db.batch();
    results.forEach((questionId, correct) {
      batch.insert('question_attempt_log', {
        'child_id': childId,
        'mission_id': missionId,
        'question_id': questionId,
        'attempted_at': timestamp,
        'is_correct': correct ? 1 : 0,
      });
    });

    await batch.commit(noResult: true);
  }

  /// Minutes played today, from this device's own sessions.
  Future<int> secondsPlayedToday(int childId, {DateTime? now}) async {
    final db = await _db;
    final day = _localDay(now ?? DateTime.now());

    final rows = await db.rawQuery(
      'SELECT COALESCE(SUM(seconds), 0) AS total FROM screen_time_sessions WHERE child_id = ? AND day_local = ?',
      [childId, day],
    );

    return (rows.first['total'] as num?)?.toInt() ?? 0;
  }

  Future<void> addScreenTime({
    required int childId,
    required int seconds,
    DateTime? at,
  }) async {
    if (seconds <= 0) return;

    final db = await _db;
    final when = at ?? DateTime.now();

    await db.insert('screen_time_sessions', {
      'id': '${childId}_${when.microsecondsSinceEpoch}',
      'child_id': childId,
      'started_at': when.toIso8601String(),
      'ended_at': when.toIso8601String(),
      'seconds': seconds,
      'day_local': _localDay(when),
    });
  }

  static String _localDay(DateTime when) {
    final local = when.toLocal();
    return '${local.year.toString().padLeft(4, '0')}-'
        '${local.month.toString().padLeft(2, '0')}-'
        '${local.day.toString().padLeft(2, '0')}';
  }
}
