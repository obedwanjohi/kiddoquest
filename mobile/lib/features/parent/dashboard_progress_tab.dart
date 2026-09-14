part of 'parent_dashboard_screen.dart';

/// Tab 2, "Learning Progress" — the full report card.
class _ProgressTab extends StatefulWidget {
  const _ProgressTab({required this.child, required this.report});

  final Child child;
  final DashboardReport report;

  @override
  State<_ProgressTab> createState() => _ProgressTabState();
}

class _ProgressTabState extends State<_ProgressTab> {
  /// The website's subject pills relabel the report card; the report itself is
  /// the same for every subject, and so it is here.
  String _subject = 'all';

  static const List<({String key, String emoji, String label})> subjects = [
    (key: 'all', emoji: '🌟', label: 'All Subjects'),
    (key: 'math', emoji: '🔢', label: 'Mathematics'),
    (key: 'english', emoji: '📖', label: 'English & Phonics'),
    (key: 'cre', emoji: '✝️', label: 'CRE & Values'),
  ];

  void _inspect(HistoryRow row) {
    showDialog<void>(
      context: context,
      barrierColor: Tw.black.withValues(alpha: 0.8),
      builder: (context) => _Drilldown(row: row),
    );
  }

  @override
  Widget build(BuildContext context) {
    final child = widget.child;
    final report = widget.report;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        // Subject filter pills.
        SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          child: Row(
            children: [
              for (final subject in subjects) ...[
                if (subject.key != 'all') const SizedBox(width: 8),
                KidFocusable(
                  onPressed: () => setState(() => _subject = subject.key),
                  semanticLabel: subject.label,
                  borderRadius: BorderRadius.circular(Tw.roundedXl),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                    decoration: BoxDecoration(
                      color: _subject == subject.key ? Tw.indigo500.withValues(alpha: 0.3) : Tw.slate800.withValues(alpha: 0.8),
                      borderRadius: BorderRadius.circular(Tw.roundedXl),
                      border: Border.all(color: _subject == subject.key ? Tw.indigo400 : Tw.slate700),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(subject.emoji, style: const TextStyle(fontSize: 12)),
                        const SizedBox(width: 6),
                        Text(
                          subject.label,
                          style: Tw.text(
                            Tw.xs,
                            color: _subject == subject.key ? Tw.white : Tw.slate300,
                            weight: _subject == subject.key ? FontWeight.w800 : FontWeight.w700,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ],
          ),
        ),
        const SizedBox(height: 16),

        _ParentCard(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              _CardHeading(
                emoji: child.avatarEmoji,
                title: "${child.name}'s Report Card",
                subtitle: '${_subject[0].toUpperCase()}${_subject.substring(1)} Analytics',
                trailing: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: Tw.emerald500.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(Tw.roundedXl),
                    border: Border.all(color: Tw.emerald500.withValues(alpha: 0.4)),
                  ),
                  child: Text(report.growthLabel, style: Tw.text(Tw.xs, color: Tw.emerald300, weight: FontWeight.w900)),
                ),
              ),
              const SizedBox(height: 20),

              // What my child can do now.
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Tw.slate800.withValues(alpha: 0.8),
                  borderRadius: BorderRadius.circular(Tw.rounded2xl),
                  border: Border.all(color: Tw.slate700.withValues(alpha: 0.6)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('🌟 COMPETENCIES MASTERED:', style: Tw.label(Tw.xs, color: Tw.emerald400)),
                    const SizedBox(height: 10),
                    if (report.canDoNow.isEmpty)
                      Text(
                        'ℹ️  No missions completed yet for ${child.name}. Complete a mission to unlock competencies!',
                        style: Tw.text(Tw.xs, color: Tw.slate400, weight: FontWeight.w500),
                      )
                    else
                      for (final item in report.canDoNow)
                        Padding(
                          padding: const EdgeInsets.only(bottom: 6),
                          child: _Bullet(bullet: '✅', text: item, style: Tw.text(Tw.xs, color: Tw.slate100)),
                        ),
                    const SizedBox(height: 12),
                    Text('🔜 LEARNING NEXT:', style: Tw.label(Tw.xs, color: Tw.indigo300)),
                    const SizedBox(height: 6),
                    if (report.learningNext.isEmpty)
                      Text('Start Mission 1 on the Adventure Map', style: Tw.text(Tw.xs, color: Tw.slate400, weight: FontWeight.w500))
                    else
                      for (final item in report.learningNext)
                        Padding(
                          padding: const EdgeInsets.only(bottom: 4),
                          child: _Bullet(bullet: '🔜', text: item, style: Tw.text(Tw.xs, color: Tw.indigo200, weight: FontWeight.w600)),
                        ),
                  ],
                ),
              ),
              const SizedBox(height: 20),

              // Skills heat map.
              Text('📊 SKILLS HEAT MAP', style: Tw.label(Tw.xs, color: Tw.indigo300)),
              const SizedBox(height: 12),
              Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: Tw.slate900.withValues(alpha: 0.6),
                  borderRadius: BorderRadius.circular(Tw.rounded2xl),
                  border: Border.all(color: Tw.slate800),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    for (final row in report.heatMap)
                      Padding(
                        padding: const EdgeInsets.only(bottom: 10),
                        child: _HeatMapRow(row: row),
                      ),
                  ],
                ),
              ),
              const SizedBox(height: 20),

              // Mission history.
              Text('📈 MISSION HISTORY & ATTEMPT DRILLDOWN', style: Tw.label(Tw.xs, color: Tw.indigo300)),
              const SizedBox(height: 12),
              _HistoryTable(childName: child.name, rows: report.history, onInspect: _inspect),
            ],
          ),
        ),
      ],
    );
  }
}

