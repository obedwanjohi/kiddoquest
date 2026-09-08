import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:kiddoquest/app/providers.dart';
import 'package:kiddoquest/core/auth/auth_repository.dart';
import 'package:kiddoquest/core/models/child.dart';
import 'package:kiddoquest/core/network/api_client.dart';
import 'package:kiddoquest/core/platform/form_factor.dart';
import 'package:kiddoquest/design/theme.dart';
import 'package:kiddoquest/features/auth/approve_tv_screen.dart';
import 'package:kiddoquest/features/auth/tv_sign_in_screen.dart';

/// An auth repository that never touches the network.
///
/// It is a real AuthRepository with the two device-code calls replaced, so the
/// screens under test are wired exactly as they are in the app.
class _FakeAuth extends AuthRepository {
  _FakeAuth({this.code, this.failWith}) : super(api: ApiClient());

  final DeviceCode? code;
  final Object? failWith;

  int codesRequested = 0;
  final List<String> approved = [];

  @override
  Future<DeviceCode> requestDeviceCode() async {
    codesRequested++;

    if (failWith != null) throw failWith!;

    return code ??
        DeviceCode(
          code: 'K7QP2M',
          expiresAt: DateTime.now().add(const Duration(minutes: 10)),
          pollSeconds: 60,
          approveUrl: 'https://kiddoquest.co.ke/tv',
        );
  }

  @override
  Future<({Guardian guardian, List<Child> children})?> claimDeviceCode(String code) async => null;

  @override
  Future<void> approveDeviceCode(String code) async {
    if (failWith != null) throw failWith!;
    approved.add(code);
  }
}

class _FakeSession extends SessionNotifier {
  @override
  SessionState build() => const SessionState(loading: false);
}

void main() {
  Widget harness(Widget screen, _FakeAuth auth, {FormFactor formFactor = FormFactor.television}) {
    return ProviderScope(
      overrides: [
        authRepositoryProvider.overrideWithValue(auth),
        sessionProvider.overrideWith(_FakeSession.new),
      ],
      child: MaterialApp(
        theme: KidTheme.light(formFactor),
        home: FormFactorScope(formFactor: formFactor, child: screen),
      ),
    );
  }

  group('DeviceCode', () {
    test('reads what the server sent', () {
      final code = DeviceCode.fromJson({
        'code': 'K7QP2M',
        'expires_at': DateTime.now().add(const Duration(minutes: 10)).toIso8601String(),
        'poll_seconds': 5,
        'approve_url': 'https://kiddoquest.co.ke/tv',
      });

      expect(code.code, 'K7QP2M');
      expect(code.pollSeconds, 5);
      expect(code.isExpired, isFalse);
      expect(code.remaining.inMinutes, greaterThan(8));
    });

    test('a code past its time is expired and has nothing left to give', () {
      final code = DeviceCode(
        code: 'OLD123',
        expiresAt: DateTime.now().subtract(const Duration(minutes: 1)),
      );

      expect(code.isExpired, isTrue);
      expect(code.remaining, Duration.zero);
    });

    test('a reply with no expiry still produces a usable code', () {
      final code = DeviceCode.fromJson({'code': 'ABC123'});

      expect(code.isExpired, isFalse);
      expect(code.pollSeconds, 3);
    });
  });

  group('the television sign-in screen', () {
    testWidgets('asks for a code and shows it one character at a time', (tester) async {
      final auth = _FakeAuth();

      await tester.pumpWidget(harness(const TvSignInScreen(), auth));
      await tester.pump();

      expect(auth.codesRequested, 1);

      // Six characters, six tiles, so it can be read across a room.
      for (final character in 'K7QP2M'.split('')) {
        expect(find.text(character), findsOneWidget);
      }

      expect(find.textContaining('Sign in a TV'), findsOneWidget);
      expect(find.textContaining('kiddoquest.co.ke/tv'), findsOneWidget);
    });

    testWidgets('never asks anyone to type a password on a television', (tester) async {
      await tester.pumpWidget(harness(const TvSignInScreen(), _FakeAuth()));
      await tester.pump();

      expect(find.byType(TextField), findsNothing);
      expect(find.textContaining('password', findRichText: true), findsNothing);
    });

    testWidgets('says how long the code is good for', (tester) async {
      await tester.pumpWidget(harness(const TvSignInScreen(), _FakeAuth()));
      await tester.pump();

      expect(find.textContaining('another 9 minutes'), findsOneWidget);
    });

    testWidgets('counts down in seconds once the code is nearly out of time', (tester) async {
      final auth = _FakeAuth(
        code: DeviceCode(
          code: 'ZZ9911',
          expiresAt: DateTime.now().add(const Duration(seconds: 40)),
          pollSeconds: 60,
        ),
      );

      await tester.pumpWidget(harness(const TvSignInScreen(), auth));
      await tester.pump();

      expect(find.textContaining('seconds'), findsOneWidget);
    });

    testWidgets('a failure leaves a way forward rather than a permanent spinner', (tester) async {
      final auth = _FakeAuth(failWith: StateError('the reply made no sense'));

      await tester.pumpWidget(harness(const TvSignInScreen(), auth));
      await tester.pump();

      // A television has no back button a parent can reach for, so a screen
      // stuck on a spinner is a dead device.
      expect(find.byType(CircularProgressIndicator), findsNothing);
      expect(find.textContaining('Something went wrong'), findsOneWidget);
      expect(find.text('Try again'), findsOneWidget);

      await tester.tap(find.text('Try again'));
      await tester.pump();

      expect(auth.codesRequested, 2);
    });
  });

  group('approving a television from the phone', () {
    testWidgets('refuses a code too short to be real without calling the server', (tester) async {
      final auth = _FakeAuth();

      await tester.pumpWidget(
        harness(const ApproveTvScreen(), auth, formFactor: FormFactor.compact),
      );
      await tester.pump();

      await tester.enterText(find.byType(TextField), 'AB');
      await tester.tap(find.text('Approve this TV'));
      await tester.pump();

      expect(auth.approved, isEmpty);
      expect(find.textContaining('Type the code'), findsOneWidget);
    });

    testWidgets('sends the code upper-cased and says so plainly when it lands', (tester) async {
      final auth = _FakeAuth();

      await tester.pumpWidget(
        harness(const ApproveTvScreen(), auth, formFactor: FormFactor.compact),
      );
      await tester.pump();

      await tester.enterText(find.byType(TextField), 'k7qp2m');
      await tester.tap(find.text('Approve this TV'));
      await tester.pumpAndSettle();

      expect(auth.approved, ['K7QP2M']);
      expect(find.textContaining('is signed in'), findsOneWidget);
    });
  });
}
