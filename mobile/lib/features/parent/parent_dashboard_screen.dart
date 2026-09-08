import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/providers.dart';
import '../../core/models/child.dart';
import '../../core/models/parent_report.dart';
import '../../core/platform/form_factor.dart';
import '../../design/components/kid_button.dart';
import '../../design/components/kid_scaffold.dart';
import '../../design/components/progress_bar.dart';
import '../../design/tokens.dart';

/// What the parent came for: how their child is actually doing.
///
/// The website answered this with a page of encouragement and a hard-coded
/// "15 minutes". This screen only says things the projections can back up, and
/// when it is showing a cached copy it says so.
class ParentDashboardScreen extends ConsumerStatefulWidget {
  const ParentDashboardScreen({super.key, this.childId});

  final int? childId;

  @override
  ConsumerState<ParentDashboardScreen> createState() => _ParentDashboardScreenState();
}

class _ParentDashboardScreenState extends ConsumerState<ParentDashboardScreen> {
  static const List<({String value, String label})> _ranges = [
    (value: '7d', label: 'This week'),
    (value: '30d', label: '30 days'),
    (value: '90d', label: '90 days'),
  ];

  String _range = '7d';
  int? _childId;

  @override
  Widget build(BuildContext context) {
    final session = ref.watch(sessionProvider);
    final children = session.children;

    if (children.isEmpty) {
      return _frame(
        context,
        child: const Center(child: Text('Add a child first and their report will appear here.')),
      );
    }

    final childId = _childId ??
        widget.childId ??
        session.activeChild?.id ??
        children.first.id;

    final report = ref.watch(parentReportProvider((childId, _range)));

    return _frame(
      context,
      onRefresh: () => ref.invalidate(parentReportProvider((childId, _range))),
      child: ListView(
        children: [
          SizedBox(height: KidSpacing.sm * context.formFactor.density),
          if (children.length > 1) _ChildPicker(
            children: children,
            selectedId: childId,
            onSelect: (id) => setState(() => _childId = id),
          ),
          _RangePicker(
            ranges: _ranges,
            selected: _range,
            onSelect: (value) => setState(() => _range = value),
          ),
          SizedBox(height: KidSpacing.md * context.formFactor.density),
          report.when(
            loading: () => const Padding(
              padding: EdgeInsets.symmetric(vertical: KidSpacing.xxl),
              child: Center(child: CircularProgressIndicator()),
            ),
            error: (error, _) => _ErrorCard(
              message: '$error',
              onRetry: () => ref.invalidate(parentReportProvider((childId, _range))),
            ),
            data: (view) => _ReportBody(view: view),
          ),
          SizedBox(height: KidSpacing.xl * context.formFactor.density),
        ],
      ),
    );
  }

  Widget _frame(BuildContext context, {required Widget child, VoidCallback? onRefresh}) {
    return KidScaffold(
      onBack: () => context.go('/parent/home'),
      appBar: AppBar(
        title: const Text('How they are doing'),
        leading: BackButton(onPressed: () => context.go('/parent/home')),
        actions: [
          if (onRefresh != null)
            IconButton(
              tooltip: 'Refresh',
              icon: const Icon(Icons.refresh_rounded),
              onPressed: onRefresh,
            ),
        ],
      ),
      child: child,
    );
  }
}

class _ReportBody extends StatelessWidget {
  const _ReportBody({required this.view});

  final ParentReportView view;

  @override
  Widget build(BuildContext context) {
    final report = view.report;
    final gap = SizedBox(height: KidSpacing.md * context.formFactor.density);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        if (view.fromCache) ...[
          _CachedNotice(generatedAt: report.generatedAt),
          gap,
        ],
        _OverviewCard(report: report),
        gap,
        if (report.overview.daily.isNotEmpty) ...[
          _RhythmCard(days: report.overview.daily),
          gap,
        ],
        if (report.progress.subjects.isNotEmpty) ...[
          _SubjectsCard(subjects: report.progress.subjects),
          gap,
        ],
        _SupportCard(support: report.support, childName: report.childName),
        gap,
        if (report.progress.canDoNow.isNotEmpty) ...[
          _CanDoCard(skills: report.progress.canDoNow, next: report.progress.learningNext),
          gap,
        ],
        if (report.badges.isNotEmpty) ...[
          _BadgesCard(report: report),
          gap,
        ],
        if (report.history.isNotEmpty) _HistoryCard(history: report.history),
        if (!report.hasActivity) _EmptyCard(childName: report.childName),
      ],
    );
  }
}

