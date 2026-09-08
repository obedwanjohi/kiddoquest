import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/providers.dart';
import '../../core/auth/auth_repository.dart';
import '../../core/network/api_exception.dart';
import '../../core/platform/form_factor.dart';
import '../../design/components/kid_button.dart';
import '../../design/components/kid_scaffold.dart';
import '../../design/components/mascot_stage.dart';
import '../../design/tokens.dart';

/// Signing in on a television.
///
/// Nobody should type an email address with a directional pad. The TV shows six
/// characters and waits; a phone that is already signed in vouches for it, and
/// the television claims its own token exactly once.
///
/// The screen holds no credential of its own at any point, which is the reason
/// this flow exists rather than a keyboard.
class TvSignInScreen extends ConsumerStatefulWidget {
  const TvSignInScreen({super.key});

  @override
  ConsumerState<TvSignInScreen> createState() => _TvSignInScreenState();
}

class _TvSignInScreenState extends ConsumerState<TvSignInScreen> {
  DeviceCode? _code;
  Timer? _poll;
  Timer? _tick;
  String? _error;
  bool _requesting = false;

  @override
  void initState() {
    super.initState();
    Future.microtask(_request);
  }

  @override
  void dispose() {
    _poll?.cancel();
    _tick?.cancel();
    super.dispose();
  }

  Future<void> _request() async {
    _poll?.cancel();
    _tick?.cancel();

    setState(() {
      _requesting = true;
      _error = null;
    });

    try {
      final code = await ref.read(authRepositoryProvider).requestDeviceCode();

      if (!mounted) return;

      setState(() {
        _code = code;
        _requesting = false;
      });

      _poll = Timer.periodic(Duration(seconds: code.pollSeconds), (_) => _check());
      // Only so the countdown moves; the claim itself is on the poll timer.
      _tick = Timer.periodic(const Duration(seconds: 1), (_) {
        if (mounted) setState(() {});
      });
    } on ApiException catch (error) {
      if (!mounted) return;

      setState(() {
        _requesting = false;
        _error = error.isOffline
            ? 'This television is not connected to the internet yet.'
            : error.message;
      });
    } catch (_) {
      // Anything else — a malformed reply, a store that will not open — must
      // still leave a way forward. A television has no back button a parent can
      // reach for, so a permanent spinner here is a dead device.
      if (!mounted) return;

      setState(() {
        _requesting = false;
        _error = 'Something went wrong asking for a code. Please try again.';
      });
    }
  }

  Future<void> _check() async {
    final code = _code;

    if (code == null) return;

    if (code.isExpired) {
      // Ask for a fresh one rather than leaving a dead code on the screen for a
      // parent who is still walking across the room with their phone.
      await _request();
      return;
    }

    try {
      final result = await ref.read(authRepositoryProvider).claimDeviceCode(code.code);

      if (result == null || !mounted) return;

      _poll?.cancel();
      _tick?.cancel();

      await ref.read(sessionProvider.notifier).bootstrap();

      if (mounted) {
        context.go(result.children.isEmpty ? '/add-child' : '/profiles');
      }
    } on DeviceCodeExpired {
      await _request();
    } catch (_) {
      // A television in a house with a flaky router should keep asking rather
      // than give up on the first failed poll. The timer is still running.
    }
  }

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);
    final code = _code;

    return KidScaffold(
      child: Center(
        child: SingleChildScrollView(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const MascotStage(mood: MascotMood.talking),
              SizedBox(height: KidSpacing.lg * formFactor.density),
              Text(
                'On your phone, open KiddoQuest and tap “Sign in a TV”.',
                textAlign: TextAlign.center,
                style: theme.textTheme.headlineSmall,
              ),
              SizedBox(height: KidSpacing.md * formFactor.density),
              Text(
                'Then type this code:',
                textAlign: TextAlign.center,
                style: theme.textTheme.titleMedium?.copyWith(color: theme.colorScheme.onSurfaceVariant),
              ),
              SizedBox(height: KidSpacing.lg * formFactor.density),
              if (_error != null)
                _Problem(message: _error!, onRetry: _request)
              else if (code == null || _requesting)
                const Padding(
                  padding: EdgeInsets.all(KidSpacing.xl),
                  child: CircularProgressIndicator(),
                )
              else ...[
                _CodeDisplay(code: code.code),
                SizedBox(height: KidSpacing.md * formFactor.density),
                Text(
                  _remainingLabel(code),
                  style: theme.textTheme.titleMedium?.copyWith(color: theme.colorScheme.onSurfaceVariant),
                ),
                if (code.approveUrl != null) ...[
                  SizedBox(height: KidSpacing.sm * formFactor.density),
                  Text(
                    'No phone app? Go to ${code.approveUrl}',
                    style: theme.textTheme.bodyLarge?.copyWith(color: theme.colorScheme.onSurfaceVariant),
                  ),
                ],
                SizedBox(height: KidSpacing.lg * formFactor.density),
                KidButton(
                  label: 'Show a new code',
                  icon: Icons.refresh_rounded,
                  tone: KidButtonTone.neutral,
                  autofocus: true,
                  onPressed: _request,
                ),
              ],
              if (!formFactor.isTv) ...[
                SizedBox(height: KidSpacing.lg * formFactor.density),
                TextButton(
                  onPressed: () => context.go('/sign-in'),
                  child: const Text('Sign in with an email instead'),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  static String _remainingLabel(DeviceCode code) {
    final seconds = code.remaining.inSeconds;

    if (seconds <= 0) return 'Getting a new code…';

    final minutes = seconds ~/ 60;

    return minutes > 0
        ? 'This code works for another $minutes minute${minutes == 1 ? '' : 's'}.'
        : 'This code works for another $seconds seconds.';
  }
}

/// The code itself, one character per tile.
///
/// Split into tiles because this is read aloud across a room and then typed on
/// a different device; a run of six characters is easy to lose your place in.
class _CodeDisplay extends StatelessWidget {
  const _CodeDisplay({required this.code});

  final String code;

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final size = (formFactor.isTv ? 96.0 : 56.0);

    return Wrap(
      spacing: KidSpacing.sm * formFactor.density,
      runSpacing: KidSpacing.sm,
      alignment: WrapAlignment.center,
      children: [
        for (final character in code.split(''))
          Container(
            width: size,
            height: size * 1.25,
            alignment: Alignment.center,
            decoration: BoxDecoration(
              color: KidColors.primarySoft,
              borderRadius: KidRadius.card,
              border: Border.all(color: KidColors.primary, width: 3),
            ),
            child: Text(
              character,
              style: TextStyle(
                fontFamily: KidFonts.display,
                fontSize: size * 0.6,
                fontWeight: FontWeight.w800,
                color: KidColors.primaryDark,
              ),
            ),
          ),
      ],
    );
  }
}

class _Problem extends StatelessWidget {
  const _Problem({required this.message, required this.onRetry});

  final String message;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Text(message, textAlign: TextAlign.center, style: Theme.of(context).textTheme.titleMedium),
        SizedBox(height: KidSpacing.md * context.formFactor.density),
        KidButton(label: 'Try again', icon: Icons.refresh_rounded, autofocus: true, onPressed: onRetry),
      ],
    );
  }
}
