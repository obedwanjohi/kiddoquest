import 'package:flutter_test/flutter_test.dart';
import 'package:kiddoquest/core/models/parent_report.dart';

void main() {
  group('ParentReport', () {
    test('reads a full report', () {
      final report = ParentReport.fromJson({
        'child': {'id': 1, 'name': 'Emma', 'streak': 3, 'stars': 40, 'coins': 12, 'daily_limit_minutes': 30},
        'range_days': 7,
        'overview': {
          'minutes_today': 12,
          'minutes_in_range': 64,
          'missions_passed': 4,
          'questions_answered': 30,
          'accuracy_percent': 80,
          'days_played': 3,
          'days_in_range': 7,
          'daily': [
            {'day': '2026-09-07', 'minutes': 20, 'missions': 2, 'stars': 5},
          ],
        },
        'progress': {
          'can_do_now': ['Counting to ten'],
          'learning_next': [
            {'id': 9, 'title': 'Shapes'},
          ],
          'subjects': [
            {'name': 'Mathematics', 'code': 'MATH', 'answered': 20, 'accuracy_percent': 85},
          ],
        },
        'history': [
          {
            'mission_id': 3,
            'title': 'Counting Safari',
            'score': 4,
            'total': 6,
            'percentage': 67,
            'stars': 2,
            'passed': true,
            'minutes': 5,
            'completed_at': '2026-09-07T10:00:00+03:00',
            'mistakes': ['How many zebras?'],
          },
        ],
        'support': {'has_struggle': true, 'headline': 'Counting Safari: How many zebras?', 'activity': 'Count stones together.', 'mission_id': 3},
        'badges': [
          {'key': 'first_mission', 'name': 'First Steps', 'icon': '👣'},
        ],
        'generated_at': '2026-09-08T09:00:00+03:00',
      });

      expect(report.childName, 'Emma');
      expect(report.streak, 3);
      expect(report.overview.minutesToday, 12);
      expect(report.overview.daily.single.minutes, 20);
      expect(report.progress.subjects.single.accuracyPercent, 85);
      expect(report.progress.learningNext.single.title, 'Shapes');
      expect(report.history.single.mistakes, ['How many zebras?']);
      expect(report.support.hasStruggle, isTrue);
      expect(report.badges.single.name, 'First Steps');
      expect(report.hasActivity, isTrue);
    });

    test('survives a report with nothing in it', () {
      final report = ParentReport.fromJson({'child': <String, dynamic>{}});

      expect(report.childName, 'Your child');
      expect(report.overview.accuracyPercent, isNull);
      expect(report.history, isEmpty);
      expect(report.badges, isEmpty);
      expect(report.hasActivity, isFalse);
    });

    test('a child with no play yet is not treated as active', () {
      final report = ParentReport.fromJson({
        'child': {'name': 'Sam'},
        'overview': {'questions_answered': 0},
        'history': const [],
        'support': {'has_struggle': false, 'headline': 'Sam has not got stuck on anything yet.'},
      });

      expect(report.hasActivity, isFalse);
      expect(report.support.headline, contains('not got stuck'));
    });
  });
}
