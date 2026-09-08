import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/providers.dart';
import '../../core/network/api_exception.dart';
import '../../core/platform/form_factor.dart';
import '../../design/components/focus_ring.dart';
import '../../design/components/kid_button.dart';
import '../../design/components/kid_scaffold.dart';
import '../../design/components/mascot_stage.dart';
import '../../design/tokens.dart';

/// The four-digit gate between the child's world and the grown-up's.
///
/// It works offline against a digest kept from the last successful check, and
/// it locks for a minute after five wrong tries, which is the same rule the
/// server enforces.
class ParentGateScreen extends ConsumerStatefulWidget {
  const ParentGateScreen({super.key});

  @override
  ConsumerState<ParentGateScreen> createState() => _ParentGateScreenState();
}

class _ParentGateScreenState extends ConsumerState<ParentGateScreen> {
  static const int _maxAttempts = 5;

  String _pin = '';
  int _wrongAttempts = 0;
  bool _busy = false;
  String? _message;
  DateTime? _lockedUntil;

  bool get _locked => _lockedUntil != null && _lockedUntil!.isAfter(DateTime.now());

  Future<void> _submit() async {
    if (_pin.length != 4 || _locked) return;

    setState(() {
      _busy = true;
      _message = null;
    });

    final auth = ref.read(authRepositoryProvider);
    bool? ok;

    try {
      ok = await auth.verifyPin(_pin);
    } on ApiException catch (error) {
      if (error.isOffline) {
        ok = await auth.verifyPinOffline(_pin);

        if (ok == null) {
          setState(() {
            _busy = false;
            _message = 'The parent zone needs the internet the first time on this device.';
            _pin = '';
          });
          return;
        }
      } else if (error.code == 'pin_locked') {
        setState(() {
          _busy = false;
          _lockedUntil = DateTime.now().add(const Duration(minutes: 1));
          _message = error.message;
          _pin = '';
        });
        return;
      } else {
        ok = false;
      }
    } catch (error) {
      // Anything else (a device store that will not open, a malformed reply)
      // must still leave the gate usable rather than stuck on a spinner.
      if (!mounted) return;

      setState(() {
        _busy = false;
        _pin = '';
        _message = 'Something went wrong checking that PIN. Please try again.';
      });

      return;
    }

    if (!mounted) return;

    if (ok == true) {
      setState(() => _busy = false);
      context.go('/parent/home');
      return;
    }

    _wrongAttempts++;

    setState(() {
      _busy = false;
      _pin = '';
      if (_wrongAttempts >= _maxAttempts) {
        _lockedUntil = DateTime.now().add(const Duration(minutes: 1));
        _message = 'Too many tries. Wait a minute and try again.';
        _wrongAttempts = 0;
      } else {
        _message = 'That PIN is not right. ${_maxAttempts - _wrongAttempts} tries left.';
      }
    });
  }

  void _press(String digit) {
    if (_locked || _pin.length >= 4) return;

    setState(() {
      _pin += digit;
      _message = null;
    });

    if (_pin.length == 4) _submit();
  }

  void _backspace() {
    if (_pin.isEmpty) return;
    setState(() => _pin = _pin.substring(0, _pin.length - 1));
  }

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);
    final guardian = ref.watch(sessionProvider).guardian;

    return KidScaffold(
      maxContentWidth: 420,
      onBack: () => context.go('/profiles'),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const MascotStage(mood: MascotMood.thinking),
          SizedBox(height: KidSpacing.md * formFactor.density),
          Text('Grown-ups only', style: theme.textTheme.displayMedium),
          SizedBox(height: KidSpacing.xs * formFactor.density),
          Text(
            'Enter your 4-digit PIN',
            style: theme.textTheme.bodyLarge?.copyWith(color: theme.colorScheme.onSurfaceVariant),
          ),
          if (guardian != null && !guardian.hasCustomPin) ...[
            SizedBox(height: KidSpacing.sm * formFactor.density),
            Container(
              padding: EdgeInsets.all(KidSpacing.sm * formFactor.density),
              decoration: const BoxDecoration(color: KidColors.amberSoft, borderRadius: KidRadius.button),
              child: Text(
                'New account? Your starter PIN is 1234. Change it in Controls.',
                textAlign: TextAlign.center,
                style: theme.textTheme.labelSmall,
              ),
            ),
          ],
          SizedBox(height: KidSpacing.lg * formFactor.density),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: List.generate(4, (index) {
              final filled = index < _pin.length;

              return Container(
                margin: EdgeInsets.symmetric(horizontal: KidSpacing.sm * formFactor.density),
                width: 20 * formFactor.density,
                height: 20 * formFactor.density,
                decoration: BoxDecoration(
                  color: filled ? KidColors.primary : Colors.transparent,
                  shape: BoxShape.circle,
                  border: Border.all(color: KidColors.primary, width: 2),
                ),
              );
            }),
          ),
          if (_message != null) ...[
            SizedBox(height: KidSpacing.md * formFactor.density),
            Text(
              _message!,
              textAlign: TextAlign.center,
              style: theme.textTheme.bodyLarge?.copyWith(color: KidColors.danger),
            ),
          ],
          SizedBox(height: KidSpacing.lg * formFactor.density),
          if (_busy)
            const CircularProgressIndicator()
          else
            _Keypad(onDigit: _press, onBackspace: _backspace, enabled: !_locked),
          SizedBox(height: KidSpacing.lg * formFactor.density),
          TextButton(
            onPressed: () => context.go('/profiles'),
            child: const Text('Back to the explorers'),
          ),
        ],
      ),
    );
  }
}