// ── Cards ────────────────────────────────────────────────────────────────────

class _ReportCard extends StatelessWidget {
  const _ReportCard({required this.title, required this.child, this.subtitle});

  final String title;
  final String? subtitle;
  final Widget child;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final density = context.formFactor.density;

    return Card(
      child: Padding(
        padding: EdgeInsets.all(KidSpacing.md * density),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(title, style: theme.textTheme.titleLarge),
            if (subtitle != null) ...[
              SizedBox(height: KidSpacing.xs * density),
              Text(
                subtitle!,
                style: theme.textTheme.bodyMedium?.copyWith(color: theme.colorScheme.onSurfaceVariant),
              ),
            ],
            SizedBox(height: KidSpacing.sm * density),
            child,
          ],
        ),
      ),
    );
  }
}

class _OverviewCard extends StatelessWidget {
  const _OverviewCard({required this.report});

  final ParentReport report;

  @override
  Widget build(BuildContext context) {
    final overview = report.overview;
    final accuracy = overview.accuracyPercent;

    return _ReportCard(
      title: report.childName,
      subtitle: overview.daysPlayed > 0
          ? 'Played on ${overview.daysPlayed} of the last ${overview.daysInRange} days.'
          : 'No learning recorded in the last ${overview.daysInRange} days.',
      child: Wrap(
        spacing: KidSpacing.md,
        runSpacing: KidSpacing.md,
        children: [
          _Stat(label: 'Minutes today', value: '${overview.minutesToday}'),
          _Stat(label: 'Minutes this period', value: '${overview.minutesInRange}'),
          _Stat(label: 'Missions passed', value: '${overview.missionsPassed}'),
          _Stat(label: 'Questions answered', value: '${overview.questionsAnswered}'),
          _Stat(label: 'Correct', value: accuracy == null ? '—' : '$accuracy%'),
          _Stat(label: 'Day streak', value: '${report.streak}'),
        ],
      ),
    );
  }
}

class _Stat extends StatelessWidget {
  const _Stat({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return SizedBox(
      width: 150 * context.formFactor.density,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            value,
            style: theme.textTheme.headlineSmall?.copyWith(
              fontFamily: KidFonts.display,
              color: KidColors.primary,
            ),
          ),
          Text(
            label,
            style: theme.textTheme.labelLarge?.copyWith(color: theme.colorScheme.onSurfaceVariant),
          ),
        ],
      ),
    );
  }
}

/// Which days had any learning on them. A rhythm is what a parent can act on;
/// a total for the week is not.
class _RhythmCard extends StatelessWidget {
  const _RhythmCard({required this.days});

  final List<ReportDay> days;

