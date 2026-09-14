import 'package:flutter/material.dart';

import '../components/focus_ring.dart';
import 'tw.dart';
import 'tw_motion.dart';

/// The bar across the top of every kid page, as the website draws it.
///
/// Mirrors `resources/views/components/kid/exit-bar.blade.php`: Home (on the
/// map) or Map (everywhere else) and the coin pouch on the left, the page title
/// and the time left in the middle, the star counter and the parent cog on the
/// right.
class SiteExitBar extends StatelessWidget {
  const SiteExitBar({
    super.key,
    required this.stars,
    required this.coins,
    this.title,
    this.onMap = false,
    this.onHome,
    this.onShop,
    this.onParentZone,
    this.remainingMinutes,
  });

  final int stars;
  final int coins;
  final String? title;

  /// On the map the left button goes home to the profiles; elsewhere it goes
  /// back to the map.
  final bool onMap;
  final VoidCallback? onHome;
  final VoidCallback? onShop;
  final VoidCallback? onParentZone;

  /// Minutes of play left today, or null when there is no daily limit.
  final int? remainingMinutes;

  @override
  Widget build(BuildContext context) {
    final sm = Tw.isSm(context);
    final md = MediaQuery.sizeOf(context).width >= 768;

    return Container(
      padding: EdgeInsets.fromLTRB(
        sm ? 16 : 10,
        (sm ? 10 : 8) + MediaQuery.paddingOf(context).top,
        sm ? 16 : 10,
        sm ? 10 : 8,
      ),
      decoration: BoxDecoration(
        color: Tw.white.withValues(alpha: 0.95),
        border: const Border(bottom: BorderSide(color: Tw.slate100, width: 2)),
        boxShadow: Tw.shadowSm,
      ),
      child: Row(
        children: [
          // Left: Home or Map, and the coin pouch.
          _Pressable(
            onPressed: onHome,
            label: onMap ? 'Back to Profiles' : 'Back to Map',
            radius: sm ? Tw.rounded2xl : Tw.roundedXl,
            child: Container(
              padding: EdgeInsets.symmetric(horizontal: sm ? 12 : 10, vertical: 6),
              decoration: BoxDecoration(
                color: onMap ? Tw.indigo600 : Tw.emerald600,
                borderRadius: BorderRadius.circular(sm ? Tw.rounded2xl : Tw.roundedXl),
                boxShadow: Tw.shadowSm,
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(onMap ? '🏠' : '🗺️', style: TextStyle(fontSize: sm ? 16 : 14)),
                  if (sm) ...[
                    const SizedBox(width: 4),
                    Text(onMap ? 'Home' : 'Map', style: Tw.text(Tw.xs, color: Tw.white, weight: FontWeight.w900)),
                  ],
                ],
              ),
            ),
          ),
          SizedBox(width: sm ? 8 : 6),
          _Pressable(
            onPressed: onShop,
            label: 'Star Shop',
            radius: sm ? Tw.rounded2xl : Tw.roundedXl,
            child: Container(
              padding: EdgeInsets.symmetric(horizontal: sm ? 12 : 10, vertical: 6),
              decoration: BoxDecoration(
                gradient: const LinearGradient(colors: [Tw.amber400, Tw.amber500]),
                borderRadius: BorderRadius.circular(sm ? Tw.rounded2xl : Tw.roundedXl),
                border: Border.all(color: Tw.amber300),
                boxShadow: Tw.shadowSm,
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text('🪙', style: TextStyle(fontSize: sm ? 16 : 14)),
                  const SizedBox(width: 4),
                  Text(
                    _thousands(coins),
                    style: Tw.text(sm ? Tw.sm : Tw.xs, color: Tw.slate950, weight: FontWeight.w900),
                  ),
                  if (md) ...[
                    const SizedBox(width: 4),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                      decoration: BoxDecoration(
                        color: Tw.slate950.withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: Text('SHOP', style: Tw.text(10, color: Tw.slate900, weight: FontWeight.w900)),
                    ),
                  ],
                ],
              ),
            ),
          ),

          // Centre: title and the time left.
          Expanded(
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                if (title != null)
                  Flexible(
                    child: ConstrainedBox(
                      constraints: BoxConstraints(maxWidth: sm ? 320 : 120),
                      child: Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 4),
                        child: Text(
                          title!,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          textAlign: TextAlign.center,
                          style: Tw.display(md ? Tw.base : (sm ? Tw.sm : Tw.xs), color: Tw.slate800),
                        ),
                      ),
                    ),
                  ),
                if (remainingMinutes != null) ...[
                  const SizedBox(width: 8),
                  _TimePill(minutes: remainingMinutes!),
                ],
              ],
            ),
          ),

          // Right: stars and the parent cog.
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            decoration: BoxDecoration(
              color: Tw.white.withValues(alpha: 0.9),
              borderRadius: BorderRadius.circular(999),
              boxShadow: const [BoxShadow(color: Color(0x14000000), blurRadius: 8, offset: Offset(0, 2))],
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Text('⭐', style: TextStyle(fontSize: 20)),
                const SizedBox(width: 6),
                Text(
                  '$stars',
                  style: Tw.display(18, color: Tw.amber600).copyWith(
                    fontFeatures: const [FontFeature.tabularFigures()],
                  ),
                ),
              ],
            ),
          ),
          SizedBox(width: sm ? 8 : 6),
          _Pressable(
            onPressed: onParentZone,
            label: 'Parent Zone (PIN Protected)',
            radius: sm ? 999 : Tw.roundedXl,
            child: Container(
              width: sm ? 36 : 32,
              height: sm ? 36 : 32,
              alignment: Alignment.center,
              decoration: BoxDecoration(
                color: Tw.slate100,
                borderRadius: BorderRadius.circular(sm ? 999 : Tw.roundedXl),
                border: Border.all(color: Tw.slate300),
                boxShadow: Tw.shadowSm,
              ),
              child: Text('⚙️', style: TextStyle(fontSize: sm ? 16 : 14)),
            ),
          ),
        ],
      ),
    );
  }
}

