import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/providers.dart';
import '../../core/platform/form_factor.dart';
import '../../design/components/kid_button.dart';
import '../../design/components/kid_scaffold.dart';
import '../../design/components/mascot_stage.dart';
import '../../design/tokens.dart';

/// The story panel a child sees before a mission starts.
///
/// One line from Leo, three facts, one button. On the website this screen is a
/// wall of text; here it is the shape the design system asks for, because a
/// three-year-old is not reading any of it.
class MissionBriefingScreen extends ConsumerStatefulWidget {
  const MissionBriefingScreen({super.key, required this.missionId});

  final int missionId;

  @override
  ConsumerState<MissionBriefingScreen> createState() => _MissionBriefingScreenState();
}

class _MissionBriefingScreenState extends ConsumerState<MissionBriefingScreen> {
  Map<String, Object?>? _mission;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _load());
  }

  Future<void> _load() async {
    final row = await ref.read(contentDaoProvider).mission(widget.missionId);

    if (!mounted) return;

    setState(() {
      _mission = row;
      _loading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);

    if (_loading) {
      return const KidScaffold(child: LeoLoading(message: 'Opening the story…'));
    }

    final mission = _mission;

    if (mission == null) {
      return KidScaffold(
        child: LeoMessage(
          title: 'That mission is not here yet',
          body: 'Download this world first and it will be ready to play offline.',
          action: KidButton(label: 'Back to the map', onPressed: () => context.go('/map')),
        ),
      );
    }

    final title = (mission['display_title'] as String?)?.isNotEmpty == true
        ? mission['display_title'] as String
        : (mission['title'] as String? ?? 'Mission');

    final story = (mission['intro_text'] as String?)?.trim();
    final minutes = mission['estimated_minutes'] as int? ?? 5;
    final stars = mission['stars_reward'] as int? ?? 3;
    final questions = mission['questions_per_session'] as int? ?? 6;
    final hasVideo = (mission['video_path'] as String?)?.isNotEmpty == true;

    return KidScaffold(
      onBack: () => context.go('/map'),
      child: Center(
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              MascotStage(mood: MascotMood.talking, size: formFactor.isTv ? 150 : 100),
              SizedBox(height: KidSpacing.md * formFactor.density),
              Text(title, textAlign: TextAlign.center, style: theme.textTheme.displayMedium),
              if (story != null && story.isNotEmpty) ...[
                SizedBox(height: KidSpacing.md * formFactor.density),
                MascotSpeech(text: story),
              ],
              SizedBox(height: KidSpacing.lg * formFactor.density),
              Wrap(
                alignment: WrapAlignment.center,
                spacing: KidSpacing.sm * formFactor.density,
                runSpacing: KidSpacing.sm * formFactor.density,
                children: [
                  _StatChip(emoji: '⏱', label: '$minutes min'),
                  _StatChip(emoji: '⭐', label: 'up to $stars'),
                  _StatChip(emoji: '❓', label: '$questions questions'),
                ],
              ),
              SizedBox(height: KidSpacing.xl * formFactor.density),
              KidButton(
                label: 'Start mission!',
                icon: Icons.rocket_launch_rounded,
                tone: KidButtonTone.amber,
                size: KidButtonSize.large,
                autofocus: true,
                // A mission with a film starts with the film; everything else
                // goes straight to the questions.
                onPressed: () => context.go(
                  hasVideo
                      ? '/mission/${widget.missionId}/video'
                      : '/mission/${widget.missionId}/play',
                ),
              ),
              SizedBox(height: KidSpacing.md * formFactor.density),
              TextButton(
                onPressed: () => context.go('/map'),
                child: const Text('Back to the map'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _StatChip extends StatelessWidget {
  const _StatChip({required this.emoji, required this.label});

  final String emoji;
  final String label;

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);

    return Container(
      padding: EdgeInsets.symmetric(
        horizontal: KidSpacing.md * formFactor.density,
        vertical: KidSpacing.sm * formFactor.density,
      ),
      decoration: BoxDecoration(
        color: theme.colorScheme.surface,
        borderRadius: KidRadius.pill,
        border: Border.all(color: theme.colorScheme.outline, width: 2),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(emoji, style: TextStyle(fontSize: 16 * formFactor.density)),
          SizedBox(width: KidSpacing.xs * formFactor.density),
          Text(label, style: theme.textTheme.titleMedium),
        ],
      ),
    );
  }
}
