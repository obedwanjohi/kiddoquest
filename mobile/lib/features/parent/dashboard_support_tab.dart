part of 'parent_dashboard_screen.dart';

/// Tab 3, "Learning Support" — how can I help?
class _SupportTab extends ConsumerStatefulWidget {
  const _SupportTab({required this.child, required this.report, required this.missions, required this.host});

  final Child child;
  final DashboardReport report;
  final List<FocusOption> missions;
  final _ParentDashboardScreenState host;

  @override
  ConsumerState<_SupportTab> createState() => _SupportTabState();
}

class _SupportTabState extends ConsumerState<_SupportTab> {
  late int? _focus = widget.child.assignedMissionId;
  bool _assigning = false;

  final _question = TextEditingController();
  String? _answer;
  bool _asking = false;

  @override
  void didUpdateWidget(_SupportTab oldWidget) {
    super.didUpdateWidget(oldWidget);

    if (oldWidget.child.id != widget.child.id) {
      _focus = widget.child.assignedMissionId;
      _answer = null;
      _question.clear();
    }
  }

  @override
  void dispose() {
    _question.dispose();
    super.dispose();
  }

  Future<void> _assign() async {
    setState(() => _assigning = true);

    try {
      await ref.read(parentDashboardRepositoryProvider).assignFocus(widget.child.id, _focus);

      final title = widget.missions.where((m) => m.id == _focus).map((m) => m.title).firstOrNull;

      widget.host.flash(_focus == null || title == null
          ? 'Focus mission assignment cleared.'
          : "📌 Assigned '$title' as focus mission for ${widget.child.name}!");

      await widget.host.refresh();
    } on ApiException catch (error) {
      widget.host.error(error.message);
    } finally {
      if (mounted) setState(() => _assigning = false);
    }
  }

