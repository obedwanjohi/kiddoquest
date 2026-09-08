import 'package:flutter/material.dart';

import '../../../core/models/pack.dart';
import '../../../core/platform/form_factor.dart';
import '../../../design/tokens.dart';
import 'renderer_kit.dart';

/// Match the pairs.
///
/// Two columns: tap something on the left, then its partner on the right. That
/// is two taps per pair, which is the limit the interaction guidelines set for
/// a three-year-old, and it works identically with a finger or a remote.
class MatchingRenderer extends StatefulWidget {
  const MatchingRenderer({super.key, required this.context_});

  final RendererContext context_;

  @override
  State<MatchingRenderer> createState() => _MatchingRendererState();
}

class _MatchingRendererState extends State<MatchingRenderer> {
  int? _selectedLeft;
  final List<List<int>> _pairs = [];
  final Set<int> _matched = {};

  RendererContext get _ctx => widget.context_;

  void _tapLeft(PackOption option) {
    if (_matched.contains(option.id)) return;
    setState(() => _selectedLeft = _selectedLeft == option.id ? null : option.id);
  }

  void _tapRight(PackOption option, int expectedPairs) {
    final left = _selectedLeft;
    if (left == null || _matched.contains(option.id)) return;

    setState(() {
      _pairs.add([left, option.id]);
      _matched.addAll([left, option.id]);
      _selectedLeft = null;
    });

    // Every pair made: hand the whole thing to the scorer at once, so a child
    // is told about their answer as a whole rather than judged pair by pair.
    if (_pairs.length >= expectedPairs) {
      _ctx.submit({'pairs': _pairs});
    }
  }

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final sides = splitMatchingSides(_ctx.options);
    final expected = sides.left.length;

    if (sides.left.isEmpty || sides.right.isEmpty) {
      return const Center(child: Text('This matching question is missing its pairs.'));
    }

    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        RendererPrompt(
          text: _selectedLeft == null ? 'Tap one, then its partner' : 'Now tap its partner',
        ),
        Expanded(
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: _Column(
                  options: sides.left,
                  media: _ctx.media,
                  selectedId: _selectedLeft,
                  matched: _matched,
                  autofocusFirst: true,
                  onTap: _ctx.isAnswering ? _tapLeft : null,
                ),
              ),
              SizedBox(width: KidSpacing.md * formFactor.density),
              Expanded(
                child: _Column(
                  options: sides.right,
                  media: _ctx.media,
                  selectedId: null,
                  matched: _matched,
                  dimmed: _selectedLeft == null,
                  onTap: _ctx.isAnswering ? (option) => _tapRight(option, expected) : null,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class _Column extends StatelessWidget {
  const _Column({
    required this.options,
    required this.media,
    required this.matched,
    this.selectedId,
    this.onTap,
    this.dimmed = false,
    this.autofocusFirst = false,
  });

  final List<PackOption> options;
  final Map<String, dynamic> media;
  final Set<int> matched;
  final int? selectedId;
  final void Function(PackOption option)? onTap;
  final bool dimmed;
  final bool autofocusFirst;

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;

    return ListView.separated(
      shrinkWrap: true,
      itemCount: options.length,
      separatorBuilder: (_, _) => SizedBox(height: KidSpacing.sm * formFactor.density),
      itemBuilder: (context, index) {
        final option = options[index];

        return KidTile(
          label: option.text,
          mediaKey: option.image,
          media: media.cast(),
          selected: option.id == selectedId,
          done: matched.contains(option.id),
          dimmed: dimmed,
          autofocus: autofocusFirst && index == 0,
          onPressed: onTap == null ? null : () => onTap!(option),
        );
      },
    );
  }
}