class _Bullet extends StatelessWidget {
  const _Bullet({required this.bullet, required this.text, required this.style});

  final String bullet;
  final String text;
  final TextStyle style;

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(bullet, style: const TextStyle(fontSize: 12)),
        const SizedBox(width: 8),
        Expanded(child: Text(text, style: style)),
      ],
    );
  }
}

class _HeatMapRow extends StatelessWidget {
  const _HeatMapRow({required this.row});

  final HeatRow row;

  @override
  Widget build(BuildContext context) {
    final sm = Tw.isSm(context);

    final bars = ConstrainedBox(
      constraints: const BoxConstraints(maxWidth: 200),
      child: Row(
        children: [
          Expanded(
            child: Row(
              children: [
                for (var b = 1; b <= row.total; b++) ...[
                  if (b > 1) const SizedBox(width: 4),
                  Expanded(
                    child: Container(
                      height: 10,
                      decoration: BoxDecoration(
                        color: b <= row.bar ? Tw.green500 : Tw.white.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(2),
                        boxShadow: b <= row.bar
                            ? [BoxShadow(color: Tw.green500.withValues(alpha: 0.5), blurRadius: 6)]
                            : null,
                      ),
                    ),
                  ),
                ],
              ],
            ),
          ),
          const SizedBox(width: 6),
          SizedBox(
            width: 32,
            child: Text(
              '${row.score}%',
              textAlign: TextAlign.right,
              style: Tw.text(11, color: Tw.indigo300, weight: FontWeight.w900),
            ),
          ),
        ],
      ),
    );

    final name = SizedBox(
      width: 176,
      child: Text(row.name, maxLines: 1, overflow: TextOverflow.ellipsis, style: Tw.text(Tw.xs, color: Tw.slate200)),
    );

    if (sm) {
      return Row(
        children: [
          name,
          const Spacer(),
          Flexible(child: bars),
        ],
      );
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        name,
        const SizedBox(height: 4),
        bars,
      ],
    );
  }
}

class _HistoryTable extends StatelessWidget {
  const _HistoryTable({required this.childName, required this.rows, required this.onInspect});

  final String childName;
  final List<HistoryRow> rows;
  final ValueChanged<HistoryRow> onInspect;

