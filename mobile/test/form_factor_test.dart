import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:kiddoquest/core/platform/form_factor.dart';

/// The form factor decides every layout in the app, so the rule that picks it
/// is worth pinning down.
void main() {
  Widget harness({required Size size, required bool television, required void Function(FormFactor) onBuilt}) {
    return MediaQuery(
      data: MediaQueryData(size: size),
      child: Builder(
        builder: (context) {
          final resolved = resolveFormFactor(context, isTelevision: television);
          onBuilt(resolved);

          return FormFactorScope(
            formFactor: resolved,
            child: const SizedBox.shrink(),
          );
        },
      ),
    );
  }

  testWidgets('a phone in portrait is compact', (tester) async {
    late FormFactor result;
    await tester.pumpWidget(harness(
      size: const Size(390, 844),
      television: false,
      onBuilt: (value) => result = value,
    ));

    expect(result, FormFactor.compact);
  });

  testWidgets('a phone rotated to landscape is still a phone', (tester) async {
    late FormFactor result;
    await tester.pumpWidget(harness(
      size: const Size(844, 390),
      television: false,
      onBuilt: (value) => result = value,
    ));

    expect(result, FormFactor.compact, reason: 'the shortest side decides, not the width');
  });

  testWidgets('a tablet is expanded', (tester) async {
    late FormFactor result;
    await tester.pumpWidget(harness(
      size: const Size(1280, 800),
      television: false,
      onBuilt: (value) => result = value,
    ));

    expect(result, FormFactor.expanded);
  });

  testWidgets('a television wins whatever size it reports', (tester) async {
    late FormFactor result;
    await tester.pumpWidget(harness(
      size: const Size(1920, 1080),
      television: true,
      onBuilt: (value) => result = value,
    ));

    expect(result, FormFactor.television);
  });

  testWidgets('even a small television is a television', (tester) async {
    late FormFactor result;
    await tester.pumpWidget(harness(
      size: const Size(480, 320),
      television: true,
      onBuilt: (value) => result = value,
    ));

    expect(result, FormFactor.television);
  });

  test('density grows from phone to tablet to television', () {
    expect(FormFactor.compact.density, 1.0);
    expect(FormFactor.expanded.density, greaterThan(FormFactor.compact.density));
    expect(FormFactor.television.density, greaterThan(FormFactor.expanded.density));
  });

  test('only a television reserves an overscan margin', () {
    expect(FormFactor.compact.safeInsets, EdgeInsets.zero);
    expect(FormFactor.expanded.safeInsets, EdgeInsets.zero);
    expect(FormFactor.television.safeInsets.left, greaterThan(0));
  });

  test('a television fits four answers across, a phone two', () {
    expect(FormFactor.compact.answerColumns, 2);
    expect(FormFactor.television.answerColumns, 4);
  });
}
