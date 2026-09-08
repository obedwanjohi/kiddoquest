import 'package:flutter_test/flutter_test.dart';
import 'package:kiddoquest/core/models/subscription.dart';

void main() {
  group('what the server offers', () {
    final state = SubscriptionState.fromJson({
      'entitlement': {
        'status': 'active',
        'plan': 'annual',
        'expires_at': DateTime.now().add(const Duration(days: 200)).toIso8601String(),
        'offline_grace_days': 7,
        'enforced': true,
      },
      'currency': 'KES',
      'plans': [
        {'type': 'monthly', 'name': 'Monthly Quest', 'amount': 200, 'days': 30},
        {
          'type': 'annual',
          'name': 'Annual Champion',
          'amount': 1800,
          'days': 365,
          'badge': 'Save 25%',
          'highlight': true,
        },
      ],
      'payments': [
        {'amount': 1800, 'status': 'completed', 'receipt': 'SLK7YH2N9P'},
      ],
    });

    test('reads the plans the server sent, in order', () {
      expect(state.plans.map((p) => p.type), ['monthly', 'annual']);
      expect(state.plans.first.amount, 200);
      expect(state.plans.last.badge, 'Save 25%');
      expect(state.plans.last.highlight, isTrue);
    });

    test('an active subscription is active', () {
      expect(state.entitlement.isActive, isTrue);
      expect(state.entitlement.plan, 'annual');
    });

    test('reads a receipt back', () {
      expect(state.payments.single.isPaid, isTrue);
      expect(state.payments.single.receipt, 'SLK7YH2N9P');
    });

    test('invents no price when the server sends none', () {
      // The app must never show a number it made up on a payment screen.
      final empty = SubscriptionState.fromJson(const {});

      expect(empty.plans, isEmpty);
      expect(empty.entitlement.isActive, isFalse);
      expect(empty.currency, 'KES');
    });
  });

  group('where a payment has got to', () {
    PaymentStatus of(String status) => PaymentStatus.fromJson({'status': status});

    test('completed is paid', () {
      expect(of('completed').progress, PaymentProgress.paid);
    });

    test('pending is the ordinary case while the prompt sits on the phone', () {
      expect(of('pending').progress, PaymentProgress.pending);
    });

    test('failed and cancelled both mean nothing was charged', () {
      expect(of('failed').progress, PaymentProgress.failed);
      expect(of('cancelled').progress, PaymentProgress.failed);
    });

    test('an unknown id is missing, not failed', () {
      // These are different: a parent whose payment we cannot find must not be
      // told their money is gone.
      expect(of('not_found').progress, PaymentProgress.missing);
    });

    test('an unrecognised status is treated as still pending', () {
      expect(of('something_new').progress, PaymentProgress.pending);
    });
  });

  group('the M-Pesa number', () {
    test('accepts how a Kenyan parent actually types it', () {
      expect(MpesaPhone.looksValid('0712345678'), isTrue);
      expect(MpesaPhone.looksValid('0112345678'), isTrue);
      expect(MpesaPhone.looksValid('254712345678'), isTrue);
      expect(MpesaPhone.looksValid('0712 345 678'), isTrue);
      expect(MpesaPhone.looksValid('+254 712 345 678'), isTrue);
    });

    test('rejects a typo before anyone gets a prompt', () {
      expect(MpesaPhone.looksValid(''), isFalse);
      expect(MpesaPhone.looksValid('07123'), isFalse);
      expect(MpesaPhone.looksValid('07123456789'), isFalse);
      expect(MpesaPhone.looksValid('not a phone'), isFalse);
      // A landline is not an M-Pesa line.
      expect(MpesaPhone.looksValid('020123456'), isFalse);
    });
  });
}
