import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/providers.dart';
import '../../core/models/child.dart';
import '../../core/platform/form_factor.dart';
import '../../design/components/focus_ring.dart';
import '../../design/site/site_scaffold.dart';
import '../../design/site/tw.dart';
import '../../design/site/tw_motion.dart';

/// "Who's Playing?" — the website's profile picker, drawn the same way.
///
/// Mirrors `resources/views/kids/profiles.blade.php`: the lilac, cream and pink
/// sky, the floating cloud and star, Sign Out and Parents in the header, one
/// white card per child with their level, buddy, stars and a Play arrow, and a
/// dashed card for adding another.
class WhosPlayingScreen extends ConsumerWidget {
  const WhosPlayingScreen({super.key});

  /// `bg-gradient-to-b from-[#DDD6FE] via-[#FEF3C7] to-[#FCE7F3]`.
  static const BoxDecoration background = BoxDecoration(
    gradient: LinearGradient(
      begin: Alignment.topCenter,
      end: Alignment.bottomCenter,
      colors: [Tw.violet200, Tw.amber100, Tw.pink100],
    ),
  );

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final session = ref.watch(sessionProvider);
    final sm = Tw.isSm(context);

    return SiteScaffold(
      decoration: background,
      child: Stack(
        children: [
          // Decorative background floaters.
          const Positioned(top: 32, left: 16, child: _Floater('☁️', size: 30, opacity: 0.3, pulse: true)),
          const Positioned(top: 40, right: 24, child: _Floater('⭐', size: 30, opacity: 0.3, pulse: true)),
          const Positioned(bottom: 40, left: 24, child: _Floater('🎈', size: 24, opacity: 0.2)),
          const Positioned(bottom: 48, right: 24, child: _Floater('✨', size: 24, opacity: 0.2)),

          Padding(
            padding: EdgeInsets.all(sm ? 20 : 12),
            child: Column(
              children: [
                _Header(
                  onSignOut: () async {
                    await ref.read(sessionProvider.notifier).signOut();
                    if (context.mounted) context.go('/sign-in');
                  },
                  onParents: () => context.go('/parent'),
                ),
                Expanded(
                  child: session.loading
                      ? const _Loading()
                      : Center(
                          child: SingleChildScrollView(
                            padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 16),
                            child: ConstrainedBox(
                              constraints: const BoxConstraints(maxWidth: 512),
                              child: session.children.isEmpty
                                  ? _EmptyState(onAdd: () => context.go('/add-child'))
                                  : _ProfileGrid(
                                      children: session.children,
                                      onPlay: (child) {
                                        ref.read(sessionProvider.notifier).selectChild(child);
                                        context.go('/map');
                                      },
                                      onAdd: () => context.go('/add-child'),
                                    ),
                            ),
                          ),
                        ),
                ),
                Padding(
                  padding: const EdgeInsets.only(bottom: 4),
                  child: Text(
                    '✨ Tap a picture to jump into your CBC adventure!',
                    textAlign: TextAlign.center,
                    style: Tw.text(sm ? 11 : 10, color: Tw.slate600.withValues(alpha: 0.8)),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _Header extends StatelessWidget {
  const _Header({required this.onSignOut, required this.onParents});

  final VoidCallback onSignOut;
  final VoidCallback onParents;

  @override
  Widget build(BuildContext context) {
    final sm = Tw.isSm(context);

    return ConstrainedBox(
      constraints: const BoxConstraints(maxWidth: 576),
      child: Padding(
        padding: const EdgeInsets.only(top: 4),
        child: Row(
          children: [
            _HeaderButton(
              emoji: '🚪',
              label: 'Sign Out',
              color: Tw.rose700,
              border: Tw.rose200,
              onPressed: onSignOut,
            ),
            Expanded(
              child: Center(
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                  decoration: BoxDecoration(
                    color: Tw.white.withValues(alpha: 0.9),
                    borderRadius: BorderRadius.circular(999),
                    border: Border.all(color: Tw.purple100),
                    boxShadow: Tw.shadowSm,
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Text('🦁', style: TextStyle(fontSize: 16)),
                      const SizedBox(width: 6),
                      Flexible(
                        child: Text(
                          "Who's Playing?",
                          overflow: TextOverflow.ellipsis,
                          style: Tw.display(sm ? Tw.sm : Tw.xs, color: Tw.slate900),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
            _HeaderButton(
              emoji: '🔐',
              label: sm ? 'Parents' : null,
              color: Tw.purple900,
              border: Tw.purple200,
              onPressed: onParents,
            ),
          ],
        ),
      ),
    );
  }
}

class _HeaderButton extends StatelessWidget {
  const _HeaderButton({
    required this.emoji,
    required this.color,
    required this.border,
    required this.onPressed,
    this.label,
  });

  final String emoji;
  final String? label;
  final Color color;
  final Color border;
  final VoidCallback onPressed;

  @override
  Widget build(BuildContext context) {
    final sm = Tw.isSm(context);

    return KidFocusable(
      onPressed: onPressed,
      borderRadius: BorderRadius.circular(Tw.roundedXl),
      semanticLabel: label ?? 'Parents',
      child: Container(
        padding: EdgeInsets.symmetric(horizontal: label == null ? 12 : (sm ? 12 : 10), vertical: 6),
        decoration: BoxDecoration(
          color: Tw.white.withValues(alpha: 0.9),
          borderRadius: BorderRadius.circular(Tw.roundedXl),
          border: Border.all(color: border),
          boxShadow: Tw.shadowSm,
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(emoji, style: const TextStyle(fontSize: 14)),
            if (label != null) ...[
              const SizedBox(width: 4),
              Text(label!, style: Tw.text(Tw.xs, color: color, weight: FontWeight.w900)),
            ],
          ],
        ),
      ),
    );
  }
}

class _ProfileGrid extends StatelessWidget {
  const _ProfileGrid({required this.children, required this.onPlay, required this.onAdd});

  final List<Child> children;
  final ValueChanged<Child> onPlay;
  final VoidCallback onAdd;

  @override
  Widget build(BuildContext context) {
    final sm = Tw.isSm(context);
    final columns = sm ? 3 : 2;
    final gap = sm ? 16.0 : 12.0;

    // A remote needs somewhere to start; a finger or a mouse does not, and a
    // focus ring on a phone is a ring the website does not have.
    final tv = context.formFactor.isTv;

    final cards = <Widget>[
      for (var i = 0; i < children.length; i++)
        _ProfileCard(child: children[i], autofocus: i == 0 && tv, onPressed: () => onPlay(children[i])),
      _AddExplorerCard(onPressed: onAdd),
    ];

    return LayoutBuilder(
      builder: (context, constraints) {
        final width = (constraints.maxWidth - gap * (columns - 1)) / columns;

        return Wrap(
          spacing: gap,
          runSpacing: gap,
          children: [
            for (final card in cards) SizedBox(width: width, child: card),
          ],
        );
      },
    );
  }
}

class _ProfileCard extends StatelessWidget {
  const _ProfileCard({required this.child, required this.onPressed, this.autofocus = false});

  final Child child;
  final VoidCallback onPressed;
  final bool autofocus;

  @override
  Widget build(BuildContext context) {
    final sm = Tw.isSm(context);
    final radius = BorderRadius.circular(sm ? Tw.rounded3xl : Tw.rounded2xl);
    final avatarSize = sm ? 56.0 : 48.0;
    final hat = child.equippedHatEmoji;

    return KidFocusable(
      onPressed: onPressed,
      autofocus: autofocus,
      borderRadius: radius,
      semanticLabel: '${child.name}, ${child.totalStars} stars',
      child: Container(
        constraints: BoxConstraints(minHeight: sm ? 165 : 145),
        padding: EdgeInsets.all(sm ? 16 : 12),
        decoration: BoxDecoration(
          color: Tw.white.withValues(alpha: 0.95),
          borderRadius: radius,
          border: Border.all(color: Tw.white, width: 2),
          boxShadow: Tw.shadowLg,
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            // Level and NEW badge.
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                // Shrinks rather than overflowing a narrow card: "PLAY GROUP"
                // beside a NEW badge is wider than a small phone's column.
                Flexible(
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                    decoration: BoxDecoration(color: Tw.purple100, borderRadius: BorderRadius.circular(999)),
                    child: Text(
                      (child.level ?? 'PP1').toUpperCase(),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: Tw.label(sm ? 9 : 8, color: Tw.purple800),
                    ),
                  ),
                ),
                if (!child.hasPlayed)
                  TwPulse(
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                      decoration: BoxDecoration(
                        color: Tw.rose50,
                        borderRadius: BorderRadius.circular(999),
                        border: Border.all(color: Tw.rose200),
                      ),
                      child: Text('NEW ✨', style: Tw.text(8, color: Tw.rose600, weight: FontWeight.w900)),
                    ),
                  ),
              ],
            ),
            const SizedBox(height: 4),

            // Buddy, with the hat from the shop on top.
            Stack(
              clipBehavior: Clip.none,
              alignment: Alignment.topCenter,
              children: [
                Container(
                  width: avatarSize,
                  height: avatarSize,
                  alignment: Alignment.center,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    gradient: const LinearGradient(
                      begin: Alignment.bottomLeft,
                      end: Alignment.topRight,
                      colors: [Tw.amber100, Tw.purple100],
                    ),
                    border: Border.all(color: Tw.amber200),
                  ),
                  child: Text(child.avatarEmoji, style: TextStyle(fontSize: sm ? 30 : 24)),
                ),
                if (hat != null)
                  Positioned(
                    top: -10,
                    child: TwBounce(child: Text(hat, style: const TextStyle(fontSize: 14))),
                  ),
              ],
            ),
            const SizedBox(height: 2),

            // Name and buddy.
            Text(
              child.name,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              textAlign: TextAlign.center,
              style: Tw.display(sm ? Tw.sm : Tw.xs, color: Tw.slate900, height: 1.25),
            ),
            Text(
              child.avatarName,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              textAlign: TextAlign.center,
              style: Tw.text(sm ? 10 : 9, color: Tw.slate500),
            ),

            // Stars and Play.
            Container(
              margin: const EdgeInsets.only(top: 4),
              padding: const EdgeInsets.only(top: 4),
              decoration: const BoxDecoration(border: Border(top: BorderSide(color: Tw.slate100))),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  // A four-figure star count must scale down, not push Play
                  // off the edge of the card.
                  Flexible(
                    child: FittedBox(
                      fit: BoxFit.scaleDown,
                      alignment: Alignment.centerLeft,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(color: Tw.amber50, borderRadius: BorderRadius.circular(4)),
                        child: Text(
                          '⭐ ${_thousands(child.totalStars)}',
                          style: Tw.text(10, color: Tw.amber900, weight: FontWeight.w900),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 4),
                  Text('Play ➔', style: Tw.text(9, color: Tw.purple700, weight: FontWeight.w900)),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _AddExplorerCard extends StatelessWidget {
  const _AddExplorerCard({required this.onPressed});

  final VoidCallback onPressed;

  @override
  Widget build(BuildContext context) {
    final sm = Tw.isSm(context);
    final radius = sm ? Tw.rounded3xl : Tw.rounded2xl;
    final circle = sm ? 48.0 : 40.0;

    return KidFocusable(
      onPressed: onPressed,
      borderRadius: BorderRadius.circular(radius),
      semanticLabel: 'Add Explorer',
      child: CustomPaint(
        painter: _DashedRRect(color: Tw.purple300, radius: radius),
        child: Container(
          constraints: BoxConstraints(minHeight: sm ? 165 : 145),
          padding: EdgeInsets.all(sm ? 16 : 12),
          decoration: BoxDecoration(
            color: Tw.white.withValues(alpha: 0.6),
            borderRadius: BorderRadius.circular(radius),
          ),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                width: circle,
                height: circle,
                alignment: Alignment.center,
                decoration: const BoxDecoration(color: Tw.purple100, shape: BoxShape.circle, boxShadow: Tw.shadowSm),
                child: Text('➕', style: TextStyle(fontSize: sm ? 24 : 20)),
              ),
              const SizedBox(height: 4),
              Text('Add Explorer', style: Tw.display(sm ? Tw.xs : 11, color: Tw.slate800)),
              Text('New Child Profile', style: Tw.text(9, color: Tw.slate500)),
            ],
          ),
        ),
      ),
    );
  }
}

class _EmptyState extends StatelessWidget {
  const _EmptyState({required this.onAdd});

  final VoidCallback onAdd;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Container(
        constraints: const BoxConstraints(maxWidth: 384),
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: Tw.white.withValues(alpha: 0.95),
          borderRadius: BorderRadius.circular(Tw.rounded3xl),
          border: Border.all(color: Tw.white, width: 2),
          boxShadow: Tw.shadowXl,
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const TwBounce(child: Text('🧒', style: TextStyle(fontSize: 48))),
            const SizedBox(height: 8),
            Text('No Adventurers Yet!', style: Tw.display(Tw.lg)),
            const SizedBox(height: 4),
            Text(
              'Add your child to unlock personalized games, stories, and star rewards!',
              textAlign: TextAlign.center,
              style: Tw.text(Tw.xs, color: Tw.slate600),
            ),
            const SizedBox(height: 16),
            KidFocusable(
              onPressed: onAdd,
              autofocus: context.formFactor.isTv,
              borderRadius: BorderRadius.circular(Tw.rounded2xl),
              semanticLabel: 'Add Your First Child',
              child: Container(
                width: double.infinity,
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(Tw.rounded2xl),
                  gradient: const LinearGradient(colors: [Tw.purple600, Tw.pink600, Tw.amber500]),
                  boxShadow: Tw.shadowMd,
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text('✨ Add Your First Child', style: Tw.display(Tw.xs, color: Tw.white)),
                    const SizedBox(width: 8),
                    Text('→', style: Tw.display(Tw.xs, color: Tw.white)),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _Loading extends StatelessWidget {
  const _Loading();

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const TwBounce(child: Text('🦁', style: TextStyle(fontSize: 48))),
          const SizedBox(height: 12),
          Text('Finding your explorers…', style: Tw.display(Tw.base, color: Tw.slate800)),
        ],
      ),
    );
  }
}

class _Floater extends StatelessWidget {
  const _Floater(this.emoji, {required this.size, required this.opacity, this.pulse = false});

  final String emoji;
  final double size;
  final double opacity;
  final bool pulse;

  @override
  Widget build(BuildContext context) {
    final glyph = IgnorePointer(
      child: Opacity(opacity: opacity, child: Text(emoji, style: TextStyle(fontSize: size))),
    );

    return pulse ? TwPulse(child: glyph) : glyph;
  }
}

/// `border-2 border-dashed`.
class _DashedRRect extends CustomPainter {
  const _DashedRRect({required this.color, required this.radius});

  final Color color;
  final double radius;

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = color
      ..style = PaintingStyle.stroke
      ..strokeWidth = 2;

    final path = Path()
      ..addRRect(RRect.fromRectAndRadius(
        (Offset.zero & size).deflate(1),
        Radius.circular(radius),
      ));

    for (final metric in path.computeMetrics()) {
      var distance = 0.0;
      while (distance < metric.length) {
        canvas.drawPath(metric.extractPath(distance, (distance + 6).clamp(0, metric.length)), paint);
        distance += 10;
      }
    }
  }

  @override
  bool shouldRepaint(_DashedRRect oldDelegate) => oldDelegate.color != color || oldDelegate.radius != radius;
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
