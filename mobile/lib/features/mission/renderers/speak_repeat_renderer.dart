import 'package:flutter/material.dart';

import '../../../core/platform/form_factor.dart';
import '../../../design/components/kid_button.dart';
import '../../../design/components/pack_image.dart';
import '../../../design/tokens.dart';
import 'renderer_kit.dart';

/// Say the word.
///
/// Speech recognition is not wired up yet, so this runs in practice mode: the
/// child sees the word, says it, and taps to move on. That is a deliberate
/// choice rather than a stub. Recognition is unavailable on a great many of the
/// devices this product is for — cheap phones with no language pack, television
/// boxes with no microphone — and a three-year-old cannot be told to buy better
/// hardware. The shared scorer therefore treats an honest practice attempt as
/// correct on every platform, and only skipping scores nothing.
///
/// When recognition is added, it reports `mode: recognized` and nothing else
/// here or on the server has to change.
class SpeakRepeatRenderer extends StatelessWidget {
  const SpeakRepeatRenderer({super.key, required this.context_});

  final RendererContext context_;

  /// The word to say: the author's metadata, the scoring target, the first
  /// option, or failing all of those the prompt itself.
  String get _word {
    final metadata = context_.question.metadata['word']?.toString();
    if (metadata != null && metadata.trim().isNotEmpty) return metadata.trim();

    final target = context_.question.scoringConfig['target_word']?.toString();
    if (target != null && target.trim().isNotEmpty) return target.trim();

    final option = context_.options.firstOrNull?.text;
    if (option != null && option.trim().isNotEmpty) return option.trim();

    return context_.question.prompt ?? '';
  }

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);

    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        const RendererPrompt(text: 'Say it out loud'),

        Container(
          padding: EdgeInsets.symmetric(
            horizontal: KidSpacing.xl * formFactor.density,
            vertical: KidSpacing.lg * formFactor.density,
          ),
          decoration: BoxDecoration(
            color: theme.colorScheme.surface,
            borderRadius: KidRadius.card,
            border: Border.all(color: KidColors.primary, width: 3),
            boxShadow: KidShadows.edge(KidColors.primaryDark.withValues(alpha: 0.4)),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              if (context_.question.image != null) ...[
                PackImage(mediaKey: context_.question.image, media: context_.media, height: 120),
                SizedBox(height: KidSpacing.sm * formFactor.density),
              ],
              Text(
                _word,
                textAlign: TextAlign.center,
                style: theme.textTheme.displayMedium,
              ),
            ],
          ),
        ),

        SizedBox(height: KidSpacing.lg * formFactor.density),

        Text('🎤', style: TextStyle(fontSize: 44 * formFactor.density)),

        SizedBox(height: KidSpacing.md * formFactor.density),

        KidButton(
          label: 'I said it!',
          tone: KidButtonTone.success,
          icon: Icons.check_rounded,
          autofocus: true,
          onPressed: context_.isAnswering ? () => context_.submit({'mode': 'practice'}) : null,
        ),

        SizedBox(height: KidSpacing.sm * formFactor.density),

        TextButton(
          onPressed: context_.isAnswering ? () => context_.submit({'mode': 'skipped'}) : null,
          child: Text(
            'Skip this one',
            style: theme.textTheme.labelLarge?.copyWith(color: theme.colorScheme.onSurfaceVariant),
          ),
        ),
      ],
    );
  }
}