  /// `ask(q)` and `submitAi()` on the website.
  Future<void> _ask([String? preset]) async {
    if (preset != null) _question.text = preset;

    final question = _question.text.trim();
    if (question.isEmpty || _asking) return;

    setState(() => _asking = true);

    try {
      final answer = await ref.read(parentDashboardRepositoryProvider).askCoach(widget.child.id, question);
      if (mounted) setState(() => _answer = answer);
    } on ApiException catch (error) {
      if (mounted) {
        setState(() => _answer = error.isOffline
            ? 'The coach needs the internet to answer. Everything else in the Parent Zone works offline.'
            : error.message);
      }
    } finally {
      if (mounted) setState(() => _asking = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final child = widget.child;
    final report = widget.report;
    final struggle = report.hasStruggle;
    final assigned = report.assignedMission != null;

    return _ParentCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          _CardHeading(
            emoji: child.avatarEmoji,
            title: 'Learning Support for ${child.name}',
            subtitle: 'Actionable Guidance & Focus Assignment',
          ),
          const SizedBox(height: 20),

          // Struggle area, or the learning insight when there is none.
          if (report.mistake.isNotEmpty) ...[
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: (struggle ? Tw.amber950 : Tw.emerald950).withValues(alpha: 0.4),
                borderRadius: BorderRadius.circular(Tw.rounded2xl),
                border: Border.all(color: (struggle ? Tw.amber400 : Tw.emerald400).withValues(alpha: 0.4)),
                boxShadow: Tw.shadowMd,
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(struggle ? '🎯' : '🎉', style: const TextStyle(fontSize: 24)),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          struggle ? 'STRUGGLE AREA IDENTIFIED' : 'LEARNING INSIGHT',
                          style: Tw.label(10, color: struggle ? Tw.amber400 : Tw.emerald400),
                        ),
                        const SizedBox(height: 2),
                        Text(report.mistake, style: Tw.text(Tw.xs, color: struggle ? Tw.amber200 : Tw.emerald200)),
                        const SizedBox(height: 8),
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: (struggle ? Tw.amber900 : Tw.emerald900).withValues(alpha: 0.3),
                            borderRadius: BorderRadius.circular(Tw.roundedXl),
                            border: Border.all(color: (struggle ? Tw.amber500 : Tw.emerald500).withValues(alpha: 0.3)),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                '💡 Recommended 1-Minute Home Activity:',
                                style: Tw.text(Tw.xs, color: struggle ? Tw.amber300 : Tw.emerald300, weight: FontWeight.w900),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                report.activity,
                                style: Tw.text(
                                  Tw.xs,
                                  color: struggle ? Tw.amber100 : Tw.emerald100,
                                  weight: FontWeight.w600,
                                  height: 1.6,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),
          ],

          // Tomorrow's focus mission.
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [Tw.indigo900.withValues(alpha: 0.6), Tw.purple900.withValues(alpha: 0.6)],
              ),
              borderRadius: BorderRadius.circular(Tw.rounded2xl),
              border: Border.all(color: Tw.indigo400.withValues(alpha: 0.4)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Row(
                  children: [
                    Expanded(child: Text("📌 TOMORROW'S FOCUS MISSION", style: Tw.label(Tw.xs, color: Tw.amber300))),
                    if (assigned)
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                        decoration: BoxDecoration(
                          color: Tw.amber400.withValues(alpha: 0.2),
                          borderRadius: BorderRadius.circular(999),
                          border: Border.all(color: Tw.amber400.withValues(alpha: 0.4)),
                        ),
                        child: Text('Assigned ✓', style: Tw.text(10, color: Tw.amber300, weight: FontWeight.w900)),
                      ),
                  ],
                ),
                const SizedBox(height: 8),
                Text(
                  "Select a mission to highlight on ${child.name}'s Adventure Map tomorrow:",
                  style: Tw.text(Tw.xs, color: Tw.slate300, weight: FontWeight.w500),
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Expanded(
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12),
                        decoration: BoxDecoration(
                          color: Tw.slate900,
                          borderRadius: BorderRadius.circular(Tw.roundedXl),
                          border: Border.all(color: Tw.slate600),
                        ),
                        child: DropdownButtonHideUnderline(
                          child: DropdownButton<int?>(
                            value: widget.missions.any((m) => m.id == _focus) ? _focus : null,
                            isExpanded: true,
                            dropdownColor: Tw.slate900,
                            iconEnabledColor: Tw.white,
                            style: Tw.text(Tw.xs, color: Tw.white),
                            items: [
                              const DropdownMenuItem<int?>(value: null, child: Text('-- Pick a Focus Mission --')),
                              for (final mission in widget.missions)
                                DropdownMenuItem<int?>(
                                  value: mission.id,
                                  child: Text(mission.title, overflow: TextOverflow.ellipsis),
                                ),
                            ],
                            onChanged: (value) => setState(() => _focus = value),
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    _SolidButton(
                      label: assigned ? 'Update' : '📌 Assign',
                      color: Tw.amber500,
                      textColor: Tw.slate900,
                      busy: _assigning,
                      expand: false,
                      onPressed: _assign,
                    ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),

          // Ask the AI pedagogy coach.
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [Tw.indigo950, Tw.purple950, Tw.slate900],
              ),
              borderRadius: BorderRadius.circular(Tw.rounded2xl),
              border: Border.all(color: Tw.indigo500.withValues(alpha: 0.4)),
              boxShadow: Tw.shadowXl,
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Row(
                  children: [
                    const TwBounce(child: Text('🤖', style: TextStyle(fontSize: 24))),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('ASK AI PEDAGOGY COACH', style: Tw.label(Tw.xs, color: Tw.indigo300)),
                          Text(
                            "Ask Leo's AI anything about early childhood learning!",
                            style: Tw.text(11, color: Tw.slate300, weight: FontWeight.w400),
                          ),
                        ],
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 2),
                      decoration: BoxDecoration(
                        color: Tw.amber400.withValues(alpha: 0.2),
                        borderRadius: BorderRadius.circular(999),
                        border: Border.all(color: Tw.amber400.withValues(alpha: 0.4)),
                      ),
                      child: Text('Premium AI ✨', style: Tw.text(10, color: Tw.amber300, weight: FontWeight.w900)),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Wrap(
                  spacing: 6,
                  runSpacing: 6,
                  children: [
                    _PromptChip(
                      label: '💬 "Why is my child confusing 6 and 9?"',
                      onPressed: () => _ask('Why is my child confusing 6 and 9?'),
                    ),
                    _PromptChip(
                      label: '💬 "Recommended daily screen time?"',
                      onPressed: () => _ask('How much screen time is healthy for a 4 year old?'),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Expanded(
                      child: TextField(
                        controller: _question,
                        onSubmitted: (_) => _ask(),
                        style: Tw.text(Tw.xs, color: Tw.white),
                        decoration: InputDecoration(
                          isDense: true,
                          hintText: 'Ask AI: e.g. Why does my child struggle with phonics?',
                          hintStyle: Tw.text(Tw.xs, color: Tw.slate500, weight: FontWeight.w600),
                          filled: true,
                          fillColor: Tw.slate900,
                          contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                          enabledBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(Tw.roundedXl),
                            borderSide: const BorderSide(color: Tw.slate700),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(Tw.roundedXl),
                            borderSide: const BorderSide(color: Tw.indigo500),
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    _SolidButton(
                      label: _asking ? 'Thinking...' : 'Ask AI 🚀',
                      color: Tw.indigo600,
                      expand: false,
                      onPressed: _asking ? null : () => _ask(),
                    ),
                  ],
                ),
                if (_answer != null && _answer!.isNotEmpty) ...[
                  const SizedBox(height: 12),
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Tw.slate900.withValues(alpha: 0.9),
                      borderRadius: BorderRadius.circular(Tw.roundedXl),
                      border: Border.all(color: Tw.indigo500.withValues(alpha: 0.4)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('AI TEACHER RESPONSE:', style: Tw.label(10, color: Tw.emerald400)),
                        const SizedBox(height: 4),
                        Text.rich(
                          _markdownBold(_answer!, Tw.text(Tw.xs, color: Tw.slate100, weight: FontWeight.w600, height: 1.6)),
                        ),
                      ],
                    ),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _PromptChip extends StatelessWidget {
  const _PromptChip({required this.label, required this.onPressed});

  final String label;
  final VoidCallback onPressed;

  @override
  Widget build(BuildContext context) {
    return KidFocusable(
      onPressed: onPressed,
      semanticLabel: label,
      borderRadius: BorderRadius.circular(Tw.roundedXl),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
        decoration: BoxDecoration(
          color: Tw.slate800,
          borderRadius: BorderRadius.circular(Tw.roundedXl),
          border: Border.all(color: Tw.slate700),
        ),
        child: Text(label, style: Tw.text(11, color: Tw.indigo200)),
      ),
    );
  }
}

/// The coach answers in light markdown: `**bold**` becomes bold, the rest is
/// kept as written, line breaks and all.
TextSpan _markdownBold(String text, TextStyle style) {
  final spans = <TextSpan>[];
  final pattern = RegExp(r'\*\*(.+?)\*\*', dotAll: true);
  var cursor = 0;

  for (final match in pattern.allMatches(text)) {
    if (match.start > cursor) spans.add(TextSpan(text: text.substring(cursor, match.start)));
    spans.add(TextSpan(text: match.group(1), style: const TextStyle(fontWeight: FontWeight.w900)));
    cursor = match.end;
  }

  if (cursor < text.length) spans.add(TextSpan(text: text.substring(cursor)));

  return TextSpan(style: style, children: spans);
}
