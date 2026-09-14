import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/providers.dart';
import '../../core/network/api_exception.dart';
import '../../core/platform/form_factor.dart';
import '../../design/components/focus_ring.dart';
import '../../design/site/site_scaffold.dart';
import '../../design/site/tw.dart';
import '../../design/site/tw_motion.dart';

/// "Parent Zone — Enter your 4-Digit Parent PIN", as the website draws it.
///
/// Mirrors `resources/views/parent/pin-gate.blade.php`: the navy card, four
/// dots, a 3×4 keypad with Clear and ✓, and the starter-PIN hint for a new
/// account. Four digits submit on their own, as the website's keypad does.
///
/// Behind it the app keeps what the website cannot do: a check that works
/// offline against a digest kept from the last successful online check, and
/// the same five-tries lockout the server enforces.
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

  void _press(String digit) {
    if (_busy || _locked || _pin.length >= 4) return;

    HapticFeedback.selectionClick();
    setState(() {
      _pin += digit;
      _message = null;
    });

    if (_pin.length == 4) {
      Timer(const Duration(milliseconds: 150), _submit);
    }
  }

  void _clear() => setState(() => _pin = '');

  Future<void> _submit() async {
    if (_pin.length != 4 || _locked || _busy) return;

    setState(() => _busy = true);

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
            _pin = '';
            _message = 'The parent zone needs the internet the first time on this device.';
          });
          return;
        }
      } else if (error.code == 'pin_locked') {
        setState(() {
          _busy = false;
          _pin = '';
          _lockedUntil = DateTime.now().add(const Duration(minutes: 1));
          _message = error.message;
        });
        return;
      } else {
        ok = false;
      }
    } catch (_) {
      // A device store that will not open or a malformed reply must still leave
      // the gate usable rather than stuck.
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
      context.go('/parent/dashboard');
      return;
    }

    _wrongAttempts++;
    final left = _maxAttempts - _wrongAttempts;

    setState(() {
      _busy = false;
      _pin = '';

      if (left <= 0) {
        _lockedUntil = DateTime.now().add(const Duration(minutes: 1));
        _wrongAttempts = 0;
        _message = 'Incorrect PIN. Please wait a minute before trying again.';
      } else {
        _message = 'Incorrect PIN. $left ${left == 1 ? 'try' : 'tries'} left.';
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final sm = Tw.isSm(context);
    final guardian = ref.watch(sessionProvider).guardian;
    final showStarterHint = guardian != null && !guardian.hasCustomPin;

    return SiteScaffold(
      onBack: () => context.go('/profiles'),
      // .pin-bg: radial-gradient(circle at center, #1E1B4B 0%, #0F172A 100%)
      decoration: const BoxDecoration(
        gradient: RadialGradient(radius: 1.2, colors: [Tw.indigo950, Tw.slate900]),
      ),
      child: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Container(
            constraints: const BoxConstraints(maxWidth: 384),
            padding: EdgeInsets.all(sm ? 32 : 24),
            decoration: BoxDecoration(
              color: const Color(0xF21E293B),
              borderRadius: BorderRadius.circular(28),
              border: Border.all(color: const Color(0x66818CF8), width: 2),
              boxShadow: const [BoxShadow(color: Color(0x99000000), blurRadius: 50, offset: Offset(0, 20))],
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const TwBounce(child: Text('🔐', style: TextStyle(fontSize: 48))),
                const SizedBox(height: 8),
                Text('Parent Zone', style: Tw.text(Tw.x2l, color: Tw.white, weight: FontWeight.w900)),
                const SizedBox(height: 4),
                Text(
                  'Enter your 4-Digit Parent PIN',
                  style: Tw.text(Tw.xs, color: Tw.indigo200, weight: FontWeight.w600),
                ),
                const SizedBox(height: 20),

                if (_message != null) ...[
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: Tw.rose500.withValues(alpha: 0.2),
                      borderRadius: BorderRadius.circular(Tw.roundedXl),
                      border: Border.all(color: Tw.rose500),
                    ),
                    child: Text(
                      _message!,
                      textAlign: TextAlign.center,
                      style: Tw.text(Tw.xs, color: Tw.rose300),
                    ),
                  ),
                  const SizedBox(height: 16),
                ],

                // PIN dots.
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    for (var i = 1; i <= 4; i++)
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 8),
                        child: AnimatedScale(
                          duration: const Duration(milliseconds: 150),
                          scale: _pin.length >= i ? 1.2 : 1,
                          child: AnimatedContainer(
                            duration: const Duration(milliseconds: 150),
                            width: 16,
                            height: 16,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              color: _pin.length >= i ? Tw.indigo500 : Tw.white.withValues(alpha: 0.15),
                              border: Border.all(color: Tw.indigo400, width: 2),
                              boxShadow: _pin.length >= i
                                  ? const [BoxShadow(color: Tw.indigo400, blurRadius: 14)]
                                  : null,
                            ),
                          ),
                        ),
                      ),
                  ],
                ),
                const SizedBox(height: 24),

                // Keypad.
                ConstrainedBox(
                  constraints: const BoxConstraints(maxWidth: 260),
                  child: Column(
                    children: [
                      for (final row in const [
                        ['1', '2', '3'],
                        ['4', '5', '6'],
                        ['7', '8', '9'],
                        ['clear', '0', 'submit'],
                      ])
                        Padding(
                          padding: const EdgeInsets.only(bottom: 12),
                          child: Row(
                            children: [
                              for (var i = 0; i < row.length; i++) ...[
                                if (i > 0) const SizedBox(width: 12),
                                Expanded(
                                  child: _Key(
                                    label: switch (row[i]) {
                                      'clear' => 'Clear',
                                      'submit' => '✓',
                                      _ => row[i],
                                    },
                                    color: switch (row[i]) {
                                      'clear' => Tw.rose400,
                                      'submit' => Tw.emerald400,
                                      _ => Tw.white,
                                    },
                                    small: row[i] == 'clear' || row[i] == 'submit',
                                    autofocus: row[i] == '1' && context.formFactor.isTv,
                                    onPressed: switch (row[i]) {
                                      'clear' => _clear,
                                      'submit' => _submit,
                                      _ => () => _press(row[i]),
                                    },
                                  ),
                                ),
                              ],
                            ],
                          ),
                        ),
                    ],
                  ),
                ),
                const SizedBox(height: 12),

                KidFocusable(
                  onPressed: () => context.go('/profiles'),
                  semanticLabel: 'Back to Kids App',
                  borderRadius: BorderRadius.circular(8),
                  child: Padding(
                    padding: const EdgeInsets.all(4),
                    child: Text('← Back to Kids App', style: Tw.text(Tw.xs, color: Tw.indigo300)),
                  ),
                ),
                if (showStarterHint) ...[
                  const SizedBox(height: 8),
                  Text.rich(
                    TextSpan(
                      text: 'New account? Your starter PIN is ',
                      style: Tw.text(11, color: Tw.indigo400.withValues(alpha: 0.6), weight: FontWeight.w600),
                      children: [
                        TextSpan(
                          text: '1234',
                          style: Tw.text(11, color: Tw.indigo200, weight: FontWeight.w900),
                        ),
                        const TextSpan(text: ' — change it in Controls.'),
                      ],
                    ),
                    textAlign: TextAlign.center,
                  ),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }
}

