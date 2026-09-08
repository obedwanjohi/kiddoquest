import 'dart:convert';
import 'dart:io';

import 'package:flutter_test/flutter_test.dart';
import 'package:kiddoquest/core/scoring/question_scorer.dart';

/// Runs the same fixtures the PHP scorer is run against.
///
/// This is the contract between the app and the server. If the app said a child
/// got three stars and the server said one, the child would watch their stars
/// disappear on the next sync. These tests are what stops that.
void main() {
  const scorer = QuestionScorer();

  Map<String, dynamic> loadFixture(String name) {
    final file = File('../fixtures/scoring/$name');

    if (!file.existsSync()) {
      fail('Missing shared fixture ${file.path}. It is checked in next to the Laravel app.');
    }

    return jsonDecode(file.readAsStringSync()) as Map<String, dynamic>;
  }

  group('question scoring matches the shared fixtures', () {
    final cases = (loadFixture('questions.json')['cases'] as List).cast<Map<String, dynamic>>();

    test('the fixture file actually has cases', () {
      expect(cases, isNotEmpty);
    });

    for (final testCase in cases) {
      test(testCase['name'] as String, () {
        final actual = scorer.isCorrect(
          (testCase['question'] as Map).cast<String, dynamic>(),
          (testCase['answer'] as Map).cast<String, dynamic>(),
        );

        expect(actual, testCase['expected']);
      });
    }
  });

  group('mission scoring matches the shared fixtures', () {
    final cases = (loadFixture('missions.json')['cases'] as List).cast<Map<String, dynamic>>();

    for (final testCase in cases) {
      test(testCase['name'] as String, () {
        final result = scorer.scoreMission(
          questions: (testCase['questions'] as List).map((e) => (e as Map).cast<String, dynamic>()).toList(),
          answers: (testCase['answers'] as List).map((e) => (e as Map).cast<String, dynamic>()).toList(),
          passThresholdPercent: (testCase['pass_threshold_percent'] as num?)?.toInt() ?? 60,
          cap: (testCase['cap'] as num?)?.toInt(),
        );

        final expected = (testCase['expected'] as Map).cast<String, dynamic>();

        expect(result.score, expected['score'], reason: 'score');
        expect(result.total, expected['total'], reason: 'total');
        expect(result.percentage, expected['percentage'], reason: 'percentage');
        expect(result.stars, expected['stars'], reason: 'stars');
        expect(result.passed, expected['passed'], reason: 'passed');
      });
    }
  });

  group('star thresholds', () {
    test('90 percent and above is three stars', () {
      expect(QuestionScorer.starsFor(9, 10), 3);
      expect(QuestionScorer.starsFor(10, 10), 3);
    });

    test('60 to 89 percent is two stars', () {
      expect(QuestionScorer.starsFor(6, 10), 2);
      expect(QuestionScorer.starsFor(8, 10), 2);
    });

    test('30 to 59 percent is one star', () {
      expect(QuestionScorer.starsFor(3, 10), 1);
      expect(QuestionScorer.starsFor(5, 10), 1);
    });

    test('below 30 percent is no stars', () {
      expect(QuestionScorer.starsFor(2, 10), 0);
      expect(QuestionScorer.starsFor(0, 10), 0);
    });

    test('an empty mission cannot earn stars', () {
      expect(QuestionScorer.starsFor(0, 0), 0);
      expect(QuestionScorer.starsFor(5, 0), 0);
    });
  });

  group('type names', () {
    test('hyphens and underscores mean the same thing', () {
      expect(QuestionScorer.normalizeType('multiple-choice'), 'multiple_choice');
      expect(QuestionScorer.normalizeType('multiple_choice'), 'multiple_choice');
    });

    test('complete-pattern is rendered by the pattern renderer', () {
      expect(QuestionScorer.normalizeType('complete-pattern'), 'pattern');
      expect(QuestionScorer.normalizeType('complete_pattern'), 'pattern');
    });

    test('tap_answer is a multiple choice', () {
      expect(QuestionScorer.normalizeType('tap_answer'), 'multiple_choice');
    });

    test('an unknown type keeps its name rather than crashing', () {
      expect(QuestionScorer.normalizeType('some-new-thing'), 'some_new_thing');
    });
  });
}
