import 'package:flutter/material.dart';

import '../../../core/models/pack.dart';
import '../../../core/platform/form_factor.dart';
import '../../../design/tokens.dart';
import 'renderer_kit.dart';

/// Put things in order.
///
/// Tapping a card sends it to the next empty slot. Nothing is checked until the
/// child says they are finished, so they can undo by tapping a placed card and
/// are never corrected mid-thought.
class DragSequenceRenderer extends StatefulWidget {
  const DragSequenceRenderer({super.key, required this.context_});

  final RendererContext context_;

  @override
  State<DragSequenceRenderer> createState() => _DragSequenceRendererState();
}

class _DragSequenceRendererState extends State<DragSequenceRenderer> {
  final List<int> _order = [];

  RendererContext get _ctx => widget.context_;

  void _place(PackOption option) {
    if (_order.contains(option.id)) return;
    setState(() => _order.add(option.id));
  }

  void _takeBack(int optionId) {
    setState(() => _order.remove(optionId));
  }

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final options = _ctx.options;
    final tray = options.where((o) => !_order.contains(o.id)).toList();
    final full = _order.length == options.length;

    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        RendererPrompt(text: full ? 'Happy with the order?' : 'Tap them in order'),

        // The slots, in the order chosen so far.
        Wrap(
          alignment: WrapAlignment.center,
          spacing: KidSpacing.sm * formFactor.density,
          runSpacing: KidSpacing.sm * formFactor.density,
          children: [
            for (var slot = 0; slot < options.length; slot++)
              if (slot < _order.length)
                _Slot(
                  position: slot + 1,
                  option: options.firstWhere((o) => o.id == _order[slot]),
                  media: _ctx.media,
                  onPressed: _ctx.isAnswering ? () => _takeBack(_order[slot]) : null,
                )
              else
                _EmptySlot(position: slot + 1),
          ],
        ),

        SizedBox(height: KidSpacing.lg * formFactor.density),

        if (tray.isNotEmpty)
          Wrap(
            alignment: WrapAlignment.center,
            spacing: KidSpacing.sm * formFactor.density,
            runSpacing: KidSpacing.sm * formFactor.density,
            children: [
              for (var i = 0; i < tray.length; i++)
                KidTile(
                  label: tray[i].text,
                  mediaKey: tray[i].image,
                  media: _ctx.media,
                  autofocus: i == 0,
                  onPressed: _ctx.isAnswering ? () => _place(tray[i]) : null,
                ),
            ],
          ),

        SizedBox(height: KidSpacing.lg * formFactor.density),
        CheckButton(
          enabled: full && _ctx.isAnswering,
          onPressed: () => _ctx.submit({'order': _order}),
        ),
      ],
    );
  }
}

class _Slot extends StatelessWidget {
  const _Slot({required this.position, required this.option, required this.media, this.onPressed});

  final int position;
  final PackOption option;
  final Map<String, dynamic> media;
  final VoidCallback? onPressed;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 110 * context.formFactor.density,
      child: KidTile(
        label: '$position. ${option.text ?? ''}',
        mediaKey: option.image,
        media: media.cast(),
        selected: true,
        onPressed: onPressed,
      ),
    );
  }
}

class _EmptySlot extends StatelessWidget {
  const _EmptySlot({required this.position});

  final int position;

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);

    return Container(
      width: 110 * formFactor.density,
      height: KidTouch.min * formFactor.density,
      alignment: Alignment.center,
      decoration: BoxDecoration(
        borderRadius: KidRadius.button,
        border: Border.all(color: theme.colorScheme.outline, width: 2, strokeAlign: BorderSide.strokeAlignInside),
        color: theme.colorScheme.surface.withValues(alpha: 0.4),
      ),
      child: Text('$position', style: theme.textTheme.titleMedium?.copyWith(color: KidColors.muted)),
    );
  }
}
