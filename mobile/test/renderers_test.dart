import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:kiddoquest/core/models/pack.dart';
import 'package:kiddoquest/core/platform/form_factor.dart';
import 'package:kiddoquest/core/scoring/question_scorer.dart';
import 'package:kiddoquest/design/theme.dart';
import 'package:kiddoquest/features/mission/renderers/drag_sequence_renderer.dart';
import 'package:kiddoquest/features/mission/renderers/drag_sort_renderer.dart';
import 'package:kiddoquest/features/mission/renderers/matching_renderer.dart';
import 'package:kiddoquest/features/mission/renderers/memory_match_renderer.dart';
import 'package:kiddoquest/features/mission/renderers/renderer_kit.dart';
import 'package:kiddoquest/features/mission/renderers/speak_repeat_renderer.dart';
import 'package:kiddoquest/features/mission/renderers/tracing_renderer.dart';

/// Each renderer is checked twice over: that playing it correctly produces a
/// response, and that the response it produces is one the shared scorer marks
/// right. A renderer that builds an answer the scorer cannot read would show a
/// child doing everything correctly and still being told to try again.
void main() {
  const scorer = QuestionScorer();

  Widget harness(Widget child, {FormFactor formFactor = FormFactor.compact}) {
    return MaterialApp(
      theme: KidTheme.light(formFactor),
      home: FormFactorScope(
        formFactor: formFactor,
        child: Scaffold(body: SizedBox(width: 800, height: 800, child: child)),
      ),
    );
  }

  PackOption option(int id, String text, {String? matchKey, int sortOrder = 0, String? contentType}) {
    return PackOption(id: id, text: text, matchKey: matchKey, sortOrder: sortOrder, contentType: contentType);
  }

  group('matching', () {
    final question = PackQuestion(
      id: 1,
      type: 'matching',
      options: [
        option(10, '🐶', matchKey: 'dog', sortOrder: 1, contentType: 'left'),
        option(11, 'Puppy', matchKey: 'dog', sortOrder: 2, contentType: 'right'),
        option(12, '🐱', matchKey: 'cat', sortOrder: 3, contentType: 'left'),
        option(13, 'Kitten', matchKey: 'cat', sortOrder: 4, contentType: 'right'),
      ],
    );

    testWidgets('pairing everything submits pairs the scorer accepts', (tester) async {
      Map<String, dynamic>? submitted;

      await tester.pumpWidget(harness(MatchingRenderer(
        context_: RendererContext(
          question: question,
          media: const {},
          onSubmit: (response) => submitted = response,
        ),
      )));

      await tester.tap(find.text('🐶'));
      await tester.pump();
      await tester.tap(find.text('Puppy'));
      await tester.pump();
      await tester.tap(find.text('🐱'));
      await tester.pump();
      await tester.tap(find.text('Kitten'));
      await tester.pump();

      expect(submitted, isNotNull);
      expect(scorer.isCorrect(question.toScorerJson(), {'response': submitted}), isTrue);
    });

    testWidgets('crossing the pairs over is marked wrong, not accepted', (tester) async {
      Map<String, dynamic>? submitted;

      await tester.pumpWidget(harness(MatchingRenderer(
        context_: RendererContext(
          question: question,
          media: const {},
          onSubmit: (response) => submitted = response,
        ),
      )));

      await tester.tap(find.text('🐶'));
      await tester.pump();
      await tester.tap(find.text('Kitten'));
      await tester.pump();
      await tester.tap(find.text('🐱'));
      await tester.pump();
      await tester.tap(find.text('Puppy'));
      await tester.pump();

      expect(submitted, isNotNull);
      expect(scorer.isCorrect(question.toScorerJson(), {'response': submitted}), isFalse);
    });
  });

  group('drag sort', () {
    final question = PackQuestion(
      id: 2,
      type: 'drag_sort',
      metadata: const {
        'buckets': [
          {'name': 'Big'},
          {'name': 'Small'},
        ],
      },
      options: [
        option(20, '🐘', matchKey: 'Big', sortOrder: 1),
        option(21, '🐜', matchKey: 'Small', sortOrder: 2),
      ],
    );

    testWidgets('sorting into the right groups scores correct', (tester) async {
      Map<String, dynamic>? submitted;

      await tester.pumpWidget(harness(DragSortRenderer(
        context_: RendererContext(
          question: question,
          media: const {},
          onSubmit: (response) => submitted = response,
        ),
      )));

      // The first chip is presented; tap its group, then the next chip's group.
      await tester.tap(find.text('Big'));
      await tester.pump();
      await tester.tap(find.text('Small'));
      await tester.pump();

      expect(submitted, isNotNull);
      expect(scorer.isCorrect(question.toScorerJson(), {'response': submitted}), isTrue);
    });

    testWidgets('putting everything in one group scores wrong', (tester) async {
      Map<String, dynamic>? submitted;

      await tester.pumpWidget(harness(DragSortRenderer(
        context_: RendererContext(
          question: question,
          media: const {},
          onSubmit: (response) => submitted = response,
        ),
      )));

      await tester.tap(find.text('Big'));
      await tester.pump();
      await tester.tap(find.textContaining('Big'));
      await tester.pump();

      expect(submitted, isNotNull);
      expect(scorer.isCorrect(question.toScorerJson(), {'response': submitted}), isFalse);
    });
  });

  group('sequence', () {
    final question = PackQuestion(
      id: 3,
      type: 'drag_sequence',
      options: [
        option(30, 'One', sortOrder: 1),
        option(31, 'Two', sortOrder: 2),
        option(32, 'Three', sortOrder: 3),
      ],
    );

    testWidgets('nothing is submitted until the child says they are done', (tester) async {
      Map<String, dynamic>? submitted;

      await tester.pumpWidget(harness(DragSequenceRenderer(
        context_: RendererContext(
          question: question,
          media: const {},
          onSubmit: (response) => submitted = response,
        ),
      )));

      await tester.tap(find.text('One'));
      await tester.pump();
      await tester.tap(find.text('Two'));
      await tester.pump();

      expect(submitted, isNull, reason: 'a half-built order must not be judged');
    });

    testWidgets('the right order scores correct', (tester) async {
      Map<String, dynamic>? submitted;

      await tester.pumpWidget(harness(DragSequenceRenderer(
        context_: RendererContext(
          question: question,
          media: const {},
          onSubmit: (response) => submitted = response,
        ),
      )));

      for (final label in ['One', 'Two', 'Three']) {
        await tester.tap(find.text(label));
        await tester.pump();
      }

      await tester.tap(find.text('Check!'));
      await tester.pump();

      expect(submitted, isNotNull);
      expect(scorer.isCorrect(question.toScorerJson(), {'response': submitted}), isTrue);
    });
  });

  group('memory match', () {
    final question = PackQuestion(
      id: 4,
      type: 'memory_match',
      options: [
        option(40, 'Sun', matchKey: 'sun', sortOrder: 1),
        option(41, 'Sun', matchKey: 'sun', sortOrder: 2),
      ],
    );

    testWidgets('finding the pair submits a count the scorer accepts', (tester) async {
      Map<String, dynamic>? submitted;

      await tester.pumpWidget(harness(MemoryMatchRenderer(
        context_: RendererContext(
          question: question,
          media: const {},
          onSubmit: (response) => submitted = response,
        ),
      )));

      final cards = find.text('?');
      expect(cards, findsNWidgets(2));

      await tester.tap(cards.first);
      await tester.pump();
      await tester.tap(find.text('?'));
      await tester.pump();

      expect(submitted, isNotNull);
      expect(scorer.isCorrect(question.toScorerJson(), {'response': submitted}), isTrue);
    });
  });

  group('practice types', () {
    testWidgets('tracing counts for doing it', (tester) async {
      const question = PackQuestion(id: 5, type: 'tracing', metadata: {'character': 'A'});
      Map<String, dynamic>? submitted;

      await tester.pumpWidget(harness(TracingRenderer(
        context_: RendererContext(
          question: question,
          media: const {},
          onSubmit: (response) => submitted = response,
        ),
      )));

      await tester.tap(find.text("I'm done!"));
      await tester.pump();

      expect(submitted?['mode'], 'traced');
      expect(scorer.isCorrect(question.toScorerJson(), {'response': submitted}), isTrue);
    });

    testWidgets('a television traces in the air instead', (tester) async {
      const question = PackQuestion(id: 5, type: 'tracing', metadata: {'character': 'A'});
      Map<String, dynamic>? submitted;

      await tester.pumpWidget(harness(
        TracingRenderer(
          context_: RendererContext(
            question: question,
            media: const {},
            onSubmit: (response) => submitted = response,
          ),
        ),
        formFactor: FormFactor.television,
      ));

      await tester.tap(find.text('I traced it!'));
      await tester.pump();

      expect(submitted?['mode'], 'watched');
      expect(scorer.isCorrect(question.toScorerJson(), {'response': submitted}), isTrue);
    });

    testWidgets('saying the word counts, skipping does not', (tester) async {
      const question = PackQuestion(id: 6, type: 'speak_repeat', metadata: {'word': 'elephant'});
      Map<String, dynamic>? submitted;

      await tester.pumpWidget(harness(SpeakRepeatRenderer(
        context_: RendererContext(
          question: question,
          media: const {},
          onSubmit: (response) => submitted = response,
        ),
      )));

      expect(find.text('elephant'), findsOneWidget);

      await tester.tap(find.text('I said it!'));
      await tester.pump();
      expect(scorer.isCorrect(question.toScorerJson(), {'response': submitted}), isTrue);

      await tester.tap(find.text('Skip this one'));
      await tester.pump();
      expect(scorer.isCorrect(question.toScorerJson(), {'response': submitted}), isFalse);
    });
  });
}
