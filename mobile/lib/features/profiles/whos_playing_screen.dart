import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/providers.dart';
import '../../core/models/child.dart';
import '../../core/platform/form_factor.dart';
import '../../design/components/focus_ring.dart';
import '../../design/components/kid_button.dart';
import '../../design/components/kid_scaffold.dart';
import '../../design/components/mascot_stage.dart';
import '../../design/tokens.dart';

/// "Who is playing?" — the first thing a child sees, and the only screen where
/// a grown-up hands the device over.
class WhosPlayingScreen extends ConsumerWidget {
  const WhosPlayingScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final session = ref.watch(sessionProvider);
    final formFactor = context.formFactor;
    final theme = Theme.of(context);

    if (session.loading) {
      return const KidScaffold(child: LeoLoading(message: 'Finding your explorers…'));
    }

    return KidScaffold(
      child: Column(
        children: [
          SizedBox(height: KidSpacing.lg * formFactor.density),
          const MascotStage(mood: MascotMood.encouraging),
          SizedBox(height: KidSpacing.sm * formFactor.density),
          Text('Who is playing?', style: theme.textTheme.displayMedium),
          SizedBox(height: KidSpacing.lg * formFactor.density),
          Expanded(
            child: session.children.isEmpty
                ? LeoMessage(
                    title: 'No explorers yet',
                    body: 'Add your first child and Leo will get their adventure ready.',
                    action: KidButton(
                      label: 'Add an explorer',
                      icon: Icons.add_rounded,
                      autofocus: true,
                      onPressed: () => context.go('/add-child'),
                    ),
                  )
                : GridView.builder(
                    padding: EdgeInsets.only(bottom: KidSpacing.xl * formFactor.density),
                    gridDelegate: SliverGridDelegateWithMaxCrossAxisExtent(
                      maxCrossAxisExtent: formFactor.isTv ? 280 : 200,
                      mainAxisSpacing: KidSpacing.md * formFactor.density,
                      crossAxisSpacing: KidSpacing.md * formFactor.density,
                      childAspectRatio: 0.78,
                    ),
                    itemCount: session.children.length + 1,
                    itemBuilder: (context, index) {
                      if (index == session.children.length) {
                        return _AddChildCard(onPressed: () => context.go('/add-child'));
                      }

                      final child = session.children[index];

                      return _ChildCard(
                        child: child,
                        autofocus: index == 0,
                        onPressed: () {
                          ref.read(sessionProvider.notifier).selectChild(child);
                          context.go('/map');
                        },
                      );
                    },
                  ),
          ),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              TextButton.icon(
                onPressed: () => context.go('/parent'),
                icon: const Text('🔐'),
                label: const Text('Parent zone'),
              ),
              SizedBox(width: KidSpacing.md * formFactor.density),
              TextButton.icon(
                onPressed: () => context.go('/downloads'),
                icon: const Icon(Icons.download_rounded),
                label: const Text('Downloads'),
              ),
            ],
          ),
          SizedBox(height: KidSpacing.md * formFactor.density),
        ],
      ),
    );
  }
}

class _ChildCard extends StatelessWidget {
  const _ChildCard({required this.child, required this.onPressed, this.autofocus = false});

  final Child child;
  final VoidCallback onPressed;
  final bool autofocus;

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);
    final palette = WorldPalette.resolve(slug: child.favoriteColor, index: child.id);

    return KidFocusable(
      onPressed: onPressed,
      autofocus: autofocus,
      semanticLabel: '${child.name}, ${child.totalStars} stars',
      child: Container(
        padding: EdgeInsets.all(KidSpacing.md * formFactor.density),
        decoration: BoxDecoration(
          color: theme.colorScheme.surface,
          borderRadius: KidRadius.card,
          border: Border.all(color: palette.light, width: 3),
          boxShadow: KidShadows.edge(palette.light),
        ),
        // The mascot gives up its space first, so a long name or a big star
        // count never pushes the card past its cell.
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          mainAxisSize: MainAxisSize.min,
          children: [
            Flexible(
              child: FittedBox(
                fit: BoxFit.contain,
                child: MascotStage(
                  character: child.avatar,
                  hat: child.equippedHat,
                  size: formFactor.isTv ? 110 : 76,
                ),
              ),
            ),
            SizedBox(height: KidSpacing.sm * formFactor.density),
            Text(
              child.name,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: theme.textTheme.titleLarge,
            ),
            SizedBox(height: KidSpacing.xs * formFactor.density),
            Container(
              padding: EdgeInsets.symmetric(
                horizontal: KidSpacing.sm * formFactor.density,
                vertical: 2,
              ),
              decoration: BoxDecoration(color: palette.light.withValues(alpha: 0.35), borderRadius: KidRadius.pill),
              child: Text(child.level ?? 'Play Group', style: theme.textTheme.labelSmall),
            ),
            SizedBox(height: KidSpacing.xs * formFactor.density),
            Text(
              '⭐ ${child.totalStars}',
              style: theme.textTheme.labelLarge,
            ),
          ],
        ),
      ),
    );
  }
}

class _AddChildCard extends StatelessWidget {
  const _AddChildCard({required this.onPressed});

  final VoidCallback onPressed;

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);

    return KidFocusable(
      onPressed: onPressed,
      semanticLabel: 'Add an explorer',
      child: DottedBorderBox(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.add_circle_outline_rounded, size: 44 * formFactor.density, color: KidColors.primary),
            SizedBox(height: KidSpacing.sm * formFactor.density),
            Text('Add an explorer', textAlign: TextAlign.center, style: theme.textTheme.titleMedium),
          ],
        ),
      ),
    );
  }
}

/// A dashed outline that reads as "there could be something here".
class DottedBorderBox extends StatelessWidget {
  const DottedBorderBox({super.key, required this.child});

  final Widget child;

  @override
  Widget build(BuildContext context) {
    return CustomPaint(
      painter: const _DashedBorderPainter(color: KidColors.primaryLight),
      child: Padding(padding: const EdgeInsets.all(KidSpacing.md), child: child),
    );
  }
}

class _DashedBorderPainter extends CustomPainter {
  const _DashedBorderPainter({required this.color});

  final Color color;

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = color
      ..style = PaintingStyle.stroke
      ..strokeWidth = 3;

    final rect = RRect.fromRectAndRadius(
      Offset.zero & size,
      const Radius.circular(KidRadius.xl),
    );

    final path = Path()..addRRect(rect);
    const dash = 12.0;
    const gap = 8.0;

    for (final metric in path.computeMetrics()) {
      var distance = 0.0;
      while (distance < metric.length) {
        final next = distance + dash;
        canvas.drawPath(metric.extractPath(distance, next.clamp(0, metric.length)), paint);
        distance = next + gap;
      }
    }
  }

  @override
  bool shouldRepaint(_DashedBorderPainter oldDelegate) => oldDelegate.color != color;
}
