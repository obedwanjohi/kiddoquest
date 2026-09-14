part of 'parent_dashboard_screen.dart';

/// Tab 1, "Daily Overview" — how is my child doing today?
class _OverviewTab extends StatelessWidget {
  const _OverviewTab({required this.child, required this.report, required this.onViewProgress});

  final Child child;
  final DashboardReport report;
  final VoidCallback onViewProgress;

  @override
  Widget build(BuildContext context) {
    final sm = Tw.isSm(context);
    final canDo = report.canDoNow;

    // `$child->daily_time_limit_minutes ?? 30`, shown as ∞ when there is none.
    final limit = child.dailyTimeLimitMinutes;
    final screen = '${report.learningTimeToday} / ${limit > 0 ? '${limit}m' : '∞'}';

    final headerShare = Uri.parse('https://api.whatsapp.com/send').replace(queryParameters: {
      'text': '🌟 *KiddoQuest CBC Report for ${child.name}* 🌟\n\n'
          '⭐ Total Stars: ${child.totalStars}\n'
          '🪙 Star Coins: ${child.starCoins}\n'
          '🔥 Streak: ${report.streakDays} Days\n'
          '🎯 Missions Passed: ${report.passedMissions}\n'
          '📊 Accuracy: ${report.accuracyRate}%\n\n'
          'Keep up the awesome CBC learning adventure! 🚀 https://www.kiddoquest.co.ke',
    });

    final reportCardShare = Uri.parse('https://wa.me/').replace(queryParameters: {
      'text': '🌟 *Proud Parent Moment!* 🌟\n'
          'My child *${child.name}* is learning on *KiddoQuest CBC*! 🎓\n\n'
          '✅ *Mastery:* ${canDo.isNotEmpty ? canDo.first : 'Counting & Phonics'}\n'
          '🔥 *Streak:* ${report.streakDays} Days\n'
          '⭐ *Stars Earned:* ${_thousands(child.totalStars)}\n'
          '📊 *Accuracy:* ${report.accuracyRate}%\n\n'
          'Try KiddoQuest CBC for your kids here: ${_siteOrigin()}/parent/dashboard',
    });

    return _ParentCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Snapshot header.
          Container(
            padding: const EdgeInsets.only(bottom: 12),
            decoration: BoxDecoration(border: Border(bottom: BorderSide(color: Tw.slate700.withValues(alpha: 0.5)))),
            child: Row(
              children: [
                Container(
                  width: 48,
                  height: 48,
                  alignment: Alignment.center,
                  decoration: BoxDecoration(
                    color: Tw.amber400.withValues(alpha: 0.2),
                    shape: BoxShape.circle,
                    border: Border.all(color: Tw.amber400, width: 2),
                  ),
                  child: Text(child.avatarEmoji, style: const TextStyle(fontSize: 30)),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        child.name,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: Tw.text(Tw.lg, color: Tw.white, weight: FontWeight.w900, height: 1.2),
                      ),
                      Text(
                        'Playing as ${child.avatarName}',
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: Tw.text(Tw.xs, color: Tw.indigo300, weight: FontWeight.w600),
                      ),
                    ],
                  ),
                ),
                KidFocusable(
                  onPressed: () => _openShare(context, headerShare),
                  semanticLabel: 'Share child progress to WhatsApp',
                  borderRadius: BorderRadius.circular(Tw.rounded2xl),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: Tw.emerald600,
                      borderRadius: BorderRadius.circular(Tw.rounded2xl),
                      boxShadow: Tw.shadowMd,
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Text('📲', style: TextStyle(fontSize: 12)),
                        if (sm) ...[
                          const SizedBox(width: 4),
                          Text('Share Report', style: Tw.text(Tw.xs, color: Tw.white, weight: FontWeight.w900)),
                        ],
                      ],
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: Tw.amber500.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(Tw.rounded2xl),
                    border: Border.all(color: Tw.amber500.withValues(alpha: 0.4)),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const TwPulse(child: Text('🔥', style: TextStyle(fontSize: 16))),
                      const SizedBox(width: 4),
                      Text('${report.streakDays}d Streak', style: Tw.text(Tw.xs, color: Tw.amber300, weight: FontWeight.w900)),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Quick KPIs.
          LayoutBuilder(
            builder: (context, constraints) {
              const gap = 10.0;
              final columns = sm ? 3 : 2;
              final tile = (constraints.maxWidth - gap * (columns - 1)) / columns;

              return Wrap(
                spacing: gap,
                runSpacing: gap,
                children: [
                  SizedBox(
                    width: tile,
                    child: _Kpi(
                      label: 'Total Stars',
                      value: '⭐ ${_thousands(child.totalStars)}',
                      style: Tw.text(Tw.lg, color: Tw.amber400, weight: FontWeight.w900),
                    ),
                  ),
                  SizedBox(
                    width: tile,
                    child: _Kpi(
                      label: 'Screen Time Used',
                      value: screen,
                      style: Tw.text(Tw.sm, color: Tw.indigo300, weight: FontWeight.w900),
                    ),
                  ),
                  SizedBox(
                    width: sm ? tile : constraints.maxWidth,
                    child: _Kpi(
                      label: "Tomorrow's Focus",
                      value: report.assignedMission == null ? 'None set yet' : '📌 ${report.assignedMission!.title}',
                      style: Tw.text(Tw.xs, color: Tw.amber300, weight: FontWeight.w900),
                    ),
                  ),
                ],
              );
            },
          ),
          const SizedBox(height: 16),

          // Latest mastery and the report-card share.
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: Tw.indigo950.withValues(alpha: 0.6),
              borderRadius: BorderRadius.circular(Tw.rounded2xl),
              border: Border.all(color: Tw.indigo500.withValues(alpha: 0.3)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Row(
                  children: [
                    const Text('🌟', style: TextStyle(fontSize: 20)),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text.rich(
                        TextSpan(
                          text: 'Latest Mastery: ',
                          style: Tw.text(Tw.xs, color: Tw.slate100),
                          children: [
                            TextSpan(
                              text: canDo.isNotEmpty ? canDo.first : 'Count objects from 1 to 10',
                              style: const TextStyle(color: Tw.emerald400, fontWeight: FontWeight.w900),
                            ),
                          ],
                        ),
                      ),
                    ),
                    KidFocusable(
                      onPressed: onViewProgress,
                      semanticLabel: 'View Progress',
                      borderRadius: BorderRadius.circular(8),
                      child: Padding(
                        padding: const EdgeInsets.all(4),
                        child: Text('View Progress →', style: Tw.text(Tw.xs, color: Tw.indigo300, weight: FontWeight.w800)),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                _SolidButton(
                  label: "📲  Share ${child.name}'s Report Card on WhatsApp",
                  color: Tw.emerald600,
                  border: Tw.emerald400.withValues(alpha: 0.4),
                  onPressed: () => _openShare(context, reportCardShare),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _Kpi extends StatelessWidget {
  const _Kpi({required this.label, required this.value, required this.style});

  final String label;
  final String value;
  final TextStyle style;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Tw.slate800.withValues(alpha: 0.8),
        borderRadius: BorderRadius.circular(Tw.rounded2xl),
        border: Border.all(color: Tw.slate700.withValues(alpha: 0.6)),
      ),
      child: Column(
        children: [
          Text(label, textAlign: TextAlign.center, style: Tw.text(Tw.xs, color: Tw.slate400)),
          const SizedBox(height: 2),
          Text(value, maxLines: 1, overflow: TextOverflow.ellipsis, textAlign: TextAlign.center, style: style),
        ],
      ),
    );
  }
}
