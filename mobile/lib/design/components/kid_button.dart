import 'package:flutter/material.dart';

import '../../core/platform/form_factor.dart';
import '../tokens.dart';
import 'focus_ring.dart';

enum KidButtonTone { primary, success, amber, neutral, danger }

/// The chunky, pressable button the whole product is built from.
///
/// The 3-D bottom edge is the affordance: it sits proud of the surface and
/// squashes down by three pixels when pressed, which reads as "pressed" to a
/// child who cannot yet read the label.
class KidButton extends StatefulWidget {
  const KidButton({
    super.key,
    required this.label,
    this.onPressed,
    this.icon,
    this.tone = KidButtonTone.primary,
    this.expand = false,
    this.autofocus = false,
    this.busy = false,
    this.size = KidButtonSize.regular,
  });

  final String label;
  final VoidCallback? onPressed;
  final IconData? icon;
  final KidButtonTone tone;
  final bool expand;
  final bool autofocus;
  final bool busy;
  final KidButtonSize size;

  @override
  State<KidButton> createState() => _KidButtonState();
}

enum KidButtonSize { small, regular, large }

class _KidButtonState extends State<KidButton> {
  bool _pressed = false;

  ({Color fill, Color edge, Color ink}) _colors(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;

    return switch (widget.tone) {
      KidButtonTone.primary => (fill: KidColors.primary, edge: KidColors.primaryDark, ink: Colors.white),
      KidButtonTone.success => (fill: KidColors.success, edge: const Color(0xFF15803D), ink: Colors.white),
      KidButtonTone.amber => (fill: KidColors.amber, edge: const Color(0xFFB45309), ink: const Color(0xFF3B2A06)),
      KidButtonTone.danger => (fill: KidColors.danger, edge: const Color(0xFFB91C1C), ink: Colors.white),
      KidButtonTone.neutral => (fill: scheme.surface, edge: scheme.outline, ink: scheme.onSurface),
    };
  }

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final colors = _colors(context);
    final enabled = widget.onPressed != null && !widget.busy;

    final height = switch (widget.size) {
          KidButtonSize.small => KidTouch.min,
          KidButtonSize.regular => KidTouch.recommended,
          KidButtonSize.large => KidTouch.large,
        } *
        formFactor.density;

    final textStyle = Theme.of(context).textTheme.titleMedium!.copyWith(
          color: enabled ? colors.ink : colors.ink.withValues(alpha: 0.6),
          fontFamily: KidFonts.display,
          fontWeight: FontWeight.w800,
        );

    final content = Row(
      mainAxisSize: widget.expand ? MainAxisSize.max : MainAxisSize.min,
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        if (widget.busy) ...[
          SizedBox(
            width: 18 * formFactor.density,
            height: 18 * formFactor.density,
            child: CircularProgressIndicator(strokeWidth: 3, color: colors.ink),
          ),
          SizedBox(width: KidSpacing.sm * formFactor.density),
        ] else if (widget.icon != null) ...[
          Icon(widget.icon, color: colors.ink, size: 22 * formFactor.density),
          SizedBox(width: KidSpacing.sm * formFactor.density),
        ],
        Flexible(
          child: Text(
            widget.label,
            style: textStyle,
            textAlign: TextAlign.center,
            overflow: TextOverflow.ellipsis,
          ),
        ),
      ],
    );

    return KidFocusable(
      onPressed: enabled ? widget.onPressed : null,
      autofocus: widget.autofocus,
      enabled: enabled,
      borderRadius: KidRadius.button,
      semanticLabel: widget.label,
      child: Listener(
        onPointerDown: enabled ? (_) => setState(() => _pressed = true) : null,
        onPointerUp: enabled ? (_) => setState(() => _pressed = false) : null,
        onPointerCancel: enabled ? (_) => setState(() => _pressed = false) : null,
        child: AnimatedContainer(
          duration: KidMotion.fast,
          curve: KidMotion.ease,
          height: height,
          width: widget.expand ? double.infinity : null,
          margin: EdgeInsets.only(bottom: _pressed ? 0 : 4),
          padding: EdgeInsets.symmetric(horizontal: KidSpacing.lg * formFactor.density),
          alignment: Alignment.center,
          decoration: BoxDecoration(
            color: enabled ? colors.fill : colors.fill.withValues(alpha: 0.45),
            borderRadius: KidRadius.button,
            border: widget.tone == KidButtonTone.neutral ? Border.all(color: colors.edge, width: 2) : null,
            boxShadow: enabled ? KidShadows.edge(colors.edge, pressed: _pressed) : const [],
          ),
          child: content,
        ),
      ),
    );
  }
}
