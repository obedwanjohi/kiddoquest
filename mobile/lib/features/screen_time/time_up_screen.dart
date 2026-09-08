import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/providers.dart';
import '../../core/platform/form_factor.dart';
import '../../design/components/kid_button.dart';
import '../../design/components/kid_scaffold.dart';
import '../../design/components/mascot_stage.dart';
import '../../design/tokens.dart';
import 'screen_time.dart';

/// Leo's sleep mode: today's learning time is used up.
///
/// The tone matters more than the mechanism. A child is not being punished and
/// has not failed at anything, so this is warm, final and short, and it names
/// what they did rather than what they cannot do.
class TimeUpScreen extends ConsumerWidget {
  const TimeUpScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);
    final child = ref.watch(sessionProvider).activeChild;
    final screenTime = child == null ? null : ref.watch(screenTimeProvider(child)).value;

    return KidScaffold(
      onBack: () => context.go('/profiles'),
      child: Center(
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              MascotStage(mood: MascotMood.sleeping, size: formFactor.isTv ? 170 : 120),
              SizedBox(height: KidSpacing.md * formFactor.density),
              Text(
                'That is all for today',
                textAlign: TextAlign.center,
                style: theme.textTheme.displayMedium,
              ),
              SizedBox(height: KidSpacing.sm * formFactor.density),
              Text(
                screenTime == null
                    ? 'Leo is having a rest. Come back tomorrow!'
                    : 'You learned for ${screenTime.usedMinutes} minutes. '
                        'Leo is having a rest now — come back tomorrow!',
                textAlign: TextAlign.center,
                style: theme.textTheme.bodyLarge,
              ),
              SizedBox(height: KidSpacing.xl * formFactor.density),
              KidButton(
                label: 'See my explorers',
                icon: Icons.people_alt_rounded,
                autofocus: true,
                onPressed: () {
                  ref.read(sessionProvider.notifier).leaveChild();
                  context.go('/profiles');
                },
              ),
              SizedBox(height: KidSpacing.md * formFactor.density),
              TextButton(
                onPressed: () => context.go('/parent'),
                child: const Text('Grown-up? Change the daily limit'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
