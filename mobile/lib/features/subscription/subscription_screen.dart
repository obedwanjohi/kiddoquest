import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/providers.dart';
import '../../core/models/snapshot.dart';
import '../../core/models/subscription.dart';
import '../../core/network/api_exception.dart';
import '../../core/platform/form_factor.dart';
import '../../design/components/kid_button.dart';
import '../../design/components/kid_scaffold.dart';
import '../../design/tokens.dart';
import '../map/map_state.dart';

/// Paying, from inside the app.
///
/// Three steps and no more: pick a plan, confirm the phone number, then wait
/// while M-Pesa asks for a PIN on that phone. The app never sees the PIN and
/// never decides that a payment succeeded — it polls the server, which is told
/// by Safaricom.
///
/// Every price on this screen comes from the server. The app holds no number of
/// its own, not even a fallback, because a wrong price here is the one bug that
/// cannot be shipped.
class SubscriptionScreen extends ConsumerStatefulWidget {
  const SubscriptionScreen({super.key});

  @override
  ConsumerState<SubscriptionScreen> createState() => _SubscriptionScreenState();
}

class _SubscriptionScreenState extends ConsumerState<SubscriptionScreen> {
  final _phone = TextEditingController();

  PlanOffer? _plan;
  String? _checkoutId;
  Timer? _poll;
  int _waited = 0;
  bool _busy = false;
  String? _error;
  PaymentStatus? _result;

  /// Safaricom gives a customer about a minute to type their PIN. Waiting much
  /// longer than that leaves a parent staring at a spinner for no reason.
  static const int _giveUpAfterSeconds = 90;

  @override
  void initState() {
    super.initState();
    _phone.text = ref.read(sessionProvider).guardian?.phone ?? '';
  }

  @override
  void dispose() {
    _poll?.cancel();
    _phone.dispose();
    super.dispose();
  }

  Future<void> _pay() async {
    final plan = _plan;
    final phone = _phone.text.trim();

    if (plan == null) return;

    if (!MpesaPhone.looksValid(phone)) {
      setState(() => _error = 'Enter the M-Pesa number as 07… or 2547…');
      return;
    }

    setState(() {
      _busy = true;
      _error = null;
      _result = null;
      _waited = 0;
    });

    try {
      final id = await ref.read(subscriptionRepositoryProvider).requestPayment(
            phone: phone,
            planType: plan.type,
          );

      if (!mounted) return;

      setState(() {
        _busy = false;
        _checkoutId = id;
      });

      unawaited(HapticFeedback.mediumImpact());
      _poll = Timer.periodic(const Duration(seconds: 3), (_) => _check());
    } on ApiException catch (error) {
      if (mounted) {
        setState(() {
          _busy = false;
          _error = error.isOffline
              ? 'Paying needs the internet. Everything already downloaded still works.'
              : error.message;
        });
      }
    }
  }

  Future<void> _check() async {
    final id = _checkoutId;

    if (id == null) return;

    _waited += 3;

    try {
      final status = await ref.read(subscriptionRepositoryProvider).check(id);

      if (!mounted) return;

      if (status.progress == PaymentProgress.pending) {
        if (_waited >= _giveUpAfterSeconds) {
          _stopPolling();
          setState(() {
            _result = const PaymentStatus(
              progress: PaymentProgress.pending,
              message: 'We have not heard back from M-Pesa yet. If you paid, it '
                  'will appear here shortly — you do not need to pay again.',
            );
          });
        }

        return;
      }

      _stopPolling();
      setState(() => _result = status);

      if (status.progress == PaymentProgress.paid) {
        // The entitlement lives on the snapshot, so refresh what depends on it.
        ref.invalidate(subscriptionProvider);
        ref.invalidate(mapDataProvider);
      }
    } on ApiException {
      // A dropped connection mid-poll is not a failed payment. Keep asking
      // until the clock runs out.
    }
  }

