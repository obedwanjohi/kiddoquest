import 'dart:convert';

import 'package:sqflite/sqflite.dart';

import '../../core/auth/auth_repository.dart';
import '../../core/db/local_database.dart';
import '../../core/models/parent_dashboard.dart';
import '../../core/network/api_client.dart';
import '../../core/network/api_exception.dart';

/// Loads the Parent Companion Zone and carries out what a parent does in it.
///
/// Every action is one the website's dashboard offers, sent to the API
/// equivalent of the form the website posts: assign a focus mission, save the
/// feature toggles, save a time limit, change the PIN, ask the coach.
class ParentDashboardRepository {
  ParentDashboardRepository({required this.api, required this.auth, LocalDatabase? database})
      : _database = database ?? LocalDatabase.instance;

  final ApiClient api;
  final AuthRepository auth;
  final LocalDatabase _database;

  static const cacheKey = 'parent_dashboard';

  Future<ParentDashboard> load({int? childId}) async {
    try {
      final json = await api.get('/parent/dashboard', query: {'child_id': ?childId});

      final db = await _database.database;
      await db.insert(
        'settings',
        {'key': '$cacheKey:${json['selected_child_id']}', 'value': jsonEncode(json)},
        conflictAlgorithm: ConflictAlgorithm.replace,
      );
      await db.insert(
        'settings',
        {'key': '$cacheKey:last', 'value': jsonEncode(json)},
        conflictAlgorithm: ConflictAlgorithm.replace,
      );

      return ParentDashboard.fromJson(json);
    } on ApiException {
      final cached = await _cached(childId);
      if (cached != null) return cached;
      rethrow;
    }
  }

  Future<ParentDashboard?> _cached(int? childId) async {
    final db = await _database.database;

    for (final key in ['$cacheKey:${childId ?? 'last'}', '$cacheKey:last']) {
      final rows = await db.query('settings', where: 'key = ?', whereArgs: [key], limit: 1);
      if (rows.isEmpty) continue;

      try {
        return ParentDashboard.fromJson(
          (jsonDecode(rows.first['value'] as String) as Map).cast<String, dynamic>(),
          fromCache: true,
        );
      } catch (_) {
        continue;
      }
    }

    return null;
  }

  /// `POST parent/assign-mission`. Null clears the assignment.
  Future<void> assignFocus(int childId, int? missionId) async {
    await api.patch('/children/$childId/focus-mission', body: {'mission_id': missionId});
  }

  /// `POST parent/update-devotional-settings`.
  Future<void> saveFeatureControls({required bool devotional, required bool songs}) async {
    await api.patch('/parent/settings', body: {
      'enable_devotional': devotional,
      'enable_songs_hub': songs,
    });
  }

  /// `POST parent/update-screentime`. Zero is Unlimited.
  Future<void> saveTimeLimit(int childId, int minutes) async {
    await api.patch('/children/$childId/screen-time', body: {'minutes': minutes});
  }

  /// `POST parent/update-pin`.
  ///
  /// The website lets an unlocked parent zone change the PIN with nothing
  /// more; the API allows the same with the token the PIN gate issued. Returns
  /// false when that token has lapsed and the zone has to be unlocked again.
  Future<bool> updatePin(String pin) async {
    final token = auth.parentZoneToken;

    if (token == null) return false;

    try {
      await api.patch(
        '/parent/pin',
        body: {'new_pin': pin},
        headers: {'Authorization': 'Bearer $token'},
      );
    } on ApiException catch (error) {
      if (error.statusCode == 401 || error.statusCode == 422) return false;
      rethrow;
    }

    await auth.rememberPin(pin);

    return true;
  }

  /// `POST parent/ask-ai`.
  Future<String> askCoach(int childId, String question) async {
    final response = await api.post('/parent/coach', body: {'child_id': childId, 'question': question});

    return response['answer'] as String? ?? '';
  }
}
