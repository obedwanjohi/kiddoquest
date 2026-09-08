import 'dart:async';

import 'package:flutter/material.dart';

import '../../../core/models/pack.dart';
import '../../../core/platform/form_factor.dart';
import '../../../design/components/focus_ring.dart';
import '../../../design/components/pack_image.dart';
import '../../../design/tokens.dart';
import 'renderer_kit.dart';

/// Find the pairs.
///
/// The website's engine had this logic but never a screen to put it on, so this
/// is the first time the type has been playable. Cards flip back after a moment
/// rather than instantly, because a young child needs time to see what was
/// there before it disappears.
class MemoryMatchRenderer extends StatefulWidget {
  const MemoryMatchRenderer({super.key, required this.context_});

  final RendererContext context_;

  @override
  State<MemoryMatchRenderer> createState() => _MemoryMatchRendererState();
}

class _MemoryMatchRendererState extends State<MemoryMatchRenderer> {
  final List<PackOption> _cards = [];
  final Set<int> _found = {};
  final List<PackOption> _flipped = [];

  int _flips = 0;
  bool _busy = false;
  Timer? _flipBack;

  RendererContext get _ctx => widget.context_;

  @override
  void initState() {
    super.initState();
    _cards.addAll(_ctx.options);
    // The order in the pack is the order the pairs were written in, which would
    // put every pair side by side. Shuffle so there is a game to play.
    _cards.shuffle();
  }

  @override
  void dispose() {
    _flipBack?.cancel();
    super.dispose();
  }

  String _keyOf(PackOption option) => (option.matchKey ?? '${option.id}').toLowerCase();

  int get _expectedPairs {
    final keys = _cards.map(_keyOf).where((k) => k.isNotEmpty).toSet();

    return keys.isNotEmpty ? keys.length : _cards.length ~/ 2;
  }

  void _flip(PackOption card) {
    if (_busy || _found.contains(card.id) || _flipped.any((c) => c.id == card.id)) return;

    setState(() {
      _flipped.add(card);
      _flips++;
    });

    if (_flipped.length < 2) return;

    final a = _flipped[0];
    final b = _flipped[1];

    if (_keyOf(a) == _keyOf(b) && a.id != b.id) {
      setState(() {
        _found.addAll([a.id, b.id]);
        _flipped.clear();
      });

      if (_found.length >= _expectedPairs * 2) {
        _ctx.submit({'pairs_found': _expectedPairs, 'flips': _flips});
      }

      return;
    }

    // Not a pair. Leave both showing long enough to be remembered.
    setState(() => _busy = true);
    _flipBack = Timer(const Duration(milliseconds: 1100), () {
      if (!mounted) return;
      setState(() {
        _flipped.clear();
        _busy = false;
      });
    });
  }

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final columns = _cards.length <= 4 ? 2 : (formFactor.isTv ? 4 : 3);

    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        const RendererPrompt(text: 'Find the matching pairs'),
        Flexible(
          child: GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: columns,
              mainAxisSpacing: KidSpacing.sm * formFactor.density,
              crossAxisSpacing: KidSpacing.sm * formFactor.density,
              childAspectRatio: 1,
            ),
            itemCount: _cards.length,
            itemBuilder: (context, index) {
              final card = _cards[index];
              final faceUp = _found.contains(card.id) || _flipped.any((c) => c.id == card.id);

              return _Card(
                option: card,
                faceUp: faceUp,
                matched: _found.contains(card.id),
                media: _ctx.media,
                autofocus: index == 0,
                onPressed: _ctx.isAnswering ? () => _flip(card) : null,
              );
            },
          ),
        ),
        SizedBox(height: KidSpacing.sm * formFactor.density),
        Text(
          '${_found.length ~/ 2} of $_expectedPairs pairs',
          style: Theme.of(context).textTheme.labelSmall,
        ),
      ],
    );
  }
}

class _Card extends StatelessWidget {
  const _Card({
    required this.option,
    required this.faceUp,
    required this.matched,
    required this.media,
    this.onPressed,
    this.autofocus = false,
  });

  final PackOption option;
  final bool faceUp;
  final bool matched;
  final Map<String, dynamic> media;
  final VoidCallback? onPressed;
  final bool autofocus;

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);

    return KidFocusable(
      onPressed: matched ? null : onPressed,
      enabled: !matched && onPressed != null,
      autofocus: autofocus,
      borderRadius: KidRadius.card,
      semanticLabel: faceUp ? (option.text ?? 'card') : 'face down card',
      child: AnimatedContainer(
        duration: KidMotion.fast,
        alignment: Alignment.center,
        padding: EdgeInsets.all(KidSpacing.sm * formFactor.density),
        decoration: BoxDecoration(
          color: matched
              ? KidColors.successSoft
              : (faceUp ? theme.colorScheme.surface : KidColors.primary),
          borderRadius: KidRadius.card,
          border: Border.all(
            color: matched ? KidColors.success : KidColors.primaryDark,
            width: 3,
          ),
        ),
        child: faceUp
            ? FittedBox(
                child: option.image != null
                    ? PackImage(mediaKey: option.image, media: media.cast(), height: 60)
                    : Text(
                        option.text ?? '',
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          fontFamily: KidFonts.display,
                          fontSize: KidTypeScale.answer * formFactor.density,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
              )
            : Text('?', style: TextStyle(fontSize: 32 * formFactor.density, color: Colors.white, fontWeight: FontWeight.w900)),
      ),
    );
  }
}
