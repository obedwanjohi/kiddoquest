import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../app/providers.dart';
import '../../../core/audio/speech_listener.dart';
import '../../../core/platform/form_factor.dart';
import '../../../design/components/kid_button.dart';
import '../../../design/components/pack_image.dart';
import '../../../design/tokens.dart';
import 'renderer_kit.dart';

/// Say the word.
///
/// Where the device can hear, it listens and tells the child it heard them.
/// Where it cannot — a television with no microphone, a cheap phone with no
/// language pack, a browser that refuses — the same screen runs as practice:
/// the child says the word and taps to move on.
///
/// Recognition never decides the mark. It mishears small children constantly,
/// and a four-year-old who said "seven" perfectly well must not be told they
/// were wrong because a model in a data centre disagreed. So both paths submit
/// an answer the shared scorer counts, and only skipping scores nothing.
class SpeakRepeatRenderer extends ConsumerStatefulWidget {
  const SpeakRepeatRenderer({super.key, required this.context_});

  final RendererContext context_;

  @override
  ConsumerState<SpeakRepeatRenderer> createState() => _SpeakRepeatRendererState();
}

class _SpeakRepeatRendererState extends ConsumerState<SpeakRepeatRenderer> {
  bool _canHear = false;
  bool _listening = false;
  String? _heard;

  // Held rather than read on the way out: reaching for a provider through ref
  // during dispose is reaching through a BuildContext that is already gone.
  late final SpeechListener _listener;

  @override
  void initState() {
    super.initState();
    _listener = ref.read(speechListenerProvider);
    WidgetsBinding.instance.addPostFrameCallback((_) => _askIfWeCanHear());
  }

  @override
  void dispose() {
    _listener.stop();
    super.dispose();
  }

  Future<void> _askIfWeCanHear() async {
    final available = await _listener.isAvailable();

    if (mounted) setState(() => _canHear = available);
  }

  /// The word to say: the author's metadata, the scoring target, the first
  /// option, or failing all of those the prompt itself.
  String get _word {
    final metadata = widget.context_.question.metadata['word']?.toString();
    if (metadata != null && metadata.trim().isNotEmpty) return metadata.trim();

    final target = widget.context_.question.scoringConfig['target_word']?.toString();
    if (target != null && target.trim().isNotEmpty) return target.trim();

    final option = widget.context_.options.firstOrNull?.text;
    if (option != null && option.trim().isNotEmpty) return option.trim();

    return widget.context_.question.prompt ?? '';
  }

  Future<void> _listen() async {
    setState(() {
      _listening = true;
      _heard = null;
    });

    await _listener.listen(
          onHeard: (heard) {
            if (!mounted) return;

            setState(() {
              _listening = false;
              _heard = heard;
            });

            // Heard or not, the child said it. The mark is the same; only the
            // words on the screen change.
            widget.context_.submit({
              'mode': 'recognized',
              'said': heard,
              'matched': SpeechListener.matches(heard, _word),
            });
          },
        );
  }

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);
    final answering = widget.context_.isAnswering;

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
              if (widget.context_.question.image != null) ...[
                PackImage(
                  mediaKey: widget.context_.question.image,
                  media: widget.context_.media,
                  height: 120,
                ),
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

        AnimatedScale(
          duration: KidMotion.normal,
          scale: _listening ? 1.3 : 1,
          child: Text('🎤', style: TextStyle(fontSize: 44 * formFactor.density)),
        ),

        if (_heard != null && _heard!.isNotEmpty) ...[
          SizedBox(height: KidSpacing.xs * formFactor.density),
          Text(
            'I heard “${_heard!}”',
            style: theme.textTheme.titleMedium?.copyWith(color: theme.colorScheme.onSurfaceVariant),
          ),
        ],

        SizedBox(height: KidSpacing.md * formFactor.density),

        if (_canHear)
          KidButton(
            label: _listening ? 'Listening…' : 'Tap and say it',
            tone: KidButtonTone.success,
            icon: Icons.mic_rounded,
            busy: _listening,
            autofocus: true,
            onPressed: answering && !_listening ? _listen : null,
          )
        else
          KidButton(
            label: 'I said it!',
            tone: KidButtonTone.success,
            icon: Icons.check_rounded,
            autofocus: true,
            onPressed: answering ? () => widget.context_.submit({'mode': 'practice'}) : null,
          ),

        SizedBox(height: KidSpacing.sm * formFactor.density),

        TextButton(
          onPressed: answering ? () => widget.context_.submit({'mode': 'skipped'}) : null,
          child: Text(
            'Skip this one',
            style: theme.textTheme.labelLarge?.copyWith(color: theme.colorScheme.onSurfaceVariant),
          ),
        ),
      ],
    );
  }
}