  void _stopPolling() {
    _poll?.cancel();
    _poll = null;
    _checkoutId = null;
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(subscriptionProvider);
    final density = context.formFactor.density;

    return KidScaffold(
      onBack: () => context.go('/parent/home'),
      appBar: AppBar(
        title: const Text('Subscription'),
        leading: BackButton(onPressed: () => context.go('/parent/home')),
      ),
      child: state.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, _) => _Problem(
          message: '$error',
          onRetry: () => ref.invalidate(subscriptionProvider),
        ),
        data: (data) {
          if (_result != null) {
            return _Outcome(
              status: _result!,
              onDone: () => context.go('/parent/home'),
              onTryAgain: () => setState(() => _result = null),
            );
          }

          if (_checkoutId != null) {
            return _Waiting(phone: _phone.text.trim(), seconds: _waited);
          }

          return ListView(
            children: [
              SizedBox(height: KidSpacing.md * density),
              _CurrentPlan(entitlement: data.entitlement, currency: data.currency),
              SizedBox(height: KidSpacing.lg * density),
              Text('Choose a plan', style: Theme.of(context).textTheme.titleLarge),
              SizedBox(height: KidSpacing.sm * density),
              for (final plan in data.plans) ...[
                _PlanCard(
                  plan: plan,
                  currency: data.currency,
                  selected: _plan?.type == plan.type,
                  onSelect: () => setState(() => _plan = plan),
                ),
                SizedBox(height: KidSpacing.sm * density),
              ],
              if (data.plans.isEmpty)
                Text(
                  'No plans are on offer right now.',
                  style: Theme.of(context).textTheme.bodyLarge,
                ),
              SizedBox(height: KidSpacing.md * density),
              TextField(
                controller: _phone,
                keyboardType: TextInputType.phone,
                decoration: const InputDecoration(
                  labelText: 'M-Pesa number',
                  hintText: '07XX XXX XXX',
                  border: OutlineInputBorder(),
                ),
              ),
              if (_error != null) ...[
                SizedBox(height: KidSpacing.sm * density),
                Text(_error!, style: const TextStyle(color: KidColors.danger)),
              ],
              SizedBox(height: KidSpacing.md * density),
              KidButton(
                label: _plan == null
                    ? 'Pick a plan first'
                    : 'Pay ${data.currency} ${_plan!.amount} with M-Pesa',
                icon: Icons.phone_android_rounded,
                expand: true,
                busy: _busy,
                onPressed: _plan == null || _busy ? null : _pay,
              ),
              SizedBox(height: KidSpacing.sm * density),
              Text(
                'M-Pesa will ask for your PIN on your own phone. KiddoQuest '
                'never sees it.',
                style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                      color: Theme.of(context).colorScheme.onSurfaceVariant,
                    ),
              ),
              if (data.payments.isNotEmpty) ...[
                SizedBox(height: KidSpacing.lg * density),
                Text('Recent payments', style: Theme.of(context).textTheme.titleMedium),
                for (final payment in data.payments)
                  ListTile(
                    contentPadding: EdgeInsets.zero,
                    dense: true,
                    leading: Icon(
                      payment.isPaid ? Icons.check_circle_rounded : Icons.schedule_rounded,
                      color: payment.isPaid ? KidColors.success : KidColors.muted,
                    ),
                    title: Text('${data.currency} ${payment.amount}'),
                    subtitle: Text(payment.receipt ?? payment.status),
                  ),
              ],
              SizedBox(height: KidSpacing.xl * density),
            ],
          );
        },
      ),
    );
  }

}

class _CurrentPlan extends StatelessWidget {
  const _CurrentPlan({required this.entitlement, required this.currency});

  final Entitlement entitlement;
  final String currency;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final active = entitlement.isActive;

    return Container(
      padding: EdgeInsets.all(KidSpacing.md * context.formFactor.density),
      decoration: BoxDecoration(
        color: active ? KidColors.successSoft : KidColors.primarySoft,
        borderRadius: KidRadius.card,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            active ? 'Your subscription is active' : 'No subscription yet',
            style: theme.textTheme.titleLarge?.copyWith(color: KidColors.stageInk),
          ),
          const SizedBox(height: KidSpacing.xs),
          Text(
            active
                ? 'It runs until ${_date(entitlement.expiresAt)}. Paid worlds keep '
                    'working for ${entitlement.offlineGraceDays} days offline after that.'
                : entitlement.enforced
                    ? 'The first world of every subject is free. A subscription opens the rest.'
                    : 'Every world is playable right now. A subscription supports the work.',
            style: theme.textTheme.bodyLarge?.copyWith(color: KidColors.stageInk),
          ),
        ],
      ),
    );
  }

  static String _date(DateTime? when) {
    if (when == null) return 'later';

    final local = when.toLocal();

    return '${local.day}/${local.month}/${local.year}';
  }
}

