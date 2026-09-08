import 'package:flutter/material.dart';

import '../../../core/models/pack.dart';
import '../../../core/platform/form_factor.dart';
import '../../../design/tokens.dart';
import 'renderer_kit.dart';

/// Sort things into groups.
///
/// Not a drag, despite the name the database gives it. One chip is active at a
/// time and the child taps the group it belongs in, which is two taps, works
/// with a remote control, and cannot be lost half way through a gesture by a
/// small hand. The website's engine settled on the same design.
class DragSortRenderer extends StatefulWidget {
  const DragSortRenderer({super.key, required this.context_});

  final RendererContext context_;

  @override
  State<DragSortRenderer> createState() => _DragSortRendererState();
}

class _DragSortRendererState extends State<DragSortRenderer> {
  final Map<int, String> _placements = {};

  RendererContext get _ctx => widget.context_;

  /// Group names come from the author's metadata where they exist, and from the
  /// answer keys themselves where they do not.
  List<String> get _buckets {
    final declared = _ctx.question.metadata['buckets'];

    if (declared is List && declared.isNotEmpty) {
      final names = declared
          .map((b) => b is Map ? (b['name'] ?? b['label'])?.toString() : b?.toString())
          .whereType<String>()
          .where((name) => name.trim().isNotEmpty)
          .toList();

      if (names.isNotEmpty) return names;
    }

    final keys = <String>[];
    for (final option in _ctx.options) {
      final key = option.matchKey?.trim();
      if (key != null && key.isNotEmpty && !keys.contains(key)) keys.add(key);
    }

    return keys;
  }

  List<PackOption> get _remaining =>
      _ctx.options.where((option) => !_placements.containsKey(option.id)).toList();

  void _place(String bucket) {
    final next = _remaining.firstOrNull;
    if (next == null) return;

    setState(() => _placements[next.id] = bucket);

    if (_remaining.isEmpty) {
      _ctx.submit({
        'placements': _placements.map((id, value) => MapEntry('$id', value)),
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final buckets = _buckets;
    final active = _remaining.firstOrNull;

    if (buckets.isEmpty) {
      return const Center(child: Text('This sorting question is missing its groups.'));
    }

    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        RendererPrompt(text: active == null ? 'All sorted!' : 'Where does this go?'),

        // The chip being placed, held large and central so it is obvious what
        // the next tap is about.
        if (active != null)
          KidTile(
            label: active.text,
            mediaKey: active.image,
            media: _ctx.media,
            selected: true,
            height: KidTouch.large * formFactor.density,
          ),

        SizedBox(height: KidSpacing.lg * formFactor.density),

        Wrap(
          alignment: WrapAlignment.center,
          spacing: KidSpacing.md * formFactor.density,
          runSpacing: KidSpacing.md * formFactor.density,
          children: [
            for (var i = 0; i < buckets.length; i++)
              _Bucket(
                name: buckets[i],
                count: _placements.values.where((v) => v == buckets[i]).length,
                autofocus: i == 0,
                onPressed: (_ctx.isAnswering && active != null) ? () => _place(buckets[i]) : null,
              ),
          ],
        ),

        SizedBox(height: KidSpacing.md * formFactor.density),
        Text(
          '${_placements.length} of ${_ctx.options.length} sorted',
          style: Theme.of(context).textTheme.labelSmall,
        ),
      ],
    );
  }
}

class _Bucket extends StatelessWidget {
  const _Bucket({required this.name, required this.count, this.onPressed, this.autofocus = false});

  final String name;
  final int count;
  final VoidCallback? onPressed;
  final bool autofocus;

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;

    return SizedBox(
      width: 150 * formFactor.density,
      child: KidTile(
        label: count > 0 ? '$name  ($count)' : name,
        accent: KidColors.amber,
        autofocus: autofocus,
        height: KidTouch.large * formFactor.density,
        onPressed: onPressed,
      ),
    );
  }
}