class _Keypad extends StatelessWidget {
  const _Keypad({required this.onDigit, required this.onBackspace, this.enabled = true});

  final ValueChanged<String> onDigit;
  final VoidCallback onBackspace;
  final bool enabled;

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);
    final size = KidTouch.large * formFactor.density;

    Widget key(String label, {VoidCallback? onPressed, bool autofocus = false}) {
      return KidFocusable(
        onPressed: enabled ? (onPressed ?? () => onDigit(label)) : null,
        enabled: enabled,
        autofocus: autofocus,
        borderRadius: KidRadius.pill,
        semanticLabel: label,
        child: Container(
          width: size,
          height: size,
          alignment: Alignment.center,
          decoration: BoxDecoration(
            color: theme.colorScheme.surface,
            shape: BoxShape.circle,
            border: Border.all(color: theme.colorScheme.outline, width: 2),
            boxShadow: KidShadows.edge(theme.colorScheme.outline),
          ),
          child: Text(label, style: theme.textTheme.displayMedium),
        ),
      );
    }

    return Wrap(
      alignment: WrapAlignment.center,
      spacing: KidSpacing.md * formFactor.density,
      runSpacing: KidSpacing.md * formFactor.density,
      children: [
        for (final digit in ['1', '2', '3', '4', '5', '6', '7', '8', '9'])
          key(digit, autofocus: digit == '1'),
        SizedBox(width: size, height: size),
        key('0'),
        KidFocusable(
          onPressed: enabled ? onBackspace : null,
          enabled: enabled,
          borderRadius: KidRadius.pill,
          semanticLabel: 'Delete',
          child: SizedBox(
            width: size,
            height: size,
            child: const Icon(Icons.backspace_outlined),
          ),
        ),
      ],
    );
  }
}

/// The parent zone's front door: sync state, and the way through to the report
/// and the settings. Everything a child would find boring lives behind here.
class ParentHomeScreen extends ConsumerWidget {
  const ParentHomeScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final session = ref.watch(sessionProvider);
    final sync = ref.watch(syncEngineProvider);
    final formFactor = context.formFactor;
    final theme = Theme.of(context);

    return KidScaffold(
      onBack: () => context.go('/profiles'),
      appBar: AppBar(
        title: const Text('Parent zone'),
        leading: BackButton(onPressed: () => context.go('/profiles')),
      ),
      child: ListView(
        children: [
          SizedBox(height: KidSpacing.md * formFactor.density),
          Text('Signed in as ${session.guardian?.email ?? 'a parent'}', style: theme.textTheme.titleMedium),
          SizedBox(height: KidSpacing.md * formFactor.density),
          Card(
            child: Padding(
              padding: EdgeInsets.all(KidSpacing.md * formFactor.density),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Sync', style: theme.textTheme.titleLarge),
                  SizedBox(height: KidSpacing.xs * formFactor.density),
                  Text(
                    sync.state.pending == 0
                        ? 'Everything is uploaded.'
                        : '${sync.state.pending} updates waiting to upload.',
                    style: theme.textTheme.bodyLarge,
                  ),
                  if (sync.state.lastSuccessAt != null)
                    Text(
                      'Last synced ${sync.state.lastSuccessAt}',
                      style: theme.textTheme.labelSmall?.copyWith(color: theme.colorScheme.onSurfaceVariant),
                    ),
                  SizedBox(height: KidSpacing.sm * formFactor.density),
                  KidButton(
                    label: 'Sync now',
                    size: KidButtonSize.small,
                    onPressed: () => sync.syncNow(childId: session.activeChild?.id, reason: 'parent'),
                  ),
                ],
              ),
            ),
          ),
          SizedBox(height: KidSpacing.md * formFactor.density),
          KidButton(
            label: 'How they are doing',
            icon: Icons.insights_rounded,
            expand: true,
            autofocus: true,
            onPressed: () => context.go('/parent/report'),
          ),
          SizedBox(height: KidSpacing.sm * formFactor.density),
          KidButton(
            label: 'Settings',
            icon: Icons.tune_rounded,
            tone: KidButtonTone.neutral,
            expand: true,
            onPressed: () => context.go('/parent/settings'),
          ),
          SizedBox(height: KidSpacing.lg * formFactor.density),
          KidButton(
            label: 'Downloads',
            icon: Icons.download_rounded,
            tone: KidButtonTone.neutral,
            onPressed: () => context.go('/downloads'),
          ),
          SizedBox(height: KidSpacing.md * formFactor.density),
          KidButton(
            label: 'Sign out',
            icon: Icons.logout_rounded,
            tone: KidButtonTone.danger,
            onPressed: () async {
              await ref.read(sessionProvider.notifier).signOut();
              if (context.mounted) context.go('/sign-in');
            },
          ),
          SizedBox(height: KidSpacing.xl * formFactor.density),
        ],
      ),
    );
  }
}
