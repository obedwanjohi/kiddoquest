import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/providers.dart';
import '../../core/audio/audio_director.dart';
import '../../core/models/extras.dart';
import '../../core/platform/form_factor.dart';
import '../../design/components/kid_button.dart';
import '../../design/components/kid_scaffold.dart';
import '../../design/components/mascot_stage.dart';
import '../../design/tokens.dart';

/// The daily devotional: a verse, a thought and a short prayer.
///
/// Read out loud the moment it opens, because the child it is written for
/// cannot read it. Shown once a day — a devotional that appears every time the
/// map is opened stops being a moment and becomes an obstacle.
class DevotionalScreen extends ConsumerStatefulWidget {
  const DevotionalScreen({super.key});

  @override
  ConsumerState<DevotionalScreen> createState() => _DevotionalScreenState();
}

class _DevotionalScreenState extends ConsumerState<DevotionalScreen> {
  bool _spoke = false;

  // Held rather than read on the way out: reaching for a provider through ref
  // during dispose is reaching through a BuildContext that is already gone.
  late final AudioDirector _audio;

  @override
  void initState() {
    super.initState();
    _audio = ref.read(audioDirectorProvider);
  }

  @override
  void dispose() {
    _audio.stop();
    super.dispose();
  }

  Future<void> _speak(Devotional devotional) async {
    await _audio.say(text: devotional.spoken);
  }

  @override
  Widget build(BuildContext context) {
    final extras = ref.watch(extrasProvider);
    final formFactor = context.formFactor;
    final theme = Theme.of(context);

    return KidScaffold(
      onBack: () => context.go('/map'),
      child: extras.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (_, _) => _CouldNotLoad(onBack: () => context.go('/map')),
        data: (data) {
          final devotional = data.devotionalFor(DateTime.now());

          if (devotional == null) {
            return _CouldNotLoad(onBack: () => context.go('/map'));
          }

          if (!_spoke) {
            _spoke = true;
            WidgetsBinding.instance.addPostFrameCallback((_) => _speak(devotional));
          }

          return Center(
            child: SingleChildScrollView(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  SizedBox(height: KidSpacing.lg * formFactor.density),
                  Text(
                    devotional.emoji,
                    style: TextStyle(fontSize: 72 * formFactor.density),
                  ),
                  SizedBox(height: KidSpacing.md * formFactor.density),
                  Text(
                    'Today’s verse',
                    style: theme.textTheme.labelLarge?.copyWith(
                      color: theme.colorScheme.onSurfaceVariant,
                      letterSpacing: 1.2,
                    ),
                  ),
                  SizedBox(height: KidSpacing.sm * formFactor.density),
                  Text(
                    '“${devotional.verseText}”',
                    textAlign: TextAlign.center,
                    style: theme.textTheme.headlineSmall?.copyWith(fontFamily: KidFonts.display),
                  ),
                  SizedBox(height: KidSpacing.xs * formFactor.density),
                  Text(
                    devotional.verseRef,
                    style: theme.textTheme.titleMedium?.copyWith(color: KidColors.primary),
                  ),
                  SizedBox(height: KidSpacing.lg * formFactor.density),
                  _Panel(title: 'What it means', body: devotional.teaching),
                  SizedBox(height: KidSpacing.md * formFactor.density),
                  _Panel(title: 'Let’s pray', body: devotional.prayer, tint: KidColors.amberSoft),
                  SizedBox(height: KidSpacing.lg * formFactor.density),
                  Wrap(
                    spacing: KidSpacing.md,
                    runSpacing: KidSpacing.sm,
                    alignment: WrapAlignment.center,
                    children: [
                      KidButton(
                        label: 'Read it to me again',
                        icon: Icons.volume_up_rounded,
                        tone: KidButtonTone.neutral,
                        onPressed: () => _speak(devotional),
                      ),
                      KidButton(
                        label: 'Amen — let’s play',
                        icon: Icons.favorite_rounded,
                        autofocus: true,
                        onPressed: () {
                          _audio.stop();
                          context.go('/map');
                        },
                      ),
                    ],
                  ),
                  SizedBox(height: KidSpacing.xl * formFactor.density),
                ],
              ),
            ),
          );
        },
      ),
    );
  }
}

class _Panel extends StatelessWidget {
  const _Panel({required this.title, required this.body, this.tint});

  final String title;
  final String body;
  final Color? tint;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final density = context.formFactor.density;

    return Container(
      width: double.infinity,
      padding: EdgeInsets.all(KidSpacing.md * density),
      decoration: BoxDecoration(
        color: tint ?? KidColors.primarySoft,
        borderRadius: KidRadius.card,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title, style: theme.textTheme.titleMedium?.copyWith(color: KidColors.stageInk)),
          SizedBox(height: KidSpacing.xs * density),
          Text(body, style: theme.textTheme.bodyLarge?.copyWith(color: KidColors.stageInk)),
        ],
      ),
    );
  }
}

class _CouldNotLoad extends StatelessWidget {
  const _CouldNotLoad({required this.onBack});

  final VoidCallback onBack;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const MascotStage(mood: MascotMood.thinking),
          SizedBox(height: KidSpacing.md * context.formFactor.density),
          Text(
            'Today’s verse is not here yet.',
            style: Theme.of(context).textTheme.titleLarge,
          ),
          SizedBox(height: KidSpacing.md * context.formFactor.density),
          KidButton(label: 'Back to the map', autofocus: true, onPressed: onBack),
        ],
      ),
    );
  }
}
