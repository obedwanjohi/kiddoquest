import 'package:flutter/material.dart';

import '../../core/platform/form_factor.dart';
import '../tokens.dart';

/// A small pill showing one number the child cares about.
class KidCounter extends StatelessWidget {
  const KidCounter({
    super.key,
    required this.icon,
    required this.value,
    this.background,
    this.foreground,
    this.label,
    this.onPressed,
  });

  final String icon;
  final String value;
  final Color? background;
  final Color? foreground;
  final String? label;
  final VoidCallback? onPressed;

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final fg = foreground ?? KidColors.ink;

    final pill = Container(
      padding: EdgeInsets.symmetric(
        horizontal: KidSpacing.md * formFactor.density,
        vertical: KidSpacing.sm * formFactor.density,
      ),
      decoration: BoxDecoration(
        color: background ?? KidColors.amberSoft,
        borderRadius: KidRadius.pill,
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(icon, style: TextStyle(fontSize: 16 * formFactor.density)),
          SizedBox(width: KidSpacing.xs * formFactor.density),
          Text(
            value,
            style: TextStyle(
              fontFamily: KidFonts.display,
              fontWeight: FontWeight.w800,
              fontSize: KidTypeScale.body * formFactor.density,
              color: fg,
            ),
          ),
        ],
      ),
    );

    if (onPressed == null) return Semantics(label: label, child: pill);

    return Semantics(
      label: label,
      button: true,
      child: InkWell(onTap: onPressed, borderRadius: KidRadius.pill, child: pill),
    );
  }
}

class StarCounter extends StatelessWidget {
  const StarCounter({super.key, required this.stars});

  final int stars;

  @override
  Widget build(BuildContext context) =>
      KidCounter(icon: '⭐', value: '$stars', label: '$stars stars', background: KidColors.amberSoft);
}

class CoinCounter extends StatelessWidget {
  const CoinCounter({super.key, required this.coins, this.onPressed});

  final int coins;
  final VoidCallback? onPressed;

  @override
  Widget build(BuildContext context) => KidCounter(
        icon: '🪙',
        value: '$coins',
        label: '$coins star coins',
        background: const Color(0xFFFDE68A),
        onPressed: onPressed,
      );
}

class StreakChip extends StatelessWidget {
  const StreakChip({super.key, required this.days});

  final int days;

  @override
  Widget build(BuildContext context) => KidCounter(
        icon: '🔥',
        value: '$days',
        label: '$days day streak',
        background: const Color(0xFFFFE4E6),
        foreground: const Color(0xFF9F1239),
      );
}

/// Time left today. Colour follows the website's thresholds: green, then amber
/// under ten minutes, then red under five, so a child sees it coming.
class TimeRemainingPill extends StatelessWidget {
  const TimeRemainingPill({super.key, required this.minutesLeft, this.unlimited = false});

  final int minutesLeft;
  final bool unlimited;

  @override
  Widget build(BuildContext context) {
    if (unlimited) {
      return const KidCounter(icon: '⏳', value: '∞', label: 'No time limit', background: KidColors.successSoft);
    }

    final (background, foreground) = switch (minutesLeft) {
      <= 5 => (const Color(0xFFFEE2E2), const Color(0xFF991B1B)),
      <= 10 => (KidColors.amberSoft, const Color(0xFF92400E)),
      _ => (KidColors.successSoft, const Color(0xFF14532D)),
    };

    return KidCounter(
      icon: '⏳',
      value: '$minutesLeft m',
      label: '$minutesLeft minutes left today',
      background: background,
      foreground: foreground,
    );
  }
}