  @override
  Widget build(BuildContext context) {
    final busiest = days.map((d) => d.minutes).fold<int>(1, (a, b) => b > a ? b : a);
    final theme = Theme.of(context);

    return _ReportCard(
      title: 'Daily rhythm',
      subtitle: 'Minutes played each day.',
      child: SizedBox(
        height: 110 * context.formFactor.density,
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.end,
          children: [
            for (final day in days)
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 3),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [
                      Text('${day.minutes}', style: theme.textTheme.labelSmall),
                      const SizedBox(height: 2),
                      Container(
                        height: (70 * (day.minutes / busiest)).clamp(4, 70).toDouble(),
                        decoration: BoxDecoration(
                          color: day.minutes > 0 ? KidColors.primary : KidColors.border,
                          borderRadius: const BorderRadius.vertical(top: Radius.circular(6)),
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        _weekday(day.day),
                        style: theme.textTheme.labelSmall
                            ?.copyWith(color: theme.colorScheme.onSurfaceVariant),
                      ),
                    ],
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }

  static String _weekday(String isoDay) {
    final parsed = DateTime.tryParse(isoDay);
    if (parsed == null) return '';

    const names = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    return names[(parsed.weekday - 1).clamp(0, 6)];
  }
}

class _SubjectsCard extends StatelessWidget {
  const _SubjectsCard({required this.subjects});

  final List<ReportSubject> subjects;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final density = context.formFactor.density;

    return _ReportCard(
      title: 'Subjects',
      subtitle: 'How often each subject is answered correctly.',
      child: Column(
        children: [
          for (final subject in subjects)
            Padding(
              padding: EdgeInsets.only(bottom: KidSpacing.sm * density),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(child: Text(subject.name, style: theme.textTheme.titleMedium)),
                      Text(
                        '${subject.accuracyPercent}% of ${subject.answered}',
                        style: theme.textTheme.labelLarge
                            ?.copyWith(color: theme.colorScheme.onSurfaceVariant),
                      ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  KidProgressBar(
                    current: subject.accuracyPercent,
                    total: 100,
                    showBeads: false,
                    accent: subject.accuracyPercent >= 70 ? KidColors.success : KidColors.amber,
                  ),
                ],
              ),
            ),
        ],
      ),
    );
  }
}

class _SupportCard extends StatelessWidget {
  const _SupportCard({required this.support, required this.childName});

  final ReportSupport support;
  final String childName;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return _ReportCard(
      title: support.hasStruggle ? 'Worth a little help' : 'Nothing is stuck',
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(support.headline, style: theme.textTheme.titleMedium),
          SizedBox(height: KidSpacing.xs * context.formFactor.density),
          Text(support.activity, style: theme.textTheme.bodyLarge),
        ],
      ),
    );
  }
}

class _CanDoCard extends StatelessWidget {
  const _CanDoCard({required this.skills, required this.next});

  final List<String> skills;
  final List<ReportNextMission> next;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return _ReportCard(
      title: 'What they can do now',
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Wrap(
            spacing: KidSpacing.sm,
            runSpacing: KidSpacing.sm,
            children: [
              for (final skill in skills)
                Chip(
                  avatar: const Icon(Icons.check_rounded, size: 16, color: KidColors.success),
                  label: Text(skill),
                ),
            ],
          ),
          if (next.isNotEmpty) ...[
            SizedBox(height: KidSpacing.md * context.formFactor.density),
            Text('Learning next', style: theme.textTheme.titleMedium),
            SizedBox(height: KidSpacing.xs * context.formFactor.density),
            for (final mission in next)
              Padding(
                padding: const EdgeInsets.only(bottom: 2),
                child: Row(
                  children: [
                    const Icon(Icons.arrow_forward_rounded, size: 16, color: KidColors.muted),
                    const SizedBox(width: KidSpacing.xs),
                    Expanded(child: Text(mission.title, style: theme.textTheme.bodyLarge)),
                  ],
                ),
              ),
          ],
        ],
      ),
    );
  }
}

class _BadgesCard extends StatelessWidget {
  const _BadgesCard({required this.report});

  final ParentReport report;

  @override
  Widget build(BuildContext context) {
    return _ReportCard(
      title: 'Badges earned',
      subtitle: '${report.stars} stars and ${report.coins} coins so far.',
      child: Wrap(
        spacing: KidSpacing.sm,
        runSpacing: KidSpacing.sm,
        children: [
          for (final badge in report.badges)
            Tooltip(
              message: badge.blurb ?? badge.name,
              child: Chip(
                avatar: Text(badge.icon ?? '🏅', style: const TextStyle(fontSize: 16)),
                label: Text(badge.name),
                backgroundColor: KidColors.amberSoft,
              ),
            ),
        ],
      ),
    );
  }
}

class _HistoryCard extends StatelessWidget {
  const _HistoryCard({required this.history});

