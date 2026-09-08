import 'snapshot.dart';

/// What the family has paid for, and what they could buy.
class SubscriptionState {
  const SubscriptionState({
    this.entitlement = const Entitlement(),
    this.plans = const [],
    this.payments = const [],
    this.currency = 'KES',
  });

  final Entitlement entitlement;
  final List<PlanOffer> plans;
  final List<PaymentRecord> payments;
  final String currency;

  factory SubscriptionState.fromJson(Map<String, dynamic> json) => SubscriptionState(
        entitlement: Entitlement.fromJson(
          (json['entitlement'] as Map?)?.cast<String, dynamic>() ?? const {},
        ),
        currency: json['currency'] as String? ?? 'KES',
        plans: ((json['plans'] as List?) ?? const [])
            .whereType<Map>()
            .map((e) => PlanOffer.fromJson(e.cast<String, dynamic>()))
            .toList(),
        payments: ((json['payments'] as List?) ?? const [])
            .whereType<Map>()
            .map((e) => PaymentRecord.fromJson(e.cast<String, dynamic>()))
            .toList(),
      );
}

/// A plan, exactly as the server describes it.
///
/// The app holds no price of its own — not a default, not a fallback. A number
/// on a payment screen that came from anywhere but the server is a number that
/// can be wrong, and being wrong there is worse than showing nothing.
class PlanOffer {
  const PlanOffer({
    required this.type,
    required this.name,
    required this.amount,
    this.emoji = '💳',
    this.blurb = '',
    this.days = 30,
    this.badge,
    this.highlight = false,
  });

  final String type;
  final String name;
  final int amount;
  final String emoji;
  final String blurb;
  final int days;
  final String? badge;
  final bool highlight;

  factory PlanOffer.fromJson(Map<String, dynamic> json) => PlanOffer(
        type: json['type'] as String? ?? '',
        name: json['name'] as String? ?? '',
        amount: (json['amount'] as num?)?.toInt() ?? 0,
        emoji: json['emoji'] as String? ?? '💳',
        blurb: json['blurb'] as String? ?? '',
        days: (json['days'] as num?)?.toInt() ?? 30,
        badge: json['badge'] as String?,
        highlight: json['highlight'] == true,
      );
}

class PaymentRecord {
  const PaymentRecord({
    required this.amount,
    required this.status,
    this.receipt,
    this.phone,
    this.createdAt,
  });

  final int amount;
  final String status;
  final String? receipt;
  final String? phone;
  final DateTime? createdAt;

  bool get isPaid => status == 'completed';

  factory PaymentRecord.fromJson(Map<String, dynamic> json) => PaymentRecord(
        amount: (json['amount'] as num?)?.toInt() ?? 0,
        status: json['status'] as String? ?? 'pending',
        receipt: json['receipt'] as String?,
        phone: json['phone'] as String?,
        createdAt: DateTime.tryParse(json['created_at'] as String? ?? ''),
      );
}

/// Where a payment has got to.
///
/// `pending` is the ordinary case for a minute or so: the prompt is on the
/// phone and nobody has typed a PIN yet.
enum PaymentProgress { pending, paid, failed, missing }

class PaymentStatus {
  const PaymentStatus({required this.progress, this.receipt, this.message});

  final PaymentProgress progress;
  final String? receipt;
  final String? message;

  factory PaymentStatus.fromJson(Map<String, dynamic> json) {
    final status = json['status'] as String? ?? 'pending';

    return PaymentStatus(
      progress: switch (status) {
        'completed' => PaymentProgress.paid,
        'failed' || 'cancelled' => PaymentProgress.failed,
        'not_found' => PaymentProgress.missing,
        _ => PaymentProgress.pending,
      },
      receipt: json['receipt'] as String?,
      message: json['message'] as String?,
    );
  }
}

/// The number M-Pesa will prompt.
///
/// Parents type 07XX XXX XXX; Safaricom wants 2547XXXXXXXX. Both are accepted
/// here and the server normalises whichever arrives. The check exists to catch
/// a typo before a stranger's phone is asked for their M-Pesa PIN, not to be
/// clever about numbering plans.
class MpesaPhone {
  const MpesaPhone._();

  static bool looksValid(String value) {
    final digits = value.replaceAll(RegExp('[^0-9]'), '');

    return RegExp(r'^(?:254[17]\d{8}|0[17]\d{8}|[17]\d{8})$').hasMatch(digits);
  }
}
