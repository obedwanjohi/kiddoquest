import 'package:flutter/material.dart';

import '../../../core/platform/form_factor.dart';
import '../../../design/components/focus_ring.dart';
import '../../../design/components/pack_image.dart';
import '../../../design/tokens.dart';
import 'renderer_kit.dart';

/// Find the hidden things in a picture.
///
/// The admin tool has been able to place hotspots for a long time; there has
/// never been a screen that reads them. Coordinates are percentages of the
/// picture, so the same hotspot lands correctly on a phone and a television.
///
/// On a television, tapping a picture is not possible, so each hotspot is also
/// a focusable target the D-pad can reach.
class SpotFindRenderer extends StatefulWidget {
  const SpotFindRenderer({super.key, required this.context_});

  final RendererContext context_;

  @override
  State<SpotFindRenderer> createState() => _SpotFindRendererState();
}

class _SpotFindRendererState extends State<SpotFindRenderer> {
  final List<Map<String, double>> _hits = [];
  int _misses = 0;

  RendererContext get _ctx => widget.context_;

  List<({double x, double y})> get _hotspots {
    final raw = _ctx.question.metadata['hotspots'];
    if (raw is! List) return const [];

    return raw
        .whereType<Map>()
        .map((spot) => (
              x: (spot['x'] as num?)?.toDouble() ?? -1,
              y: (spot['y'] as num?)?.toDouble() ?? -1,
            ))
        .where((spot) => spot.x >= 0 && spot.y >= 0)
        .toList();
  }

  double get _radius =>
      ((_ctx.question.metadata['hotspot_radius'] ?? _ctx.question.scoringConfig['hotspot_radius'] ?? 12) as num)
          .toDouble();

  bool _isFound(({double x, double y}) spot) {
    for (final hit in _hits) {
      final dx = (hit['x'] ?? 999) - spot.x;
      final dy = (hit['y'] ?? 999) - spot.y;
      if ((dx * dx + dy * dy) <= _radius * _radius) return true;
    }

    return false;
  }

  void _tapAt(double xPercent, double yPercent) {
    if (!_ctx.isAnswering) return;

    final spots = _hotspots;
    final hit = spots.any((spot) {
      final dx = xPercent - spot.x;
      final dy = yPercent - spot.y;
      return (dx * dx + dy * dy) <= _radius * _radius;
    });

    setState(() {
      if (hit) {
        _hits.add({'x': xPercent, 'y': yPercent});
      } else {
        // A miss costs nothing but is worth recording: a picture nobody can
        // find anything in is a content problem, not a child problem.
        _misses++;
      }
    });

    if (spots.isNotEmpty && spots.every(_isFound)) {
      _ctx.submit({'hits': _hits, 'misses': _misses});
    }
  }

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final spots = _hotspots;

    if (spots.isEmpty) {
      return const Center(child: Text('This picture has nothing hidden in it yet.'));
    }

    final found = spots.where(_isFound).length;

    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        RendererPrompt(text: 'Find them all: $found of ${spots.length}'),
        Flexible(
          child: LayoutBuilder(
            builder: (context, constraints) {
              final width = constraints.maxWidth;
              final height = constraints.maxHeight.isFinite ? constraints.maxHeight : width * 0.7;

              return GestureDetector(
                onTapDown: (details) => _tapAt(
                  (details.localPosition.dx / width) * 100,
                  (details.localPosition.dy / height) * 100,
                ),
                child: SizedBox(
                  width: width,
                  height: height,
                  child: Stack(
                    children: [
                      Positioned.fill(
                        child: PackImage(
                          mediaKey: _ctx.question.image,
                          media: _ctx.media,
                          fit: BoxFit.contain,
                        ),
                      ),

                      // One focusable target per hotspot, so a remote control
                      // can reach what a finger would tap.
                      for (var i = 0; i < spots.length; i++)
                        Positioned(
                          left: (spots[i].x / 100) * width - 24,
                          top: (spots[i].y / 100) * height - 24,
                          child: _Target(
                            found: _isFound(spots[i]),
                            autofocus: formFactor.isTv && i == 0,
                            onPressed: () => _tapAt(spots[i].x, spots[i].y),
                          ),
                        ),
                    ],
                  ),
                ),
              );
            },
          ),
        ),
      ],
    );
  }
}

class _Target extends StatelessWidget {
  const _Target({required this.found, required this.onPressed, this.autofocus = false});

  final bool found;
  final VoidCallback onPressed;
  final bool autofocus;

  @override
  Widget build(BuildContext context) {
    final onTv = context.formFactor.isTv;

    return KidFocusable(
      onPressed: found ? null : onPressed,
      enabled: !found,
      autofocus: autofocus,
      borderRadius: KidRadius.pill,
      semanticLabel: found ? 'found' : 'hidden thing',
      child: AnimatedContainer(
        duration: KidMotion.normal,
        width: 48,
        height: 48,
        decoration: BoxDecoration(
          shape: BoxShape.circle,
          // Found spots are marked; unfound ones stay invisible on touch, and
          // faintly outlined on a television so the D-pad has something to aim at.
          color: found ? KidColors.success.withValues(alpha: 0.35) : Colors.transparent,
          border: Border.all(
            color: found
                ? KidColors.success
                : (onTv ? Colors.white.withValues(alpha: 0.35) : Colors.transparent),
            width: 2,
          ),
        ),
        child: found ? const Icon(Icons.check, color: Colors.white) : null,
      ),
    );
  }
}
