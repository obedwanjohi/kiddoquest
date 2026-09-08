import 'dart:math' as math;

import 'package:flutter/material.dart';

import '../../core/platform/form_factor.dart';
import '../tokens.dart';

/// What Leo is doing right now.
enum MascotMood { idle, talking, cheering, thinking, encouraging, sleeping }

/// Leo the Lion, and the buddies.
///
/// The plan calls for rigged Rive characters. Those assets do not exist yet, so
/// this renders the same character set as animated emoji with a breathing idle,
/// a bounce when talking and a cheer. Swapping in Rive later means changing the
/// body of this one widget: nothing else in the app knows how Leo is drawn.
class MascotStage extends StatefulWidget {
  const MascotStage({
    super.key,
    this.character = 'lion',
    this.mood = MascotMood.idle,
    this.size,
    this.hat,
  });

  final String character;
  final MascotMood mood;
  final double? size;
  final String? hat;

  /// The cast, by the identifiers the database already stores.
  static const Map<String, String> characters = {
    'lion': '🦁',
    'elephant': '🐘',
    'giraffe': '🦒',
    'monkey': '🐒',
    'tiger': '🐯',
    'fox': '🦊',
    'panda': '🐼',
    'koala': '🐨',
    'rabbit': '🐰',
    'frog': '🐸',
    'owl': '🦉',
    'cat': '🐱',
    'dog': '🐶',
    'cow': '🐮',
    'pig': '🐷',
    'unicorn': '🦄',
    'dino': '🦖',
    'robot': '🤖',
    'dragon': '🐉',
  };

  static const Map<String, String> hats = {
    'hat_star': '🌟',
    'hat_crown': '👑',
    'hat_pirate': '🏴‍☠️',
    'hat_superhero': '🦸',
    'hat_sunglasses': '🕶️',
    'hat_astronaut': '👨‍🚀',
    'hat_dino': '🦖',
    'hat_party': '🥳',
  };

  static String emojiFor(String? character) => characters[character ?? 'lion'] ?? '🧒';

  @override
  State<MascotStage> createState() => _MascotStageState();
}

class _MascotStageState extends State<MascotStage> with SingleTickerProviderStateMixin {
  late final AnimationController _controller = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 2400),
  )..repeat();

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final size = widget.size ?? (formFactor.isTv ? 132.0 : 84.0);
    final reduceMotion = MediaQuery.maybeDisableAnimationsOf(context) ?? false;

    return AnimatedBuilder(
      animation: _controller,
      builder: (context, _) {
        final t = _controller.value;
        double scale = 1;
        double dy = 0;
        double angle = 0;

        if (!reduceMotion) {
          switch (widget.mood) {
            case MascotMood.idle:
              scale = 1 + math.sin(t * math.pi * 2) * 0.02;
              break;
            case MascotMood.talking:
              scale = 1 + math.sin(t * math.pi * 8) * 0.035;
              break;
            case MascotMood.cheering:
              dy = -math.sin(t * math.pi * 4).abs() * size * 0.12;
              angle = math.sin(t * math.pi * 4) * 0.08;
              break;
            case MascotMood.thinking:
              angle = math.sin(t * math.pi * 2) * 0.05;
              break;
            case MascotMood.encouraging:
              angle = math.sin(t * math.pi * 6) * 0.06;
              break;
            case MascotMood.sleeping:
              scale = 1 + math.sin(t * math.pi) * 0.015;
              break;
          }
        }

        return SizedBox(
          width: size,
          height: size,
          child: Stack(
            alignment: Alignment.center,
            clipBehavior: Clip.none,
            children: [
              Transform.translate(
                offset: Offset(0, dy),
                child: Transform.rotate(
                  angle: angle,
                  child: Transform.scale(
                    scale: scale,
                    child: Text(
                      MascotStage.emojiFor(widget.character),
                      style: TextStyle(fontSize: size * 0.82),
                    ),
                  ),
                ),
              ),
              if (widget.hat != null && MascotStage.hats.containsKey(widget.hat))
                Positioned(
                  top: -size * 0.06,
                  child: Text(
                    MascotStage.hats[widget.hat]!,
                    style: TextStyle(fontSize: size * 0.34),
                  ),
                ),
              if (widget.mood == MascotMood.sleeping)
                Positioned(
                  top: 0,
                  right: 0,
                  child: Text('💤', style: TextStyle(fontSize: size * 0.24)),
                ),
            ],
          ),
        );
      },
    );
  }
}

/// Leo's speech bubble. Short lines only: five words for a three-year-old.
class MascotSpeech extends StatelessWidget {
  const MascotSpeech({
    super.key,
    required this.text,
    this.onReplay,
    this.tail = true,
  });

  final String text;
  final VoidCallback? onReplay;
  final bool tail;

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);

    return Container(
      padding: EdgeInsets.symmetric(
        horizontal: KidSpacing.md * formFactor.density,
        vertical: KidSpacing.md * formFactor.density,
      ),
      decoration: BoxDecoration(
        color: theme.colorScheme.surface,
        borderRadius: KidRadius.card,
        border: Border.all(color: theme.colorScheme.outline, width: 2),
        boxShadow: KidShadows.soft,
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(
            child: Text(
              text,
              style: theme.textTheme.headlineMedium?.copyWith(
                fontSize: (formFactor.isTv ? 32 : KidTypeScale.question) * (formFactor.isTv ? 1.0 : formFactor.density),
              ),
            ),
          ),
          if (onReplay != null) ...[
            SizedBox(width: KidSpacing.sm * formFactor.density),
            IconButton(
              onPressed: onReplay,
              iconSize: 28 * formFactor.density,
              icon: const Icon(Icons.volume_up_rounded, color: KidColors.primary),
              tooltip: 'Say it again',
            ),
          ],
        ],
      ),
    );
  }
}
