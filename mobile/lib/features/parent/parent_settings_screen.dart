import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/providers.dart';
import '../../core/models/child.dart';
import '../../core/network/api_exception.dart';
import '../../core/platform/form_factor.dart';
import '../../design/components/kid_button.dart';
import '../../design/components/kid_scaffold.dart';
import '../../design/tokens.dart';

/// The controls a parent actually turns: how long a child may play, what the
/// app offers them, and the PIN that guards this room.
///
/// Every change here goes straight to the server rather than into the outbox.
/// A parent changing a limit is a decision about the account, not a play event,
/// and it should not be sitting in a queue while a child keeps playing.
class ParentSettingsScreen extends ConsumerStatefulWidget {
  const ParentSettingsScreen({super.key});

  @override
  ConsumerState<ParentSettingsScreen> createState() => _ParentSettingsScreenState();
}

class _ParentSettingsScreenState extends ConsumerState<ParentSettingsScreen> {
  bool _busy = false;

  @override
  Widget build(BuildContext context) {
    final session = ref.watch(sessionProvider);
    final density = context.formFactor.density;

    return KidScaffold(
      onBack: () => context.go('/parent/home'),
      appBar: AppBar(
        title: const Text('Settings'),
        leading: BackButton(onPressed: () => context.go('/parent/home')),
      ),
      child: ListView(
        children: [
          SizedBox(height: KidSpacing.md * density),
          for (final child in session.children) ...[
            _ScreenTimeCard(
              child: child,
              busy: _busy,
              onChanged: (minutes) => _setScreenTime(child, minutes),
            ),
            SizedBox(height: KidSpacing.md * density),
          ],
          _ContentCard(
            devotional: session.guardian?.enableDevotional ?? true,
            songs: session.guardian?.enableSongsHub ?? true,
            busy: _busy,
            onChanged: _setContentToggles,
          ),
          SizedBox(height: KidSpacing.md * density),
          _PinCard(onSubmit: _changePin),
          SizedBox(height: KidSpacing.xl * density),
        ],
      ),
    );
  }

