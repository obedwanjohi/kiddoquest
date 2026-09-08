import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/providers.dart';
import '../../core/platform/form_factor.dart';
import '../../design/components/focus_ring.dart';
import '../../design/components/kid_button.dart';
import '../../design/components/kid_scaffold.dart';
import '../../design/components/mascot_stage.dart';
import '../../design/tokens.dart';

/// Adding a child: a name, a character, and a birthday that picks the level.
///
/// The passport preview updates as the parent types, because on the website
/// that little card is what makes a four-year-old lean in.
class AddChildScreen extends ConsumerStatefulWidget {
  const AddChildScreen({super.key});

  @override
  ConsumerState<AddChildScreen> createState() => _AddChildScreenState();
}

class _AddChildScreenState extends ConsumerState<AddChildScreen> {
  final _name = TextEditingController();
  String _avatar = 'lion';
  String _color = 'purple';
  DateTime? _birthdate;
  bool _busy = false;

  static const _colors = {
    'purple': KidColors.primary,
    'green': KidColors.success,
    'amber': KidColors.amber,
    'ocean': Color(0xFF0284C7),
    'candy': Color(0xFFEC4899),
  };

  @override
  void dispose() {
    _name.dispose();
    super.dispose();
  }

  /// Same rule as the server: three and under is Play Group, four is PP1,
  /// five is PP2.
  String get _level {
    final birthdate = _birthdate;
    if (birthdate == null) return 'PP1';

    final now = DateTime.now();
    var age = now.year - birthdate.year;
    if (now.month < birthdate.month || (now.month == birthdate.month && now.day < birthdate.day)) {
      age--;
    }

    return switch (age) {
      <= 3 => 'Play Group',
      4 => 'PP1',
      5 => 'PP2',
      6 => 'Grade 1',
      7 => 'Grade 2',
      _ => 'Grade 3',
    };
  }

  Future<void> _save() async {
    if (_name.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('What is your child called?')),
      );
      return;
    }

    setState(() => _busy = true);

    final child = await ref.read(sessionProvider.notifier).addChild(
          name: _name.text,
          avatar: _avatar,
          birthdate: _birthdate?.toIso8601String().split('T').first,
          favoriteColor: _color,
        );

    if (!mounted) return;
    setState(() => _busy = false);

    if (child != null) {
      ref.read(sessionProvider.notifier).selectChild(child);
      context.go('/map');
    }
  }

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);
    final accent = _colors[_color] ?? KidColors.primary;

    return KidScaffold(
      maxContentWidth: 640,
      child: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            SizedBox(height: KidSpacing.lg * formFactor.density),
            Text('Add an explorer', textAlign: TextAlign.center, style: theme.textTheme.displayMedium),
            SizedBox(height: KidSpacing.lg * formFactor.density),

            // Passport preview
            Container(
              padding: EdgeInsets.all(KidSpacing.lg * formFactor.density),
              decoration: BoxDecoration(
                color: theme.colorScheme.surface,
                borderRadius: KidRadius.card,
                border: Border.all(color: accent, width: 3),
                boxShadow: KidShadows.edge(accent.withValues(alpha: 0.6)),
              ),
              child: Row(
                children: [
                  MascotStage(character: _avatar, size: formFactor.isTv ? 120 : 84),
                  SizedBox(width: KidSpacing.md * formFactor.density),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          _name.text.trim().isEmpty ? 'Your explorer' : _name.text.trim(),
                          style: theme.textTheme.titleLarge,
                        ),
                        Text(_level, style: theme.textTheme.labelLarge?.copyWith(color: accent)),
                        Text(
                          MascotStage.characters.containsKey(_avatar) ? 'Buddy: $_avatar' : '',
                          style: theme.textTheme.labelSmall,
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),

            SizedBox(height: KidSpacing.lg * formFactor.density),
            TextField(
              controller: _name,
              textCapitalization: TextCapitalization.words,
              onChanged: (_) => setState(() {}),
              decoration: const InputDecoration(labelText: "Child's first name"),
            ),

            SizedBox(height: KidSpacing.lg * formFactor.density),
            Text('Pick a buddy', style: theme.textTheme.titleMedium),
            SizedBox(height: KidSpacing.sm * formFactor.density),
            Wrap(
              spacing: KidSpacing.sm * formFactor.density,
              runSpacing: KidSpacing.sm * formFactor.density,
              children: [
                for (final entry in MascotStage.characters.entries)
                  KidFocusable(
                    onPressed: () => setState(() => _avatar = entry.key),
                    borderRadius: KidRadius.pill,
                    semanticLabel: entry.key,
                    child: Container(
                      width: KidTouch.recommended * formFactor.density,
                      height: KidTouch.recommended * formFactor.density,
                      alignment: Alignment.center,
                      decoration: BoxDecoration(
                        color: _avatar == entry.key ? accent.withValues(alpha: 0.18) : theme.colorScheme.surface,
                        shape: BoxShape.circle,
                        border: Border.all(
                          color: _avatar == entry.key ? accent : theme.colorScheme.outline,
                          width: _avatar == entry.key ? 3 : 1,
                        ),
                      ),
                      child: Text(entry.value, style: TextStyle(fontSize: 26 * formFactor.density)),
                    ),
                  ),
              ],
            ),

            SizedBox(height: KidSpacing.lg * formFactor.density),
            Text('Favourite colour', style: theme.textTheme.titleMedium),
            SizedBox(height: KidSpacing.sm * formFactor.density),
            Row(
              children: [
                for (final entry in _colors.entries)
                  Padding(
                    padding: EdgeInsets.only(right: KidSpacing.sm * formFactor.density),
                    child: KidFocusable(
                      onPressed: () => setState(() => _color = entry.key),
                      borderRadius: KidRadius.pill,
                      semanticLabel: entry.key,
                      child: Container(
                        width: KidTouch.min * formFactor.density,
                        height: KidTouch.min * formFactor.density,
                        decoration: BoxDecoration(
                          color: entry.value,
                          shape: BoxShape.circle,
                          border: Border.all(
                            color: _color == entry.key ? KidColors.ink : Colors.transparent,
                            width: 3,
                          ),
                        ),
                      ),
                    ),
                  ),
              ],
            ),

            SizedBox(height: KidSpacing.lg * formFactor.density),
            Text('Birthday (sets the learning level)', style: theme.textTheme.titleMedium),
            SizedBox(height: KidSpacing.sm * formFactor.density),
            KidButton(
              label: _birthdate == null
                  ? 'Choose a birthday'
                  : '${_birthdate!.day}/${_birthdate!.month}/${_birthdate!.year}  ·  $_level',
              icon: Icons.cake_rounded,
              tone: KidButtonTone.neutral,
              expand: true,
              onPressed: () async {
                final now = DateTime.now();
                final picked = await showDatePicker(
                  context: context,
                  initialDate: DateTime(now.year - 4, now.month, now.day),
                  firstDate: DateTime(now.year - 9),
                  lastDate: now,
                  helpText: "Child's birthday",
                );

                if (picked != null) setState(() => _birthdate = picked);
              },
            ),

            SizedBox(height: KidSpacing.xl * formFactor.density),
            KidButton(
              label: 'Start the adventure',
              icon: Icons.rocket_launch_rounded,
              expand: true,
              busy: _busy,
              onPressed: _busy ? null : _save,
            ),
            SizedBox(height: KidSpacing.md * formFactor.density),
            TextButton(
              onPressed: () => context.go('/profiles'),
              child: const Text('Back to explorers'),
            ),
            SizedBox(height: KidSpacing.xl * formFactor.density),
          ],
        ),
      ),
    );
  }
}
