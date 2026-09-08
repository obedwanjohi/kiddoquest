import 'dart:math' as math;

import 'package:flutter/material.dart';

import '../../core/platform/form_factor.dart';
import '../tokens.dart';
import 'focus_ring.dart';

/// How an answer card is currently being shown.
enum AnswerState {
  /// Waiting to be chosen.
  idle,

  /// The child chose this and it was right.
  correct,

  /// The child chose this and it was not right. Grey, never red.
  gentleWrong,

  /// Revealed by the app after three tries, so the child still sees the answer.
  revealed,

  /// Dimmed because the question has moved on.
  disabled,
}

/// One tappable answer.
///
/// The rule this component exists to enforce: a wrong answer is grey with a
/// short shake and a soft sound. Never red, never a cross, never the word
/// "wrong". Several of the website's own stylesheets break that rule; here it
/// is impossible to break because there is one component.
class AnswerCard extends StatefulWidget {
  const AnswerCard({
    super.key,
    required this.state,
    this.label,
    this.image,
    this.hasImage = false,
    this.emoji,
    this.onPressed,
    this.autofocus = false,
    this.accent = KidColors.primary,
    this.index,
  });

  final AnswerState state;
  final String? label;

  /// Already-resolved artwork, if this option has any. The card does not know
  /// how a content pack stores pictures; it just draws what it is handed.
  final Widget? image;
  final bool hasImage;
  final String? emoji;
  final VoidCallback? onPressed;
  final bool autofocus;
  final Color accent;

  /// Position in the grid. On a television this shows as a number key hint.
  final int? index;

  @override
  State<AnswerCard> createState() => _AnswerCardState();
}

class _AnswerCardState extends State<AnswerCard> with SingleTickerProviderStateMixin {
  late final AnimationController _shake = AnimationController(vsync: this, duration: KidMotion.shake);

  @override
  void didUpdateWidget(AnswerCard oldWidget) {
    super.didUpdateWidget(oldWidget);

    if (widget.state == AnswerState.gentleWrong && oldWidget.state != AnswerState.gentleWrong) {
      final reduceMotion = MediaQuery.maybeDisableAnimationsOf(context) ?? false;
      if (!reduceMotion) {
        _shake.forward(from: 0);
      }
    }
  }

  @override
  void dispose() {
    _shake.dispose();
    super.dispose();
  }

  ({Color fill, Color edge, Color ink}) get _colors => switch (widget.state) {
        AnswerState.correct => (fill: KidColors.successSoft, edge: KidColors.success, ink: const Color(0xFF14532D)),
        AnswerState.gentleWrong => (fill: const Color(0xFFF3F4F6), edge: KidColors.wrong, ink: KidColors.muted),
        AnswerState.revealed => (fill: KidColors.amberSoft, edge: KidColors.amber, ink: const Color(0xFF78350F)),
        AnswerState.disabled => (fill: const Color(0xFFF9FAFB), edge: KidColors.border, ink: KidColors.muted),
        AnswerState.idle => (fill: KidColors.surface, edge: widget.accent, ink: KidColors.ink),
      };

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final colors = _colors;
    final interactive = widget.state == AnswerState.idle && widget.onPressed != null;

    final body = Column(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        if (widget.emoji != null)
          Text(widget.emoji!, style: TextStyle(fontSize: 44 * formFactor.density)),
        if (widget.hasImage && widget.image != null)
          Flexible(
            child: Padding(
              padding: EdgeInsets.only(bottom: KidSpacing.sm * formFactor.density),
              child: widget.image!,
            ),
          ),
        if (widget.label != null && widget.label!.isNotEmpty)
          Text(
            widget.label!,
            textAlign: TextAlign.center,
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
            style: TextStyle(
              fontFamily: KidFonts.display,
              fontSize: KidTypeScale.answer * formFactor.density,
              fontWeight: FontWeight.w800,
              color: colors.ink,
            ),
          ),
      ],
    );

    return AnimatedBuilder(
      animation: _shake,
      builder: (context, child) {
        // Three quick swings, never further than eight pixels.
        final t = _shake.value;
        final offset = t == 0 ? 0.0 : math.sin(t * math.pi * 6) * 8 * (1 - t);
        return Transform.translate(offset: Offset(offset, 0), child: child);
      },
      child: KidFocusable(
        onPressed: interactive ? widget.onPressed : null,
        enabled: interactive,
        autofocus: widget.autofocus,
        borderRadius: KidRadius.card,
        semanticLabel: widget.label ?? widget.emoji,
        child: AnimatedContainer(
          // Named so tests can assert on this surface rather than the focus ring.
          key: const ValueKey('answer-card-surface'),
          duration: KidMotion.normal,
          curve: KidMotion.spring,
          constraints: BoxConstraints(
            minHeight: KidTouch.large * formFactor.density,
            minWidth: KidTouch.large * formFactor.density,
          ),
          padding: EdgeInsets.all(KidSpacing.md * formFactor.density),
          decoration: BoxDecoration(
            color: colors.fill,
            borderRadius: KidRadius.card,
            border: Border.all(color: colors.edge, width: 3),
            boxShadow: widget.state == AnswerState.idle ? KidShadows.edge(colors.edge.withValues(alpha: 0.55)) : const [],
          ),
          child: Stack(
            children: [
              Center(child: body),
              if (formFactor.isTv && widget.index != null)
                Positioned(
                  top: 0,
                  left: 0,
                  child: _NumberKeyHint(number: widget.index! + 1, color: colors.edge),
                ),
              if (widget.state == AnswerState.correct)
                const Positioned(top: 0, right: 0, child: Icon(Icons.check_circle, color: KidColors.success)),
              if (widget.state == AnswerState.revealed)
                const Positioned(top: 0, right: 0, child: Icon(Icons.lightbulb, color: KidColors.amber)),
            ],
          ),
        ),
      ),
    );
  }
}

/// On a television the number keys on the remote pick answers one to four.
class _NumberKeyHint extends StatelessWidget {
  const _NumberKeyHint({required this.number, required this.color});

  final int number;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 28,
      height: 28,
      alignment: Alignment.center,
      decoration: BoxDecoration(color: color.withValues(alpha: 0.15), borderRadius: KidRadius.pill),
      child: Text(
        '$number',
        style: TextStyle(fontFamily: KidFonts.body, fontWeight: FontWeight.w800, color: color, fontSize: 14),
      ),
    );
  }
}
