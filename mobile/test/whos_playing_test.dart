import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:kiddoquest/app/providers.dart';
import 'package:kiddoquest/core/models/child.dart';
import 'package:kiddoquest/core/platform/form_factor.dart';
import 'package:kiddoquest/design/theme.dart';
import 'package:kiddoquest/features/profiles/whos_playing_screen.dart';

/// A session that is already signed in, so the screen can be tested without a
/// network, a database or secure storage.
class _FakeSession extends SessionNotifier {
  _FakeSession(this._state);

  final SessionState _state;

  @override
  SessionState build() => _state;
}

void main() {
  const guardian = Guardian(id: 1, name: 'Parent', email: 'parent@example.com');

  const children = [
    Child(id: 10, name: 'Zawadi', avatar: 'lion', level: 'Play Group', totalStars: 12),
    Child(id: 11, name: 'Baraka', avatar: 'panda', level: 'PP1', totalStars: 3),
  ];

  Widget harness(SessionState state, {FormFactor formFactor = FormFactor.compact}) {
    return ProviderScope(
      overrides: [
        sessionProvider.overrideWith(() => _FakeSession(state)),
      ],
      child: MaterialApp(
        theme: KidTheme.light(formFactor),
        home: FormFactorScope(formFactor: formFactor, child: const WhosPlayingScreen()),
      ),
    );
  }

  testWidgets('it asks who is playing and lists the children', (tester) async {
    await tester.pumpWidget(harness(
      const SessionState(loading: false, guardian: guardian, children: children),
    ));
    await tester.pump();

    expect(find.text('Who is playing?'), findsOneWidget);
    expect(find.text('Zawadi'), findsOneWidget);
    expect(find.text('Baraka'), findsOneWidget);
  });

  testWidgets('it offers a way to add another explorer', (tester) async {
    await tester.pumpWidget(harness(
      const SessionState(loading: false, guardian: guardian, children: children),
    ));
    await tester.pump();

    expect(find.text('Add an explorer'), findsOneWidget);
  });

  testWidgets('a family with no children is invited to add one', (tester) async {
    await tester.pumpWidget(harness(
      const SessionState(loading: false, guardian: guardian),
    ));
    await tester.pump();

    expect(find.text('No explorers yet'), findsOneWidget);
  });

  testWidgets('it shows Leo while it is still loading rather than an empty screen', (tester) async {
    await tester.pumpWidget(harness(const SessionState()));
    await tester.pump();

    expect(find.text('Finding your explorers…'), findsOneWidget);
  });

  testWidgets('the same screen composes on a television', (tester) async {
    tester.view.physicalSize = const Size(1920, 1080);
    tester.view.devicePixelRatio = 1.0;
    addTearDown(tester.view.reset);

    await tester.pumpWidget(harness(
      const SessionState(loading: false, guardian: guardian, children: children),
      formFactor: FormFactor.television,
    ));
    await tester.pump();

    expect(find.text('Who is playing?'), findsOneWidget);
    expect(find.text('Zawadi'), findsOneWidget);
    expect(tester.takeException(), isNull);
  });

  testWidgets('the same screen composes on a tablet', (tester) async {
    tester.view.physicalSize = const Size(1280, 800);
    tester.view.devicePixelRatio = 1.0;
    addTearDown(tester.view.reset);

    await tester.pumpWidget(harness(
      const SessionState(loading: false, guardian: guardian, children: children),
      formFactor: FormFactor.expanded,
    ));
    await tester.pump();

    expect(find.text('Who is playing?'), findsOneWidget);
    expect(tester.takeException(), isNull);
  });
}
