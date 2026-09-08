import 'dart:convert';

import 'package:sqflite/sqflite.dart';

import '../../core/db/local_database.dart';
import '../../core/models/extras.dart';
import '../../core/network/api_client.dart';

/// Fetches the devotional list and the songs hub, and keeps the last copy.
///
/// The cache is the point rather than an optimisation: a devotional is opened
/// at bedtime and a song in a car, which are the two moments a Kenyan family is
/// least likely to have a working connection.
class ExtrasRepository {
  ExtrasRepository({required this.api, LocalDatabase? database})
      : _database = database ?? LocalDatabase.instance;

  static const cacheKey = 'extras';

  final ApiClient api;
  final LocalDatabase _database;

  Future<Extras?> cached() async {
    final db = await _database.database;
    final rows = await db.query('settings', where: 'key = ?', whereArgs: [cacheKey], limit: 1);

    if (rows.isEmpty) return null;

    try {
      return Extras.fromJson((jsonDecode(rows.first['value'] as String) as Map).cast<String, dynamic>());
    } catch (_) {
      return null;
    }
  }

  Future<Extras> refresh() async {
    final json = await api.get('/content/extras');
    final extras = Extras.fromJson(json);

    final db = await _database.database;
    await db.insert(
      'settings',
      {'key': cacheKey, 'value': jsonEncode(extras.toJson())},
      conflictAlgorithm: ConflictAlgorithm.replace,
    );

    return extras;
  }

  /// The server's copy when it can be had, the device's when it cannot.
  Future<Extras> load() async {
    try {
      return await refresh();
    } catch (_) {
      return await cached() ?? const Extras();
    }
  }
}