class _TimePill extends StatelessWidget {
  const _TimePill({required this.minutes});

  final int minutes;

  @override
  Widget build(BuildContext context) {
    final sm = Tw.isSm(context);

    final (Color background, Color border, Color text) = switch (minutes) {
      <= 5 => (Tw.rose50, Tw.rose300, Tw.rose700),
      <= 10 => (Tw.amber50, Tw.amber300, Tw.amber900),
      _ => (Tw.emerald50, Tw.emerald300, Tw.emerald900),
    };

    final pill = Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: background,
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: border),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Text('⏳', style: TextStyle(fontSize: 12)),
          const SizedBox(width: 4),
          Text(
            minutes > 0 ? '${minutes}m Left' : 'Time Up 🎵',
            style: Tw.text(sm ? Tw.xs : 10, color: text, weight: FontWeight.w900),
          ),
        ],
      ),
    );

    return minutes <= 5 ? TwPulse(child: pill) : pill;
  }
}

class _Pressable extends StatelessWidget {
  const _Pressable({required this.child, required this.label, required this.radius, this.onPressed});

  final Widget child;
  final String label;
  final double radius;
  final VoidCallback? onPressed;

  @override
  Widget build(BuildContext context) {
    return KidFocusable(
      onPressed: onPressed,
      semanticLabel: label,
      borderRadius: BorderRadius.circular(radius),
      child: child,
    );
  }
}

String _thousands(int value) {
  final digits = value.toString();
  final buffer = StringBuffer();

  for (var i = 0; i < digits.length; i++) {
    if (i > 0 && (digits.length - i) % 3 == 0) buffer.write(',');
    buffer.write(digits[i]);
  }

  return buffer.toString();
}
