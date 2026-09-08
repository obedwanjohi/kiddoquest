import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/providers.dart';
import '../../core/network/api_exception.dart';
import '../../core/platform/form_factor.dart';
import '../../design/components/kid_button.dart';
import '../../design/components/kid_scaffold.dart';
import '../../design/tokens.dart';

/// The phone half of signing a television in.
///
/// It sits behind the parent PIN on purpose: approving a device hands out a
/// thirty-day token for the whole family account, which is not something a
/// child should be able to do by wandering through the menus.
class ApproveTvScreen extends ConsumerStatefulWidget {
  const ApproveTvScreen({super.key});

  @override
  ConsumerState<ApproveTvScreen> createState() => _ApproveTvScreenState();
}

class _ApproveTvScreenState extends ConsumerState<ApproveTvScreen> {
  final _controller = TextEditingController();

  bool _busy = false;
  bool _done = false;
  String? _error;

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  Future<void> _approve() async {
    final code = _controller.text.trim().toUpperCase();

    if (code.length < 4) {
      setState(() => _error = 'Type the code showing on the television.');
      return;
    }

    setState(() {
      _busy = true;
      _error = null;
    });

    try {
      await ref.read(authRepositoryProvider).approveDeviceCode(code);

      if (mounted) {
        setState(() {
          _busy = false;
          _done = true;
        });
      }
    } on ApiException catch (error) {
      if (mounted) {
        setState(() {
          _busy = false;
          _error = error.isOffline
              ? 'Your phone needs the internet to approve a television.'
              : error.message;
        });
      }
    } catch (_) {
      if (mounted) {
        setState(() {
          _busy = false;
          _error = 'Something went wrong. Please try that code again.';
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final density = context.formFactor.density;

    return KidScaffold(
      onBack: () => context.go('/parent/home'),
      appBar: AppBar(
        title: const Text('Sign in a TV'),
        leading: BackButton(onPressed: () => context.go('/parent/home')),
      ),
      child: ListView(
        children: [
          SizedBox(height: KidSpacing.lg * density),
          if (_done) ...[
            Icon(Icons.check_circle_rounded, size: 72 * density, color: KidColors.success),
            SizedBox(height: KidSpacing.md * density),
            Text(
              'That television is signed in.',
              textAlign: TextAlign.center,
              style: theme.textTheme.headlineSmall,
            ),
            SizedBox(height: KidSpacing.sm * density),
            Text(
              'It will pick up your children in a moment. You can put the phone down.',
              textAlign: TextAlign.center,
              style: theme.textTheme.bodyLarge?.copyWith(color: theme.colorScheme.onSurfaceVariant),
            ),
            SizedBox(height: KidSpacing.lg * density),
            KidButton(
              label: 'Done',
              onPressed: () => context.go('/parent/home'),
            ),
          ] else ...[
            Text(
              'Open KiddoQuest on the television. It will show a six-character '
              'code — type it here.',
              style: theme.textTheme.bodyLarge,
            ),
            SizedBox(height: KidSpacing.lg * density),
            TextField(
              controller: _controller,
              autofocus: true,
              textCapitalization: TextCapitalization.characters,
              textAlign: TextAlign.center,
              maxLength: 8,
              enabled: !_busy,
              style: TextStyle(
                fontFamily: KidFonts.display,
                fontSize: 40 * density,
                fontWeight: FontWeight.w800,
                letterSpacing: 8,
              ),
              inputFormatters: [
                FilteringTextInputFormatter.allow(RegExp('[A-Za-z0-9]')),
                TextInputFormatter.withFunction(
                  (_, next) => next.copyWith(text: next.text.toUpperCase()),
                ),
              ],
              decoration: const InputDecoration(
                counterText: '',
                hintText: 'ABC123',
                border: OutlineInputBorder(),
              ),
              onSubmitted: (_) => _approve(),
            ),
            if (_error != null) ...[
              SizedBox(height: KidSpacing.sm * density),
              Text(_error!, style: theme.textTheme.bodyLarge?.copyWith(color: KidColors.danger)),
            ],
            SizedBox(height: KidSpacing.lg * density),
            KidButton(
              label: 'Approve this TV',
              icon: Icons.tv_rounded,
              busy: _busy,
              expand: true,
              onPressed: _busy ? null : _approve,
            ),
            SizedBox(height: KidSpacing.md * density),
            Text(
              'A code only works once, and only for ten minutes.',
              style: theme.textTheme.bodyMedium?.copyWith(color: theme.colorScheme.onSurfaceVariant),
            ),
          ],
          SizedBox(height: KidSpacing.xl * density),
        ],
      ),
    );
  }
}
