import 'package:flutter/material.dart';

import '../../core/platform/form_factor.dart';
import '../tokens.dart';
import 'counters.dart';
import 'focus_ring.dart';

/// The strip along the top of every child-facing screen.
///
/// Left is the way out, right is the parent gate, and everything a child wants
/// to look at sits in between. It is the same shape on a phone and a TV so the
/// family never has to learn it twice.
class KidExitBar extends StatelessWidget implements PreferredSizeWidget {
  const KidExitBar({
    super.key,
    this.title,
    this.onBack,
    this.onParentZone,
    this.stars,
    this.coins,
    this.streakDays,
    this.minutesLeft,
    this.unlimitedTime = false,
    this.onCoinsPressed,
    this.onSongs,
    this.offline = false,
    this.pendingSync = 0,
  });

  final String? title;
  final VoidCallback? onBack;
  final VoidCallback? onParentZone;
  final int? stars;
  final int? coins;
  final int? streakDays;
  final int? minutesLeft;
  final bool unlimitedTime;
  final VoidCallback? onCoinsPressed;
  final VoidCallback? onSongs;
  final bool offline;
  final int pendingSync;

  // Tall enough for the television density (1.4x) without clipping the pills.
  @override
  Size get preferredSize => const Size.fromHeight(96);

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);

    // The scaffold applies the television overscan margin to its body, not to
    // the bar, so the bar has to keep itself off the edge of the panel.
    final overscan = formFactor.safeInsets.left;

    return SafeArea(
      bottom: false,
      child: Padding(
        padding: EdgeInsets.fromLTRB(
          KidSpacing.md * formFactor.density + overscan,
          KidSpacing.sm * formFactor.density,
          KidSpacing.md * formFactor.density + overscan,
          KidSpacing.sm * formFactor.density,
        ),
        child: Row(
          children: [
            if (onBack != null)
              KidFocusable(
                onPressed: onBack,
                borderRadius: KidRadius.pill,
                semanticLabel: 'Back',
                child: Container(
                  width: KidTouch.min * formFactor.density,
                  height: KidTouch.min * formFactor.density,
                  alignment: Alignment.center,
                  decoration: BoxDecoration(color: theme.colorScheme.surface, borderRadius: KidRadius.pill),
                  child: Icon(Icons.arrow_back_rounded, color: theme.colorScheme.onSurface),
                ),
              ),
            if (title != null) ...[
              SizedBox(width: KidSpacing.sm * formFactor.density),
              Expanded(
                child: Text(
                  title!,
                  overflow: TextOverflow.ellipsis,
                  style: theme.textTheme.titleLarge,
                ),
              ),
            ] else
              const Spacer(),
            if (offline)
              Padding(
                padding: EdgeInsets.only(right: KidSpacing.sm * formFactor.density),
                child: KidCounter(
                  icon: '📴',
                  value: pendingSync > 0 ? '$pendingSync' : 'Offline',
                  label: pendingSync > 0
                      ? '$pendingSync updates waiting to upload'
                      : 'Playing offline',
                  background: theme.colorScheme.surface,
                  foreground: theme.colorScheme.onSurfaceVariant,
                ),
              ),
            if (minutesLeft != null || unlimitedTime)
              Padding(
                padding: EdgeInsets.only(right: KidSpacing.sm * formFactor.density),
                child: TimeRemainingPill(minutesLeft: minutesLeft ?? 0, unlimited: unlimitedTime),
              ),
            if (streakDays != null && streakDays! > 0)
              Padding(
                padding: EdgeInsets.only(right: KidSpacing.sm * formFactor.density),
                child: StreakChip(days: streakDays!),
              ),
            if (coins != null)
              Padding(
                padding: EdgeInsets.only(right: KidSpacing.sm * formFactor.density),
                child: CoinCounter(coins: coins!, onPressed: onCoinsPressed),
              ),
            if (stars != null)
              Padding(
                padding: EdgeInsets.only(right: KidSpacing.sm * formFactor.density),
                child: StarCounter(stars: stars!),
              ),
            if (onSongs != null)
              Padding(
                padding: EdgeInsets.only(right: KidSpacing.sm * formFactor.density),
                child: KidFocusable(
                  onPressed: onSongs,
                  borderRadius: KidRadius.pill,
                  semanticLabel: 'Songs',
                  child: Container(
                    width: KidTouch.min * formFactor.density,
                    height: KidTouch.min * formFactor.density,
                    alignment: Alignment.center,
                    decoration: BoxDecoration(color: theme.colorScheme.surface, borderRadius: KidRadius.pill),
                    child: const Text('🎵', style: TextStyle(fontSize: 20)),
                  ),
                ),
              ),
            if (onParentZone != null)
              KidFocusable(
                onPressed: onParentZone,
                borderRadius: KidRadius.pill,
                semanticLabel: 'Parent zone',
                child: Container(
                  width: KidTouch.min * formFactor.density,
                  height: KidTouch.min * formFactor.density,
                  alignment: Alignment.center,
                  decoration: BoxDecoration(color: theme.colorScheme.surface, borderRadius: KidRadius.pill),
                  child: const Text('🔐', style: TextStyle(fontSize: 20)),
                ),
              ),
          ],
        ),
      ),
    );
  }
}