  @override
  Widget build(BuildContext context) {
    final head = Tw.label(11, color: Tw.indigo300);

    return ClipRRect(
      borderRadius: BorderRadius.circular(Tw.rounded2xl),
      child: Container(
        decoration: BoxDecoration(
          color: Tw.slate900.withValues(alpha: 0.8),
          borderRadius: BorderRadius.circular(Tw.rounded2xl),
          border: Border.all(color: Tw.slate800),
        ),
        child: Column(
          children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Tw.slate800.withValues(alpha: 0.9),
                border: const Border(bottom: BorderSide(color: Tw.slate700)),
              ),
              child: Row(
                children: [
                  Expanded(flex: 5, child: Text('MISSION', style: head)),
                  Expanded(flex: 3, child: Text('ATTEMPTS', textAlign: TextAlign.center, style: head)),
                  Expanded(flex: 3, child: Text('BEST\nSTARS', textAlign: TextAlign.center, style: head)),
                  Expanded(flex: 3, child: Text('ACTION', textAlign: TextAlign.right, style: head)),
                ],
              ),
            ),
            if (rows.isEmpty)
              Padding(
                padding: const EdgeInsets.all(16),
                child: Text(
                  'No mission attempts recorded yet for $childName.',
                  textAlign: TextAlign.center,
                  style: Tw.text(Tw.xs, color: Tw.slate400, weight: FontWeight.w500),
                ),
              )
            else
              for (var i = 0; i < rows.length; i++)
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 14),
                  decoration: BoxDecoration(
                    border: i == rows.length - 1 ? null : const Border(bottom: BorderSide(color: Tw.slate800)),
                  ),
                  child: Row(
                    children: [
                      Expanded(
                        flex: 5,
                        child: Text(rows[i].title, style: Tw.text(Tw.xs, color: Tw.white)),
                      ),
                      Expanded(
                        flex: 3,
                        child: Text(
                          '${rows[i].attemptsCount}',
                          textAlign: TextAlign.center,
                          style: Tw.text(Tw.xs, color: Tw.slate200, weight: FontWeight.w600)
                              .copyWith(fontFeatures: const [FontFeature.tabularFigures()]),
                        ),
                      ),
                      Expanded(
                        flex: 3,
                        child: Text(
                          rows[i].bestStars == 0 ? 'No stars' : List.filled(rows[i].bestStars, '⭐').join(' '),
                          textAlign: TextAlign.center,
                          style: rows[i].bestStars == 0
                              ? Tw.text(Tw.xs, color: Tw.slate500, weight: FontWeight.w400)
                              : const TextStyle(fontSize: 11),
                        ),
                      ),
                      Expanded(
                        flex: 3,
                        child: Align(
                          alignment: Alignment.centerRight,
                          child: KidFocusable(
                            onPressed: () => onInspect(rows[i]),
                            semanticLabel: 'Inspect ${rows[i].title}',
                            borderRadius: BorderRadius.circular(Tw.roundedXl),
                            child: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                              decoration: BoxDecoration(
                                color: Tw.indigo600.withValues(alpha: 0.4),
                                borderRadius: BorderRadius.circular(Tw.roundedXl),
                                border: Border.all(color: Tw.indigo500.withValues(alpha: 0.4)),
                              ),
                              child: Text('Inspect →', textAlign: TextAlign.center, style: Tw.text(11, color: Tw.indigo200)),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
          ],
        ),
      ),
    );
  }
}

/// The attempt drilldown: every try, and the questions that tripped them up.
class _Drilldown extends StatelessWidget {
  const _Drilldown({required this.row});

  final HistoryRow row;

  @override
  Widget build(BuildContext context) {
    return Dialog(
      backgroundColor: Colors.transparent,
      insetPadding: const EdgeInsets.all(16),
      child: Container(
        constraints: const BoxConstraints(maxWidth: 448),
        padding: const EdgeInsets.all(24),
        decoration: BoxDecoration(
          color: Tw.slate900,
          borderRadius: BorderRadius.circular(Tw.rounded3xl),
          border: Border.all(color: Tw.indigo500.withValues(alpha: 0.4), width: 2),
          boxShadow: Tw.shadow2xl,
        ),
        child: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Row(
                children: [
                  const Text('📈', style: TextStyle(fontSize: 24)),
                  const SizedBox(width: 8),
                  Expanded(child: Text(row.title, style: Tw.text(Tw.lg, color: Tw.white, weight: FontWeight.w900))),
                  KidFocusable(
                    onPressed: () => Navigator.of(context).pop(),
                    semanticLabel: 'Close',
                    borderRadius: BorderRadius.circular(8),
                    child: Padding(
                      padding: const EdgeInsets.all(4),
                      child: Text('✕', style: Tw.text(Tw.xl, color: Tw.slate400)),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Text('ATTEMPT LOG:', style: Tw.label(Tw.xs, color: Tw.indigo300)),
              const SizedBox(height: 8),
              for (final attempt in row.attempts)
                Container(
                  margin: const EdgeInsets.only(bottom: 6),
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(color: Tw.slate800, borderRadius: BorderRadius.circular(Tw.roundedXl)),
                  child: Row(
                    children: [
                      Expanded(
                        child: Text(
                          'Attempt ${attempt.attempt} (${attempt.date})',
                          style: Tw.text(Tw.xs, color: Tw.white),
                        ),
                      ),
                      Text(attempt.score, style: Tw.text(Tw.xs, color: Tw.emerald400, weight: FontWeight.w900)),
                    ],
                  ),
                ),
              const SizedBox(height: 16),
              Text('STRUGGLE / MISTAKES RECORDED:', style: Tw.label(Tw.xs, color: Tw.amber400)),
              const SizedBox(height: 8),
              if (row.mistakes.isEmpty)
                Text('✨ Perfect execution! No mistakes recorded.', style: Tw.text(Tw.xs, color: Tw.emerald400))
              else
                for (final mistake in row.mistakes)
                  Container(
                    margin: const EdgeInsets.only(bottom: 6),
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: Tw.amber950.withValues(alpha: 0.4),
                      borderRadius: BorderRadius.circular(Tw.roundedXl),
                      border: Border.all(color: Tw.amber500.withValues(alpha: 0.3)),
                    ),
                    child: Text(mistake, style: Tw.text(Tw.xs, color: Tw.amber200, weight: FontWeight.w600)),
                  ),
              const SizedBox(height: 20),
              _SolidButton(
                label: 'Close Drilldown',
                color: Tw.indigo600,
                onPressed: () => Navigator.of(context).pop(),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
