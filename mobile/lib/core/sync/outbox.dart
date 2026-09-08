import 'dart:convert';

import 'package:sqflite/sqflite.dart';
import 'package:uuid/uuid.dart';

import '../db/local_database.dart';
import '../models/learning_event.dart';

/// The durable queue of everything the server has not been told yet.
///
/// Nothing a child does is held in memory waiting for a network. It is written
/// here first, and it stays here until the server acknowledges the id. That is
/// what makes it safe to close the app in the middle of a mission on a bus.
class Outbox {
  Outbox([LocalDatabase? database]) : _database = database ?? LocalDatabase.instance;

  final LocalDatabase _database;
  final Uuid _uuid = const Uuid();

  Future<Database> get _db async => _database.database;

  /// Append an event. Returns the id the server will know it by.
  Future<String> add({
    required String type,
    required Map<String, dynamic> payload,
    int? childId,
    DateTime? at,
  }) async {
    final db = await _db;
    final id = _uuid.v4();
    final seq = await _nextSeq();

    await db.insert('outbox_events', {
      'id': id,
      'child_id': childId,
      'type': type,
      'payload': jsonEncode(payload),
      // The device's own offset travels with the event: streaks and screen time
      // are counted in the family's local day, not in UTC.
      'client_ts': (at ?? DateTime.now()).toIso8601String(),
      'seq': seq,
      'status': 'pending',
      'attempts': 0,
    });

    return id;
  }

  Future<List<LearningEvent>> pending({int limit = 200, int? childId}) async {
    final db = await _db;

    final rows = await db.query(
      'outbox_events',
      where: childId == null ? 'status = ?' : 'status = ? AND (child_id = ? OR child_id IS NULL)',
      whereArgs: childId == null ? ['pending'] : ['pending', childId],
      orderBy: 'seq ASC',
      limit: limit,
    );

    return rows.map((row) {
      return LearningEvent(
        id: row['id'] as String,
        type: row['type'] as String,
        seq: row['seq'] as int,
        clientTs: DateTime.tryParse(row['client_ts'] as String? ?? '') ?? DateTime.now(),
        childId: row['child_id'] as int?,
        payload: (jsonDecode(row['payload'] as String? ?? '{}') as Map).cast<String, dynamic>(),
      );
    }).toList();
  }

  Future<int> pendingCount({int? childId}) async {
    final db = await _db;

    final rows = await db.rawQuery(
      childId == null
          ? 'SELECT COUNT(*) AS c FROM outbox_events WHERE status = ?'
          : 'SELECT COUNT(*) AS c FROM outbox_events WHERE status = ? AND (child_id = ? OR child_id IS NULL)',
      childId == null ? ['pending'] : ['pending', childId],
    );

    return (rows.first['c'] as num?)?.toInt() ?? 0;
  }

  /// The server took these. They are gone.
  Future<void> acknowledge(Iterable<String> ids) async {
    if (ids.isEmpty) return;

    final db = await _db;
    final list = ids.toList();
    final placeholders = List.filled(list.length, '?').join(',');

    await db.delete('outbox_events', where: 'id IN ($placeholders)', whereArgs: list);
  }

  /// The server took these but could not use them. Keep them, marked, so a
  /// person can look at what went wrong instead of the data vanishing.
  Future<void> quarantine(Iterable<RejectedEvent> rejected) async {
    if (rejected.isEmpty) return;

    final db = await _db;
    final batch = db.batch();

    for (final event in rejected) {
      batch.update(
        'outbox_events',
        {'status': 'quarantined', 'last_error': event.reason},
        where: 'id = ?',
        whereArgs: [event.id],
      );
    }

    await batch.commit(noResult: true);
  }

  /// The attempt failed before the server saw it. Count the try and move on.
  Future<void> recordFailure(Iterable<String> ids, String error) async {
    if (ids.isEmpty) return;

    final db = await _db;
    final list = ids.toList();
    final placeholders = List.filled(list.length, '?').join(',');

    await db.rawUpdate(
      'UPDATE outbox_events SET attempts = attempts + 1, last_error = ? WHERE id IN ($placeholders)',
      [error, ...list],
    );
  }

  Future<List<Map<String, Object?>>> quarantined() async {
    final db = await _db;

    return db.query('outbox_events', where: 'status = ?', whereArgs: ['quarantined'], orderBy: 'seq DESC');
  }

  /// Monotonic per device, so the server can apply a batch in the order things
  /// actually happened even when timestamps collide.
  Future<int> _nextSeq() async {
    final db = await _db;
    final rows = await db.rawQuery('SELECT COALESCE(MAX(seq), 0) AS m FROM outbox_events');
    final highest = (rows.first['m'] as num?)?.toInt() ?? 0;

    final settings = await db.query('settings', where: 'key = ?', whereArgs: ['outbox_seq'], limit: 1);
    final stored = settings.isEmpty ? 0 : int.tryParse(settings.first['value'] as String? ?? '0') ?? 0;

    final next = (highest > stored ? highest : stored) + 1;

    await db.insert(
      'settings',
      {'key': 'outbox_seq', 'value': '$next'},
      conflictAlgorithm: ConflictAlgorithm.replace,
    );

    return next;
  }
}
