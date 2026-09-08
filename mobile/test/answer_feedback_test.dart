import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:kiddoquest/core/platform/form_factor.dart';
import 'package:kiddoquest/design/components/answer_card.dart';
import 'package:kiddoquest/design/theme.dart';
import 'package:kiddoquest/design/tokens.dart';

/// The product's most important interaction rule, expressed as a test.
///
/// From the interaction guidelines: a wrong answer is grey and gentle. Red
/// belongs to leaving and deleting. Several of the website's own stylesheets
/// break this; the app cannot, because there is one component and this test.
void main() {
  Widget wrap(Widget child, {FormFactor formFactor = FormFactor.compact}) {
    return MaterialApp(
      theme: KidTheme.light(formFactor),
      home: FormFactorScope(
        formFactor: formFactor,
        child: Scaffold(body: Center(child: SizedBox(width: 200, height: 120, child: child))),
      ),
    );
  }

  BoxDecoration decorationOf(WidgetTester tester) {
    final container = tester.widget<AnimatedContainer>(
      find.byKey(const ValueKey('answer-card-surface')),
    );

    return container.decoration! as BoxDecoration;
  }

  testWidgets('a wrong answer is grey, never red', (tester) async {
    await tester.pumpWidget(wrap(
      const AnswerCard(state: AnswerState.gentleWrong, label: '4'),
    ));
    await tester.pump(const Duration(milliseconds: 400));

    final border = decorationOf(tester).border! as Border;

    expect(border.top.color, KidColors.wrong);
    expect(border.top.color, isNot(KidColors.danger));
  });

  testWidgets('a correct answer is green', (tester) async {
    await tester.pumpWidget(wrap(
      const AnswerCard(state: AnswerState.correct, label: '3'),
    ));
    await tester.pump(const Duration(milliseconds: 400));

    final border = decorationOf(tester).border! as Border;

    expect(border.top.color, KidColors.success);
  });

  testWidgets('a revealed answer is amber, so it reads as help not failure', (tester) async {
    await tester.pumpWidget(wrap(
      const AnswerCard(state: AnswerState.revealed, label: '3'),
    ));
    await tester.pump(const Duration(milliseconds: 400));

    final border = decorationOf(tester).border! as Border;

    expect(border.top.color, KidColors.amber);
  });

  testWidgets('a card that has been answered cannot be tapped again', (tester) async {
    var taps = 0;

    await tester.pumpWidget(wrap(
      AnswerCard(state: AnswerState.correct, label: '3', onPressed: () => taps++),
    ));

    await tester.tap(find.byType(AnswerCard), warnIfMissed: false);
    await tester.pump();

    expect(taps, 0);
  });

  testWidgets('an unanswered card can be tapped', (tester) async {
    var taps = 0;

    await tester.pumpWidget(wrap(
      AnswerCard(state: AnswerState.idle, label: '3', onPressed: () => taps++),
    ));

    await tester.tap(find.byType(AnswerCard));
    await tester.pump();

    expect(taps, 1);
  });

  testWidgets('a television shows the remote number key for each answer', (tester) async {
    await tester.pumpWidget(wrap(
      const AnswerCard(state: AnswerState.idle, label: 'Apple', index: 2),
      formFactor: FormFactor.television,
    ));

    expect(find.text('3'), findsOneWidget, reason: 'index 2 is the third answer, which is remote key 3');
  });

  testWidgets('a phone does not show number key hints', (tester) async {
    await tester.pumpWidget(wrap(
      const AnswerCard(state: AnswerState.idle, label: 'Apple', index: 2),
    ));

    expect(find.text('3'), findsNothing);
  });

  testWidgets('an answer card meets the minimum touch target', (tester) async {
    await tester.pumpWidget(wrap(
      const AnswerCard(state: AnswerState.idle, label: 'Apple'),
    ));

    final size = tester.getSize(find.byType(AnswerCard));

    expect(size.width, greaterThanOrEqualTo(KidTouch.min));
    expect(size.height, greaterThanOrEqualTo(KidTouch.min));
  });
}
