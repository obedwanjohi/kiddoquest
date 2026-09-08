import 'package:flutter/material.dart';

import '../../core/platform/form_factor.dart';
import '../tokens.dart';

/// How far through a mission the child is.
///
/// Deliberately not a percentage: three-year-olds do not read percentages, so
/// this is a row of filled beads plus a bar that only ever moves forward.
class KidProgressBar extends StatelessWidget {
  const KidProgressBar({
    super.key,
    required this.current,
    required this.total,
    this.accent = KidColors.primary,
    this.showBeads = true,
  });

  final int current;
  final int total;
  final Color accent;
  final bool showBeads;

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final safeTotal = total <= 0 ? 1 : total;
    final value = (current / safeTotal).clamp(0.0, 1.0);

    if (showBeads && safeTotal <= 10) {
      return Row(
        children: List.generate(safeTotal, (index) {
          final done = index < current;
          return Expanded(
            child: Padding(
              padding: EdgeInsets.symmetric(horizontal: 2 * formFactor.density),
              child: AnimatedContainer(
                duration: KidMotion.normal,
                curve: KidMotion.spring,
                height: 12 * formFactor.density,
                decoration: BoxDecoration(
                  color: done ? accent : accent.withValues(alpha: 0.18),
                  borderRadius: KidRadius.pill,
                ),
              ),
            ),
          );
        }),
      );
    }

    return ClipRRect(
      borderRadius: KidRadius.pill,
      child: LinearProgressIndicator(
        value: value,
        minHeight: 12 * formFactor.density,
        backgroundColor: accent.withValues(alpha: 0.18),
        valueColor: AlwaysStoppedAnimation(accent),
      ),
    );
  }
}

/// The zero to three stars a mission is worth, used on nodes and in the
/// celebration.
class StarRow extends StatelessWidget {
  const StarRow({super.key, required this.stars, this.size = 18, this.max = 3});

  final int stars;
  final double size;
  final int max;

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;

    return Row(
      mainAxisSize: MainAxisSize.min,
      children: List.generate(max, (index) {
        final earned = index < stars;
        return Padding(
          padding: const EdgeInsets.symmetric(horizontal: 1),
          child: Text(
            earned ? '⭐' : '☆',
            style: TextStyle(
              fontSize: size * formFactor.density,
              color: earned ? KidColors.amber : KidColors.muted,
            ),
          ),
        );
      }),
    );
  }
}