  final List<ReportAttempt> history;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return _ReportCard(
      title: 'Recent missions',
      child: Column(
        children: [
          for (final attempt in history)
            ExpansionTile(
              tilePadding: EdgeInsets.zero,
              childrenPadding: const EdgeInsets.only(bottom: KidSpacing.sm),
              title: Text(attempt.title, style: theme.textTheme.titleMedium),
              subtitle: Text(
                '${attempt.score}/${attempt.total} · ${attempt.percentage}%'
                '${attempt.minutes > 0 ? ' · ${attempt.minutes} min' : ''}'
                '${attempt.passed ? '' : ' · not passed yet'}',
                style: theme.textTheme.bodyMedium?.copyWith(color: theme.colorScheme.onSurfaceVariant),
              ),
              trailing: StarRow(stars: attempt.stars),
              children: [
                if (attempt.mistakes.isEmpty)
                  const ListTile(
                    dense: true,
                    contentPadding: EdgeInsets.zero,
                    title: Text('Everything in this one was answered correctly.'),
                  )
                else
                  for (final mistake in attempt.mistakes)
                    ListTile(
                      dense: true,
                      contentPadding: EdgeInsets.zero,
                      leading: const Icon(Icons.help_outline_rounded, color: KidColors.muted),
                      title: Text(mistake),
                    ),
              ],
            ),
        ],
      ),
    );
  }
}

class _EmptyCard extends StatelessWidget {
  const _EmptyCard({required this.childName});

  final String childName;

  @override
  Widget build(BuildContext context) {
    return _ReportCard(
      title: 'Nothing to report yet',
      child: Text(
        'Once $childName finishes a mission, everything they do turns up here — '
        'time played, what they can do, and what they found hard.',
        style: Theme.of(context).textTheme.bodyLarge,
      ),
    );
  }
}

class _CachedNotice extends StatelessWidget {
  const _CachedNotice({this.generatedAt});

  final DateTime? generatedAt;

  @override
  Widget build(BuildContext context) {
    final when = generatedAt?.toLocal();

    return Container(
      padding: const EdgeInsets.all(KidSpacing.sm),
      decoration: const BoxDecoration(
        color: KidColors.amberSoft,
        borderRadius: KidRadius.card,
      ),
      child: Row(
        children: [
          const Icon(Icons.cloud_off_rounded, color: KidColors.amber),
          const SizedBox(width: KidSpacing.sm),
          Expanded(
            child: Text(
              when == null
                  ? 'Offline — showing the last report this device saw.'
                  : 'Offline — this is the report from '
                      '${when.day}/${when.month} at '
                      '${when.hour.toString().padLeft(2, '0')}:${when.minute.toString().padLeft(2, '0')}.',
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: KidColors.stageInk),
            ),
          ),
        ],
      ),
    );
  }
}

class _ErrorCard extends StatelessWidget {
  const _ErrorCard({required this.message, required this.onRetry});

  final String message;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    return _ReportCard(
      title: 'Could not load the report',
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(message, style: Theme.of(context).textTheme.bodyLarge),
          SizedBox(height: KidSpacing.sm * context.formFactor.density),
          KidButton(label: 'Try again', size: KidButtonSize.small, onPressed: onRetry),
        ],
      ),
    );
  }
}

// ── Pickers ──────────────────────────────────────────────────────────────────

class _ChildPicker extends StatelessWidget {
  const _ChildPicker({required this.children, required this.selectedId, required this.onSelect});

  final List<Child> children;
  final int selectedId;
  final ValueChanged<int> onSelect;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: KidSpacing.sm),
      child: Wrap(
        spacing: KidSpacing.sm,
        children: [
          for (final child in children)
            ChoiceChip(
              selected: child.id == selectedId,
              label: Text(child.name),
              onSelected: (_) => onSelect(child.id),
            ),
        ],
      ),
    );
  }
}

class _RangePicker extends StatelessWidget {
  const _RangePicker({required this.ranges, required this.selected, required this.onSelect});

  final List<({String value, String label})> ranges;
  final String selected;
  final ValueChanged<String> onSelect;

  @override
  Widget build(BuildContext context) {
    return Align(
      alignment: Alignment.centerLeft,
      child: SegmentedButton<String>(
        segments: [
          for (final range in ranges) ButtonSegment(value: range.value, label: Text(range.label)),
        ],
        selected: {selected},
        showSelectedIcon: false,
        onSelectionChanged: (values) => onSelect(values.first),
      ),
    );
  }
}
