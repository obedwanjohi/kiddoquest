import 'dart:math' as math;

import 'package:flutter/material.dart';

/// Tailwind's stock animations, as the website uses them.
///
/// All of them stand still when the platform asks for reduced motion — a child
/// who gets dizzy should not have the map bouncing at them.

/// `animate-pulse`: opacity breathing between 1 and 0.5 every two seconds.
class TwPulse extends StatefulWidget {
  const TwPulse({super.key, required this.child});

  final Widget child;

  @override
  State<TwPulse> createState() => _TwPulseState();
}

class _TwPulseState extends State<TwPulse> with SingleTickerProviderStateMixin {
  late final AnimationController _controller =
      AnimationController(vsync: this, duration: const Duration(seconds: 2))..repeat();

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (MediaQuery.disableAnimationsOf(context)) return widget.child;

    return AnimatedBuilder(
      animation: _controller,
      builder: (context, child) {
        // 0% and 100% opaque, 50% at half: cubic-bezier(0.4, 0, 0.6, 1).
        final t = const Cubic(0.4, 0, 0.6, 1).transform(_controller.value);
        final opacity = 1 - 0.5 * math.sin(t * math.pi);

        return Opacity(opacity: opacity, child: child);
      },
      child: widget.child,
    );
  }
}

/// `animate-bounce`: up a quarter of its height and back, once a second.
class TwBounce extends StatefulWidget {
  const TwBounce({super.key, required this.child, this.distance});

  final Widget child;

  /// How far it rises. Defaults to a quarter of the child's own height, which
  /// is Tailwind's `translateY(-25%)`.
  final double? distance;

  @override
  State<TwBounce> createState() => _TwBounceState();
}

class _TwBounceState extends State<TwBounce> with SingleTickerProviderStateMixin {
  late final AnimationController _controller =
      AnimationController(vsync: this, duration: const Duration(seconds: 1))..repeat();

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (MediaQuery.disableAnimationsOf(context)) return widget.child;

    return AnimatedBuilder(
      animation: _controller,
      builder: (context, child) {
        final v = _controller.value;
        // Up with ease-out for the first half, down with ease-in for the second.
        final lift = v < 0.5
            ? const Cubic(0, 0, 0.2, 1).transform(v * 2)
            : 1 - const Cubic(0.8, 0, 1, 1).transform((v - 0.5) * 2);

        return FractionalTranslation(
          translation: widget.distance == null ? Offset(0, -0.25 * lift) : Offset.zero,
          child: widget.distance == null
              ? child
              : Transform.translate(offset: Offset(0, -widget.distance! * lift), child: child),
        );
      },
      child: widget.child,
    );
  }
}

/// `animate-ping`: a ring that grows and fades, over and over.
class TwPing extends StatefulWidget {
  const TwPing({super.key, required this.color, required this.size});

  final Color color;
  final double size;

  @override
  State<TwPing> createState() => _TwPingState();
}

class _TwPingState extends State<TwPing> with SingleTickerProviderStateMixin {
  late final AnimationController _controller =
      AnimationController(vsync: this, duration: const Duration(seconds: 1))..repeat();

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (MediaQuery.disableAnimationsOf(context)) return const SizedBox.shrink();

    return AnimatedBuilder(
      animation: _controller,
      builder: (context, _) {
        final t = const Cubic(0, 0, 0.2, 1).transform(_controller.value);

        return Transform.scale(
          scale: 1 + t,
          child: Opacity(
            opacity: 0.75 * (1 - t),
            child: Container(
              width: widget.size,
              height: widget.size,
              decoration: BoxDecoration(color: widget.color, shape: BoxShape.circle),
            ),
          ),
        );
      },
    );
  }
}

/// Scale gently between 1 and [to] and back — the map's glowing active node.
class TwBreathe extends StatefulWidget {
  const TwBreathe({super.key, required this.child, this.to = 1.06, this.period = const Duration(milliseconds: 1800)});

  final Widget child;
  final double to;
  final Duration period;

  @override
  State<TwBreathe> createState() => _TwBreatheState();
}

class _TwBreatheState extends State<TwBreathe> with SingleTickerProviderStateMixin {
  late final AnimationController _controller =
      AnimationController(vsync: this, duration: widget.period)..repeat(reverse: true);

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (MediaQuery.disableAnimationsOf(context)) return widget.child;

    return AnimatedBuilder(
      animation: _controller,
      builder: (context, child) => Transform.scale(
        scale: 1 + (widget.to - 1) * Curves.easeInOut.transform(_controller.value),
        child: child,
      ),
      child: widget.child,
    );
  }
}
