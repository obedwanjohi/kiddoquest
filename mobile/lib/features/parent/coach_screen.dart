import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/providers.dart';
import '../../core/network/api_exception.dart';
import '../../core/platform/form_factor.dart';
import '../../design/components/kid_button.dart';
import '../../design/components/kid_scaffold.dart';
import '../../design/tokens.dart';

/// The coach: a parent's question about their child, answered with that child's
/// real numbers in front of it.
///
/// This one screen genuinely needs the internet, and says so rather than
/// queuing a question that would be answered hours later into an empty room.
class CoachScreen extends ConsumerStatefulWidget {
  const CoachScreen({super.key});

  @override
  ConsumerState<CoachScreen> createState() => _CoachScreenState();
}

class _CoachScreenState extends ConsumerState<CoachScreen> {
  static const List<String> _starters = [
    'How is my child doing?',
    'How can I help with counting?',
    'How much screen time is right at this age?',
    'What should we practise this week?',
  ];

  final _controller = TextEditingController();
  final List<({bool fromParent, String text})> _thread = [];

  bool _busy = false;
  int? _childId;

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  Future<void> _ask(String question, int childId) async {
    if (_busy) return;

    setState(() {
      _busy = true;
      _thread.add((fromParent: true, text: question));
    });

    _controller.clear();

    try {
      final response = await ref.read(apiClientProvider).post('/parent/coach', body: {
        'child_id': childId,
        'question': question,
      });

      if (mounted) {
        setState(() {
          _busy = false;
          _thread.add((fromParent: false, text: response['answer'] as String? ?? ''));
        });
      }
    } on ApiException catch (error) {
      if (mounted) {
        setState(() {
          _busy = false;
          _thread.add((
            fromParent: false,
            text: error.isOffline
                ? 'The coach needs the internet to answer. Your report works offline, though — everything in it is on this device.'
                : error.message,
          ));
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final session = ref.watch(sessionProvider);
    final children = session.children;
    final density = context.formFactor.density;
    final theme = Theme.of(context);

    if (children.isEmpty) {
      return _frame(const Center(child: Text('Add a child first and the coach can help.')));
    }

    final childId = _childId ?? session.activeChild?.id ?? children.first.id;
    final child = children.firstWhere((c) => c.id == childId, orElse: () => children.first);

    return _frame(
      Column(
        children: [
          if (children.length > 1)
            Padding(
              padding: EdgeInsets.only(top: KidSpacing.sm * density),
              child: Wrap(
                spacing: KidSpacing.sm,
                children: [
                  for (final option in children)
                    ChoiceChip(
                      selected: option.id == childId,
                      label: Text(option.name),
                      onSelected: (_) => setState(() => _childId = option.id),
                    ),
                ],
              ),
            ),
          Expanded(
            child: _thread.isEmpty
                ? _Starters(
                    childName: child.name,
                    starters: _starters,
                    onPick: (question) => _ask(question, childId),
                  )
                : ListView.builder(
                    padding: EdgeInsets.symmetric(vertical: KidSpacing.md * density),
                    itemCount: _thread.length + (_busy ? 1 : 0),
                    itemBuilder: (context, index) {
                      if (index >= _thread.length) {
                        return const Padding(
                          padding: EdgeInsets.all(KidSpacing.md),
                          child: Align(
                            alignment: Alignment.centerLeft,
                            child: SizedBox(
                              width: 24,
                              height: 24,
                              child: CircularProgressIndicator(strokeWidth: 2),
                            ),
                          ),
                        );
                      }

                      final message = _thread[index];

                      return _Bubble(text: message.text, fromParent: message.fromParent);
                    },
                  ),
          ),
          Padding(
            padding: EdgeInsets.only(bottom: KidSpacing.md * density),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _controller,
                    enabled: !_busy,
                    textInputAction: TextInputAction.send,
                    decoration: InputDecoration(
                      hintText: 'Ask about ${child.name}…',
                      border: const OutlineInputBorder(),
                    ),
                    onSubmitted: (value) {
                      if (value.trim().isNotEmpty) _ask(value.trim(), childId);
                    },
                  ),
                ),
                SizedBox(width: KidSpacing.sm * density),
                KidButton(
                  label: 'Ask',
                  icon: Icons.send_rounded,
                  size: KidButtonSize.small,
                  busy: _busy,
                  onPressed: _busy
                      ? null
                      : () {
                          final question = _controller.text.trim();
                          if (question.isNotEmpty) _ask(question, childId);
                        },
                ),
              ],
            ),
          ),
          Text(
            'The coach only answers questions about learning and parenting.',
            style: theme.textTheme.labelSmall?.copyWith(color: theme.colorScheme.onSurfaceVariant),
          ),
          SizedBox(height: KidSpacing.sm * density),
        ],
      ),
    );
  }

  Widget _frame(Widget child) {
    return KidScaffold(
      onBack: () => context.go('/parent/home'),
      appBar: AppBar(
        title: const Text('Ask the coach'),
        leading: BackButton(onPressed: () => context.go('/parent/home')),
      ),
      child: child,
    );
  }
}

class _Starters extends StatelessWidget {
  const _Starters({required this.childName, required this.starters, required this.onPick});

  final String childName;
  final List<String> starters;
  final ValueChanged<String> onPick;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final density = context.formFactor.density;

    return ListView(
      padding: EdgeInsets.symmetric(vertical: KidSpacing.lg * density),
      children: [
        Text('What would you like to know about $childName?', style: theme.textTheme.titleLarge),
        SizedBox(height: KidSpacing.sm * density),
        Text(
          'The coach can see what they have played, how often they get things '
          'right, and what they have found hard.',
          style: theme.textTheme.bodyLarge?.copyWith(color: theme.colorScheme.onSurfaceVariant),
        ),
        SizedBox(height: KidSpacing.lg * density),
        for (final starter in starters)
          Padding(
            padding: EdgeInsets.only(bottom: KidSpacing.sm * density),
            child: OutlinedButton(
              onPressed: () => onPick(starter),
              style: OutlinedButton.styleFrom(
                alignment: Alignment.centerLeft,
                padding: EdgeInsets.all(KidSpacing.md * density),
              ),
              child: Align(alignment: Alignment.centerLeft, child: Text(starter)),
            ),
          ),
      ],
    );
  }
}

class _Bubble extends StatelessWidget {
  const _Bubble({required this.text, required this.fromParent});

  final String text;
  final bool fromParent;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final density = context.formFactor.density;

    return Align(
      alignment: fromParent ? Alignment.centerRight : Alignment.centerLeft,
      child: Container(
        margin: EdgeInsets.only(bottom: KidSpacing.sm * density),
        padding: EdgeInsets.all(KidSpacing.md * density),
        constraints: const BoxConstraints(maxWidth: 520),
        decoration: BoxDecoration(
          color: fromParent ? KidColors.primary : theme.colorScheme.surfaceContainerHighest,
          borderRadius: KidRadius.card,
        ),
        child: Text(
          text,
          style: theme.textTheme.bodyLarge?.copyWith(
            color: fromParent ? Colors.white : theme.colorScheme.onSurface,
          ),
        ),
      ),
    );
  }
}
