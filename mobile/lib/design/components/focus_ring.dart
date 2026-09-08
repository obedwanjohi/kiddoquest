import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../core/platform/form_factor.dart';
import '../tokens.dart';

/// Makes anything selectable with a finger or a remote control.
///
/// On a television the focus ring is the interface: it is the only thing that
/// tells a child which card the OK button will press. So it is a brand element,
/// not a default outline — an amber glow, a small lift, and a spring.
class KidFocusable extends StatefulWidget {
  const KidFocusable({
    super.key,
    required this.child,
    this.onPressed,
    this.onLongPress,
    this.autofocus = false,
    this.focusNode,
    this.borderRadius = KidRadius.card,
    this.ringColor = KidColors.amber,
    this.scaleOnFocus = 1.06,
    this.enabled = true,
    this.semanticLabel,
  });

  final Widget child;
  final VoidCallback? onPressed;
  final VoidCallback? onLongPress;
  final bool autofocus;
  final FocusNode? focusNode;
  final BorderRadius borderRadius;
  final Color ringColor;
  final double scaleOnFocus;
  final bool enabled;
  final String? semanticLabel;

  @override
  State<KidFocusable> createState() => _KidFocusableState();
}

class _KidFocusableState extends State<KidFocusable> {
  bool _focused = false;
  bool _pressed = false;

  void _activate() {
    if (!widget.enabled || widget.onPressed == null) return;
    HapticFeedback.selectionClick();
    widget.onPressed!();
  }

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final ringWidth = formFactor.isTv ? 4.0 : 3.0;
    final showRing = _focused && widget.enabled;

    return Semantics(
      label: widget.semanticLabel,
      button: widget.onPressed != null,
      child: FocusableActionDetector(
        focusNode: widget.focusNode,
        autofocus: widget.autofocus,
        enabled: widget.enabled && widget.onPressed != null,
        onShowFocusHighlight: (value) => setState(() => _focused = value),
        actions: <Type, Action<Intent>>{
          ActivateIntent: CallbackAction<ActivateIntent>(onInvoke: (_) {
            _activate();
            return null;
          }),
          ButtonActivateIntent: CallbackAction<ButtonActivateIntent>(onInvoke: (_) {
            _activate();
            return null;
          }),
        },
        child: GestureDetector(
          behavior: HitTestBehavior.opaque,
          onTapDown: widget.enabled ? (_) => setState(() => _pressed = true) : null,
          onTapUp: widget.enabled ? (_) => setState(() => _pressed = false) : null,
          onTapCancel: widget.enabled ? () => setState(() => _pressed = false) : null,
          onTap: widget.enabled ? _activate : null,
          onLongPress: widget.onLongPress,
          child: AnimatedScale(
            scale: _pressed ? 0.96 : (showRing ? widget.scaleOnFocus : 1.0),
            duration: KidMotion.fast,
            curve: KidMotion.spring,
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 120),
              curve: KidMotion.spring,
              decoration: BoxDecoration(
                borderRadius: widget.borderRadius,
                border: Border.all(
                  color: showRing ? widget.ringColor : Colors.transparent,
                  width: ringWidth,
                ),
                boxShadow: showRing
                    ? [BoxShadow(color: widget.ringColor.withValues(alpha: 0.45), blurRadius: 18, spreadRadius: 2)]
                    : const [],
              ),
              child: widget.child,
            ),
          ),
        ),
      ),
    );
  }
}

/// Moves focus to the nearest widget in the pressed direction rather than in
/// tree order, which is what a person expects from a grid of answer cards.
class NearestFocusTraversal extends StatelessWidget {
  const NearestFocusTraversal({super.key, required this.child});

  final Widget child;

  @override
  Widget build(BuildContext context) {
    return FocusTraversalGroup(
      policy: ReadingOrderTraversalPolicy(),
      child: child,
    );
  }
}