class _PlanCard extends StatelessWidget {
  const _PlanCard({
    required this.plan,
    required this.currency,
    required this.selected,
    required this.onSelect,
  });

  final PlanOffer plan;
  final String currency;
  final bool selected;
  final VoidCallback onSelect;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final density = context.formFactor.density;

    return InkWell(
      onTap: onSelect,
      borderRadius: KidRadius.card,
      child: Container(
        padding: EdgeInsets.all(KidSpacing.md * density),
        decoration: BoxDecoration(
          color: theme.colorScheme.surface,
          borderRadius: KidRadius.card,
          border: Border.all(
            color: selected ? KidColors.primary : theme.colorScheme.outlineVariant,
            width: selected ? 3 : 1,
          ),
        ),
        child: Row(
          children: [
            Text(plan.emoji, style: TextStyle(fontSize: 30 * density)),
            SizedBox(width: KidSpacing.sm * density),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Flexible(child: Text(plan.name, style: theme.textTheme.titleMedium)),
                      if (plan.badge != null) ...[
                        SizedBox(width: KidSpacing.xs * density),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                          decoration: const BoxDecoration(
                            color: KidColors.amberSoft,
                            borderRadius: KidRadius.pill,
                          ),
                          child: Text(plan.badge!, style: theme.textTheme.labelSmall),
                        ),
                      ],
                    ],
                  ),
                  Text(
                    plan.blurb,
                    style: theme.textTheme.bodyMedium?.copyWith(
                      color: theme.colorScheme.onSurfaceVariant,
                    ),
                  ),
                ],
              ),
            ),
            Text(
              '$currency ${plan.amount}',
              style: theme.textTheme.titleLarge?.copyWith(color: KidColors.primary),
            ),
          ],
        ),
      ),
    );
  }
}

class _Waiting extends StatelessWidget {
  const _Waiting({required this.phone, required this.seconds});

  final String phone;
  final int seconds;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final density = context.formFactor.density;

    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const CircularProgressIndicator(),
          SizedBox(height: KidSpacing.lg * density),
          Text('Check your phone', style: theme.textTheme.headlineSmall),
          SizedBox(height: KidSpacing.sm * density),
          Text(
            'M-Pesa has sent a prompt to $phone. Enter your M-Pesa PIN there to '
            'finish.',
            textAlign: TextAlign.center,
            style: theme.textTheme.bodyLarge,
          ),
          SizedBox(height: KidSpacing.md * density),
          Text(
            'Waiting… ${seconds}s',
            style: theme.textTheme.labelLarge?.copyWith(
              color: theme.colorScheme.onSurfaceVariant,
            ),
          ),
        ],
      ),
    );
  }
}

class _Outcome extends StatelessWidget {
  const _Outcome({required this.status, required this.onDone, required this.onTryAgain});

  final PaymentStatus status;
  final VoidCallback onDone;
  final VoidCallback onTryAgain;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final density = context.formFactor.density;
    final paid = status.progress == PaymentProgress.paid;

    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            paid ? Icons.check_circle_rounded : Icons.info_rounded,
            size: 72 * density,
            color: paid ? KidColors.success : KidColors.amber,
          ),
          SizedBox(height: KidSpacing.md * density),
          Text(
            paid ? 'Thank you!' : 'Not finished yet',
            style: theme.textTheme.headlineSmall,
          ),
          SizedBox(height: KidSpacing.sm * density),
          Text(
            status.message ??
                (paid
                    ? 'Every world is open. Receipt ${status.receipt ?? ''}'.trim()
                    : 'The payment did not go through. Nothing has been charged.'),
            textAlign: TextAlign.center,
            style: theme.textTheme.bodyLarge,
          ),
          SizedBox(height: KidSpacing.lg * density),
          KidButton(
            label: paid ? 'Done' : 'Try again',
            autofocus: true,
            onPressed: paid ? onDone : onTryAgain,
          ),
        ],
      ),
    );
  }
}

class _Problem extends StatelessWidget {
  const _Problem({required this.message, required this.onRetry});

  final String message;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Text(message, textAlign: TextAlign.center),
          SizedBox(height: KidSpacing.md * context.formFactor.density),
          KidButton(label: 'Try again', autofocus: true, onPressed: onRetry),
        ],
      ),
    );
  }
}
