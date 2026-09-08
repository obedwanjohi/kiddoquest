import 'dart:convert';

import 'package:sqflite/sqflite.dart';

import '../../core/db/local_database.dart';
import '../../core/models/parent_report.dart';
import '../../core/network/api_client.dart';

/// Fetches the parent report, and keeps the last one it saw.
///
/// A parent opens this in the evening, often on the same patchy connection the
/// child plays over. So the cache is not an optimisation: the last report is
/// what the screen shows while the new one is in flight, and what it keeps
/// showing if the request never lands.
class ParentReportRepository {
  ParentReportRepository({required this.api, LocalDatabase? database})
      : _database = database ?? LocalDatabase.instance;

  final ApiClient api;
  final LocalDatabase _database;

  static String cacheKey(int childId, String range) => 'parent_report:$childId:$range';

  Future<ParentReport?> cached(int childId, String range) async {
    final db = await _database.database;
    final rows = await db.query(
      'settings',
      where: 'key = ?',
      whereArgs: [cacheKey(childId, range)],
      limit: 1,
    );

    if (rows.isEmpty) return null;

    try {
      return ParentReport.fromJson(
        (jsonDecode(rows.first['value'] as String) as Map).cast<String, dynamic>(),
      );
    } catch (_) {
      // A cache that no longer parses is worth less than nothing.
      return null;
    }
  }

  /// Ask the server, and remember the answer. Throws whatever the client throws;
  /// the caller decides whether a cached report is good enough.
  Future<ParentReport> refresh(int childId, String range) async {
    final json = await api.get('/parent/children/$childId/report', query: {'range': range});

    final db = await _database.database;
    await db.insert(
      'settings',
      {'key': cacheKey(childId, range), 'value': jsonEncode(json)},
      conflictAlgorithm: ConflictAlgorithm.replace,
    );

    return ParentReport.fromJson(json);
  }
}
