import 'package:flutter/material.dart';

import '../../../core/platform/form_factor.dart';
import '../../../design/components/kid_button.dart';
import '../../../design/tokens.dart';
import 'renderer_kit.dart';

/// Trace the letter or number.
///
/// Practice, not assessment: the website has always scored tracing as correct
/// for doing it, and so does the shared scorer. What matters is that the child
/// forms the shape, so the guide stays visible under their finger and there is
/// no wrong outcome to be told about.
///
/// A television has no touchscreen, so it gets Watch and Air-Trace instead: the
/// shape is shown, the child draws it in the air or on paper, and presses OK.
class TracingRenderer extends StatefulWidget {
  const TracingRenderer({super.key, required this.context_});

  final RendererContext context_;

  @override
  State<TracingRenderer> createState() => _TracingRendererState();
}

class _TracingRendererState extends State<TracingRenderer> {
  final List<List<Offset>> _strokes = [];
  List<Offset> _current = [];

  RendererContext get _ctx => widget.context_;

  /// The character to trace: the author's own metadata, or the first thing in
  /// the prompt that looks like a letter or digit.
  String get _character {
    final declared = (_ctx.question.metadata['character'] ?? _ctx.question.scoringConfig['character'])?.toString();

    if (declared != null && declared.trim().isNotEmpty) return declared.trim();

    final match = RegExp(r'[A-Za-z0-9]').firstMatch(_ctx.question.prompt ?? '');

    return match?.group(0) ?? '?';
  }

  void _clear() => setState(() {
        _strokes.clear();
        _current = [];
      });

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);
    final onTv = formFactor.isTv;
    final size = onTv ? 260.0 : 240.0;

    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        RendererPrompt(
          text: onTv ? 'Watch the shape, then draw it in the air' : 'Trace the shape with your finger',
        ),
        Container(
          width: size,
          height: size,
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: KidRadius.card,
            border: Border.all(color: KidColors.primaryLight, width: 3),
          ),
          child: ClipRRect(
            borderRadius: KidRadius.card,
            child: Stack(
              children: [
                Center(
                  child: Text(
                    _character,
                    style: TextStyle(
                      fontSize: size * 0.68,
                      fontFamily: KidFonts.display,
                      fontWeight: FontWeight.w800,
                      // A guide, not the answer: pale enough to draw over.
                      color: KidColors.primary.withValues(alpha: 0.18),
                    ),
                  ),
                ),
                if (!onTv)
                  Positioned.fill(
                    child: GestureDetector(
                      onPanStart: (d) => setState(() => _current = [d.localPosition]),
                      onPanUpdate: (d) => setState(() => _current = [..._current, d.localPosition]),
                      onPanEnd: (_) => setState(() {
                        if (_current.length > 1) _strokes.add(_current);
                        _current = [];
                      }),
                      child: CustomPaint(
                        painter: _StrokePainter(strokes: [..._strokes, if (_current.isNotEmpty) _current]),
                        size: Size(size, size),
                      ),
                    ),
                  ),
              ],
            ),
          ),
        ),
        SizedBox(height: KidSpacing.md * formFactor.density),
        if (!onTv && _strokes.isNotEmpty)
          Padding(
            padding: EdgeInsets.only(bottom: KidSpacing.sm * formFactor.density),
            child: TextButton.icon(
              onPressed: _clear,
              icon: const Icon(Icons.refresh_rounded),
              label: const Text('Start again'),
            ),
          ),
        KidButton(
          label: onTv ? 'I traced it!' : "I'm done!",
          tone: KidButtonTone.success,
          icon: Icons.check_rounded,
          autofocus: true,
          onPressed: _ctx.isAnswering
              ? () => _ctx.submit({
                    'mode': onTv ? 'watched' : 'traced',
                    'strokes': _strokes.length,
                  })
              : null,
        ),
        SizedBox(height: KidSpacing.xs * formFactor.density),
        Text(
          'Every try counts here',
          style: theme.textTheme.labelSmall?.copyWith(color: theme.colorScheme.onSurfaceVariant),
        ),
      ],
    );
  }
}

class _StrokePainter extends CustomPainter {
  const _StrokePainter({required this.strokes});

  final List<List<Offset>> strokes;

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = KidColors.primary
      ..strokeWidth = 12
      ..strokeCap = StrokeCap.round
      ..strokeJoin = StrokeJoin.round
      ..style = PaintingStyle.stroke;

    for (final stroke in strokes) {
      if (stroke.length < 2) continue;

      final path = Path()..moveTo(stroke.first.dx, stroke.first.dy);
      for (final point in stroke.skip(1)) {
        path.lineTo(point.dx, point.dy);
      }

      canvas.drawPath(path, paint);
    }
  }

  @override
  bool shouldRepaint(_StrokePainter oldDelegate) => true;
}