/// `.pin-key-btn`.
class _Key extends StatefulWidget {
  const _Key({
    required this.label,
    required this.onPressed,
    this.color = Tw.white,
    this.small = false,
    this.autofocus = false,
  });

  final String label;
  final VoidCallback onPressed;
  final Color color;
  final bool small;
  final bool autofocus;

  @override
  State<_Key> createState() => _KeyState();
}

class _KeyState extends State<_Key> {
  bool _down = false;

  @override
  Widget build(BuildContext context) {
    return Listener(
      onPointerDown: (_) => setState(() => _down = true),
      onPointerUp: (_) => setState(() => _down = false),
      onPointerCancel: (_) => setState(() => _down = false),
      child: KidFocusable(
        onPressed: widget.onPressed,
        autofocus: widget.autofocus,
        semanticLabel: widget.label,
        scaleOnFocus: 1.03,
        borderRadius: BorderRadius.circular(20),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 50),
          height: 60,
          alignment: Alignment.center,
          transform: Matrix4.translationValues(0, _down ? 2 : 0, 0),
          decoration: BoxDecoration(
            color: _down ? Tw.indigo500.withValues(alpha: 0.4) : Tw.white.withValues(alpha: 0.1),
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: Tw.white.withValues(alpha: 0.2), width: 1.5),
            boxShadow: [BoxShadow(color: const Color(0x66000000), offset: Offset(0, _down ? 1 : 4))],
          ),
          child: Text(
            widget.label,
            style: Tw.text(widget.small ? Tw.base : Tw.x2l, color: widget.color, weight: FontWeight.w900),
          ),
        ),
      ),
    );
  }
}
