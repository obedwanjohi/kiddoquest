import 'package:flutter_test/flutter_test.dart';
import 'package:kiddoquest/core/models/avatars.dart';
import 'package:kiddoquest/core/models/child.dart';
import 'package:kiddoquest/core/models/parent_dashboard.dart';
import 'package:kiddoquest/core/models/snapshot.dart';
import 'package:kiddoquest/features/map/site_map.dart';

void main() {
  group('grade stages, as the website picks them', () {
    final today = DateTime(2026, 9, 14);

    test('a birthday picks the same level Child::recommendLevel does', () {
      expect(GradeStage.levelForBirthdate(DateTime(2023, 9, 14), now: today), 'Play Group');
      expect(GradeStage.levelForBirthdate(DateTime(2022, 9, 14), now: today), 'PP1');
      expect(GradeStage.levelForBirthdate(DateTime(2021, 9, 14), now: today), 'PP2');
      expect(GradeStage.levelForBirthdate(DateTime(2020, 9, 14), now: today), 'Grade 1');
      expect(GradeStage.levelForBirthdate(DateTime(2019, 9, 14), now: today), 'Grade 2');
      expect(GradeStage.levelForBirthdate(DateTime(2015, 1, 1), now: today), 'Grade 3');
    });

    test('a birthday later this year has not happened yet', () {
      // Four next week, so still three today.
      expect(GradeStage.levelForBirthdate(DateTime(2022, 9, 21), now: today), 'Play Group');
    });

    test('every grade pill estimates a birthday that maps back to itself', () {
      for (final stage in GradeStage.all) {
        final estimated = DateTime(today.year - stage.years, today.month, today.day);
        expect(GradeStage.levelForBirthdate(estimated, now: today), stage.code, reason: stage.label);
      }
    });

    test('the buddy table matches the website', () {
      expect(Avatars.all, hasLength(19));
      expect(Avatars.nameFor('lion'), 'Leo the Lion');
      expect(Avatars.find('dragon')!.role, 'Fire Champion');
      expect(Avatars.emojiFor('not-a-buddy'), '🧒');
      expect(Avatars.nameFor('not-a-buddy'), 'Friend');
      expect(Avatars.hatFor('hat_crown'), '👑');
    });

    test('a child who has never played is NEW', () {
      const fresh = Child(id: 1, name: 'Zuri');
      final played = Child(id: 2, name: 'Liam', avatar: 'panda', lastPlayedAt: DateTime(2026, 9, 1));

      expect(fresh.hasPlayed, isFalse);
      expect(played.hasPlayed, isTrue);
      expect(played.avatarName, 'Pip the Panda');
    });
  });

  group('the adventure map', () {
    final map = SiteMap.fromJson({
      'songs_unlocked': false,
      'remaining_minutes': 12,
      'worlds': [
        {
          'id': 13,
          'slug': 'line-tracing-trail',
          'name': 'Line & Pattern Trail',
          'subject_name': 'Tracing & Writing ✏️',
          'subject_category': 'tracing',
          'pack': {'pack_id': 'all-trace-line-tracing-trail', 'version': 2, 'sha256': 'abc'},
          'missions': [
            {'id': 1, 'title': 'Straight Lines', 'status': null, 'stars': 0},
            {'id': 2, 'title': 'Slanted Rain Lines', 'status': 'completed', 'stars': 3},
          ],
        },
        {
          'id': 15,
          'slug': 'speak-repeat-safari',
          'name': 'Speak & Repeat Safari',
          'subject_category': 'speak',
          'pack': null,
          'missions': [
            {'id': 9, 'title': 'Easy — Animals'},
          ],
        },
      ],
    });

    test('reads worlds, packs and missions the website lists', () {
      expect(map.worlds.map((w) => w.name), ['Line & Pattern Trail', 'Speak & Repeat Safari']);
      expect(map.worlds.first.packId, 'all-trace-line-tracing-trail');
      expect(map.worlds.first.packVersion, 2);
      expect(map.worlds.last.packId, isNull);
      expect(map.worlds.first.completedCount, 1);
      expect(map.remainingMinutes, 12);
    });

    test('the subject tabs filter by the website\'s category', () {
      expect(map.forSubject('all'), hasLength(2));
      expect(map.forSubject('tracing').single.slug, 'line-tracing-trail');
      expect(map.forSubject('cre'), isEmpty);
    });

    test('a mission finished offline shows as finished before the server knows', () {
      final local = {
        1: const ProgressEntry(missionId: 1, status: 'completed', starsEarned: 2),
      };

      final overlaid = map.worlds.first.withProgress(local);

      expect(overlaid.missions.first.isCompleted, isTrue);
      expect(overlaid.missions.first.stars, 2);
      expect(overlaid.completedCount, 2);
    });

    test('the device never lowers what the server already knows', () {
      final local = {
        2: const ProgressEntry(missionId: 2, status: 'in_progress', starsEarned: 1),
      };

      final overlaid = map.worlds.first.withProgress(local);

      expect(overlaid.missions.last.isCompleted, isTrue);
      expect(overlaid.missions.last.stars, 3);
    });

    test('survives the cache round trip', () {
      final again = SiteMap.fromJson(map.toJson());

      expect(again.worlds.first.missions, hasLength(2));
      expect(again.worlds.first.packSha256, 'abc');
    });
  });

  group('the parent dashboard', () {
    final dashboard = ParentDashboard.fromJson({
      'guardian': {'enable_devotional': true, 'enable_songs_hub': false, 'has_custom_pin': false, 'default_pin': '1234'},
      'children': [
        {'id': 1, 'name': 'Emma', 'avatar': 'lion', 'total_stars': 32, 'daily_time_limit_minutes': 0},
      ],
      'selected_child_id': 1,
      'report': {
        'passed_missions': 12,
        'accuracy_rate': 82,
        'learning_time_today': '10 mins',
        'streak_days': 6,
        'can_do_now': ['Mastered Count Them 🍎'],
        'learning_next': ['Safari Apple Counter 🍎'],
        'skills_heat_map': [
          {'name': 'Mathematics Activities', 'score': 79, 'bar': 6, 'total': 8},
        ],
        'growth': {'growth_label': '⭐ Consistent (100%)', 'growth_percent': 0},
        'mistake_action': {
          'mistake': 'Struggled on School Bag Counter',
          'activity': 'Practice counting 3 physical objects',
          'has_struggle': true,
        },
        'mission_history': [
          {
            'mission_title': 'School Bag Counter 🎒',
            'attempts_count': 1,
            'best_stars': 2,
            'last_played': '6 days ago',
            'attempts': [
              {'attempt': 1, 'score': '63%', 'date': '6 days ago'},
            ],
            'mistakes': ['How many school bags do you see?'],
          },
        ],
        'assigned_mission': {'id': 11, 'title': 'School Bag Counter'},
      },
      'missions': [
        {'id': 11, 'title': 'School Bag Counter'},
      ],
    });

    test('reads the website\'s report for the selected child', () {
      final report = dashboard.report!;

      expect(dashboard.selectedChild!.name, 'Emma');
      expect(report.accuracyRate, 82);
      expect(report.learningTimeToday, '10 mins');
      expect(report.growthLabel, '⭐ Consistent (100%)');
      expect(report.heatMap.single.bar, 6);
      expect(report.hasStruggle, isTrue);
      expect(report.assignedMission!.id, 11);
    });

    test('the drilldown carries the attempt log and the real mistakes', () {
      final row = dashboard.report!.history.single;

      expect(row.attempts.single.score, '63%');
      expect(row.mistakes, ['How many school bags do you see?']);
    });

    test('the guardian settings drive the controls tab', () {
      expect(dashboard.guardian.enableSongsHub, isFalse);
      expect(dashboard.guardian.hasCustomPin, isFalse);
      expect(dashboard.missions.single.title, 'School Bag Counter');
    });

    test('a report with nothing in it still draws', () {
      final empty = ParentDashboard.fromJson(const {'guardian': {}, 'report': {}});

      expect(empty.children, isEmpty);
      expect(empty.selectedChild, isNull);
      expect(empty.report!.growthLabel, '🌱 Ready to Start');
      expect(empty.report!.history, isEmpty);
    });
  });
}