  Future<void> _setScreenTime(Child child, int minutes) async {
    setState(() => _busy = true);

    try {
      final response = await ref.read(apiClientProvider).patch(
            '/children/${child.id}/screen-time',
            body: {'minutes': minutes},
          );

      final updated = Child.fromJson((response['child'] as Map).cast<String, dynamic>());
      await ref.read(progressDaoProvider).saveChild(updated, guardianId: _guardian?.id);
      ref.read(sessionProvider.notifier).updateActiveChild(updated);

      _say(minutes == 0
          ? '${child.name} can now play without a daily limit.'
          : '${child.name} can play $minutes minutes a day.');
    } on ApiException catch (error) {
      _say(error.message);
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  Future<void> _setContentToggles({bool? devotional, bool? songs}) async {
    setState(() => _busy = true);

    try {
      await ref.read(apiClientProvider).patch('/parent/settings', body: {
        // Only the toggle that moved is sent, so two parents on two devices do
        // not overwrite each other's other switch.
        'enable_devotional': ?devotional,
        'enable_songs_hub': ?songs,
      });

      await ref.read(sessionProvider.notifier).bootstrap();
      _say('Saved.');
    } on ApiException catch (error) {
      _say(error.message);
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  Future<void> _changePin(String pin, String password) async {
    setState(() => _busy = true);

    try {
      await ref.read(apiClientProvider).patch('/parent/pin', body: {
        'new_pin': pin,
        'password': password,
      });

      await ref.read(authRepositoryProvider).rememberPin(pin);
      _say('PIN changed.');
    } on ApiException catch (error) {
      _say(error.message);
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  Guardian? get _guardian => ref.read(sessionProvider).guardian;

  void _say(String message) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message)));
  }
}

class _ScreenTimeCard extends StatelessWidget {
  const _ScreenTimeCard({required this.child, required this.busy, required this.onChanged});

  final Child child;
  final bool busy;
  final ValueChanged<int> onChanged;

  static const List<int> _choices = [0, 15, 20, 30, 45, 60, 90];

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final density = context.formFactor.density;
    final current = child.dailyTimeLimitMinutes;

    return Card(
      child: Padding(
        padding: EdgeInsets.all(KidSpacing.md * density),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('${child.name}’s daily limit', style: theme.textTheme.titleLarge),
            SizedBox(height: KidSpacing.xs * density),
            Text(
              'When the time is up the app says goodnight rather than nagging. '
              'The limit follows the child, so it holds on every device.',
              style: theme.textTheme.bodyMedium?.copyWith(color: theme.colorScheme.onSurfaceVariant),
            ),
            SizedBox(height: KidSpacing.sm * density),
            Wrap(
              spacing: KidSpacing.sm,
              runSpacing: KidSpacing.sm,
              children: [
                for (final minutes in _choices)
                  ChoiceChip(
                    selected: current == minutes,
                    label: Text(minutes == 0 ? 'No limit' : '$minutes min'),
                    onSelected: busy ? null : (_) => onChanged(minutes),
                  ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _ContentCard extends StatelessWidget {
  const _ContentCard({
    required this.devotional,
    required this.songs,
    required this.busy,
    required this.onChanged,
  });

  final bool devotional;
  final bool songs;
  final bool busy;
  final Future<void> Function({bool? devotional, bool? songs}) onChanged;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final density = context.formFactor.density;

    return Card(
      child: Padding(
        padding: EdgeInsets.all(KidSpacing.md * density),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('What the app offers', style: theme.textTheme.titleLarge),
            SwitchListTile(
              contentPadding: EdgeInsets.zero,
              value: devotional,
              title: const Text('Daily devotional'),
              subtitle: const Text('A short story and prayer on the map.'),
              onChanged: busy ? null : (value) => onChanged(devotional: value),
            ),
            SwitchListTile(
              contentPadding: EdgeInsets.zero,
              value: songs,
              title: const Text('Songs hub'),
              subtitle: const Text('Sing-along songs between missions.'),
              onChanged: busy ? null : (value) => onChanged(songs: value),
            ),
          ],
        ),
      ),
    );
  }
}

class _PinCard extends StatefulWidget {
  const _PinCard({required this.onSubmit});

  final Future<void> Function(String pin, String password) onSubmit;

  @override
  State<_PinCard> createState() => _PinCardState();
}

class _PinCardState extends State<_PinCard> {
  final _pin = TextEditingController();
  final _password = TextEditingController();
  String? _error;

  @override
  void dispose() {
    _pin.dispose();
    _password.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final density = context.formFactor.density;

    return Card(
      child: Padding(
        padding: EdgeInsets.all(KidSpacing.md * density),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Parent PIN', style: theme.textTheme.titleLarge),
            SizedBox(height: KidSpacing.xs * density),
            Text(
              'Your account password is needed too, so a child who watches you '
              'type the PIN still cannot change it.',
              style: theme.textTheme.bodyMedium?.copyWith(color: theme.colorScheme.onSurfaceVariant),
            ),
            SizedBox(height: KidSpacing.sm * density),
            TextField(
              controller: _pin,
              keyboardType: TextInputType.number,
              maxLength: 4,
              obscureText: true,
              decoration: const InputDecoration(labelText: 'New 4-digit PIN', counterText: ''),
            ),
            TextField(
              controller: _password,
              obscureText: true,
              decoration: const InputDecoration(labelText: 'Account password'),
            ),
            if (_error != null) ...[
              SizedBox(height: KidSpacing.xs * density),
              Text(_error!, style: theme.textTheme.bodyMedium?.copyWith(color: KidColors.danger)),
            ],
            SizedBox(height: KidSpacing.sm * density),
            KidButton(
              label: 'Change PIN',
              size: KidButtonSize.small,
              onPressed: () {
                final pin = _pin.text.trim();

                if (pin.length != 4 || int.tryParse(pin) == null) {
                  setState(() => _error = 'The PIN must be four digits.');
                  return;
                }

                if (_password.text.isEmpty) {
                  setState(() => _error = 'Enter your account password.');
                  return;
                }

                setState(() => _error = null);
                widget.onSubmit(pin, _password.text);
                _pin.clear();
                _password.clear();
              },
            ),
          ],
        ),
      ),
    );
  }
}
