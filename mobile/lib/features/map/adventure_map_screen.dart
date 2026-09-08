import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/providers.dart';
import '../../core/models/pack.dart';
import '../../core/models/snapshot.dart';
import '../../core/platform/form_factor.dart';
import '../../design/components/exit_bar.dart';
import '../../design/components/focus_ring.dart';
import '../../design/components/kid_button.dart';
import '../../design/components/kid_scaffold.dart';
import '../../design/components/mascot_stage.dart';
import '../../design/components/world_card.dart';
import '../../design/tokens.dart';
import '../screen_time/screen_time.dart';
import 'map_state.dart';

/// The adventure map: every world this child can play, and where they are in it.
class AdventureMapScreen extends ConsumerStatefulWidget {
  const AdventureMapScreen({super.key});

  @override
  ConsumerState<AdventureMapScreen> createState() => _AdventureMapScreenState();
}

class _AdventureMapScreenState extends ConsumerState<AdventureMapScreen> {
  String _subject = 'all';
  String? _downloading;
  bool _checkedDevotional = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _maybeShowDevotional());
  }

  /// Show the devotional the first time the map opens each day.
  ///
  /// Once a day, not once a visit: a verse that appears every time a child
  /// comes back to the map stops being a moment and becomes an obstacle.
  Future<void> _maybeShowDevotional() async {
    if (_checkedDevotional) return;
    _checkedDevotional = true;

    final extras = await ref.read(extrasProvider.future);

    if (!extras.devotionalEnabled || extras.devotionals.isEmpty) return;

    final today = DateTime.now();
    final key = 'devotional_seen:${today.year}-${today.month}-${today.day}';
    final seen = await ref.read(progressDaoProvider).flag(key);

    if (seen || !mounted) return;

    await ref.read(progressDaoProvider).setFlag(key);

    if (mounted) context.go('/devotional');
  }

  static const Map<String, ({String label, String emoji})> _subjectLabels = {
    'all': (label: 'All', emoji: '🗺️'),
    'MATH': (label: 'Numbers', emoji: '🔢'),
    'ENGLISH': (label: 'Words', emoji: '📖'),
    'CRE': (label: 'Values', emoji: '✝️'),
    'SPEAK': (label: 'Speak', emoji: '🎙️'),
    'TRACING': (label: 'Tracing', emoji: '✏️'),
  };

  Future<void> _download(PackSummary pack) async {
    setState(() => _downloading = pack.packId);

    final repository = ref.read(contentRepositoryProvider);
    final messenger = ScaffoldMessenger.of(context);

    try {
      await repository.download(pack);
      ref.invalidate(mapDataProvider);
      ref.invalidate(installedPacksProvider);
    } catch (error) {
      messenger.showSnackBar(
        SnackBar(content: Text('That world did not download. $error')),
      );
    } finally {
      if (mounted) setState(() => _downloading = null);
    }
  }

  @override
  Widget build(BuildContext context) {
    final session = ref.watch(sessionProvider);
    final child = session.activeChild;
    final formFactor = context.formFactor;
    final theme = Theme.of(context);

    if (child == null) {
      return KidScaffold(
        child: LeoMessage(
          title: 'Who is playing?',
          body: 'Pick an explorer to see their adventure map.',
          action: KidButton(label: 'Choose an explorer', onPressed: () => context.go('/profiles')),
        ),
      );
    }

    final mapAsync = ref.watch(mapDataProvider(child));
    final sync = ref.watch(syncEngineProvider);

    // Time played on this device plus time the server knows about, so a limit
    // cannot be sidestepped by moving to another device mid-afternoon.
    final screenTime = ref.watch(screenTimeProvider(child)).value;

    return KidScaffold(
      onBack: () => context.go('/profiles'),
      appBar: KidExitBar(
        onBack: () {
          ref.read(sessionProvider.notifier).leaveChild();
          context.go('/profiles');
        },
        onParentZone: () => context.go('/parent'),
        stars: child.totalStars,
        coins: child.starCoins,
        onCoinsPressed: () => context.go('/shop'),
        onSongs: () => context.go('/songs'),
        streakDays: child.streakDays,
        minutesLeft: (screenTime != null && !screenTime.unlimited) ? screenTime.minutesLeft : null,
        unlimitedTime: screenTime?.unlimited ?? !child.hasTimeLimit,
        offline: sync.state.isOffline,
        pendingSync: sync.state.pending,
      ),
      child: mapAsync.when(
        loading: () => const LeoLoading(message: 'Rolling out the map…'),
        error: (error, _) => LeoMessage(
          title: 'The map got stuck',
          body: '$error',
          action: KidButton(label: 'Try again', onPressed: () => ref.invalidate(mapDataProvider)),
        ),
        data: (data) {
          final worlds = data.forSubject(_subject);

          return Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              _HeroCard(
                childName: child.name,
                avatar: child.avatar,
                hat: child.equippedHat,
                streak: child.streakDays,
                badges: ref.watch(badgesProvider(child.id)).value ?? const [],
              ),
              SizedBox(height: KidSpacing.md * formFactor.density),
              _SubjectTabs(
                subjects: ['all', ...data.subjects],
                selected: _subject,
                labels: _subjectLabels,
                onSelected: (value) => setState(() => _subject = value),
              ),
              SizedBox(height: KidSpacing.md * formFactor.density),
              Expanded(
                child: worlds.isEmpty
                    ? LeoMessage(
                        title: 'No worlds here yet',
                        body: data.offline
                            ? 'We cannot reach the internet, so only downloaded worlds are showing.'
                            : 'New adventures for this level are on the way.',
                        action: KidButton(
                          label: 'Check again',
                          onPressed: () => ref.invalidate(mapDataProvider),
                        ),
                      )
                    : ListView.separated(
                        padding: EdgeInsets.only(bottom: KidSpacing.xl * formFactor.density),
                        itemCount: worlds.length,
                        separatorBuilder: (_, _) => SizedBox(height: KidSpacing.md * formFactor.density),
                        itemBuilder: (context, index) {
                          final world = worlds[index];
                          final downloading = _downloading == world.packId;

                          return WorldCard(
                            name: world.name,
                            palette: world.palette,
                            icon: world.icon,
                            subtitle: world.isInstalled
                                ? '${world.missions.length} missions ready'
                                : '${(world.bytesCore / 1048576).toStringAsFixed(1)} MB to download',
                            missions: world.missions,
                            downloadState: downloading
                                ? WorldDownloadState.downloading
                                : world.downloadState,
                            onDownload: world.availablePack == null ? null : () => _download(world.availablePack!),
                            onMissionTap: (mission) => context.go(
                              // A mission already under way is allowed to finish;
                              // the limit stops a new one from starting.
                              (screenTime?.isOver ?? false) ? '/time-up' : '/mission/${mission.id}',
                            ),
                          );
                        },
                      ),
              ),
              if (data.offline)
                Padding(
                  padding: EdgeInsets.only(bottom: KidSpacing.sm * formFactor.density),
                  child: Text(
                    'Playing offline. Everything you do is saved and will upload later.',
                    textAlign: TextAlign.center,
                    style: theme.textTheme.labelSmall?.copyWith(color: theme.colorScheme.onSurfaceVariant),
                  ),
                ),
            ],
          );
        },
      ),
    );
  }
}

class _HeroCard extends StatelessWidget {
  const _HeroCard({
    required this.childName,
    required this.avatar,
    this.hat,
    this.streak = 0,
    this.badges = const [],
  });

  final String childName;
  final String avatar;
  final String? hat;
  final int streak;
  final List<EarnedBadge> badges;

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);

    return Container(
      padding: EdgeInsets.all(KidSpacing.md * formFactor.density),
      decoration: BoxDecoration(
        color: theme.colorScheme.surface,
        borderRadius: KidRadius.card,
        boxShadow: KidShadows.soft,
      ),
      child: Row(
        children: [
          MascotStage(character: avatar, hat: hat, size: formFactor.isTv ? 96 : 64),
          SizedBox(width: KidSpacing.md * formFactor.density),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Hi $childName!', style: theme.textTheme.titleLarge),
                Text(
                  streak > 1 ? 'Day $streak of your streak. Keep it going!' : 'Where shall we go today?',
                  style: theme.textTheme.bodyLarge?.copyWith(color: theme.colorScheme.onSurfaceVariant),
                ),
                if (badges.isNotEmpty) ...[
                  SizedBox(height: KidSpacing.xs * formFactor.density),
                  Wrap(
                    spacing: KidSpacing.xs,
                    children: [
                      for (final badge in badges)
                        Tooltip(
                          message: badge.blurb ?? badge.name,
                          child: Text(badge.icon ?? '🏅', style: TextStyle(fontSize: 20 * formFactor.density)),
                        ),
                    ],
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

class _SubjectTabs extends StatelessWidget {
  const _SubjectTabs({
    required this.subjects,
    required this.selected,
    required this.labels,
    required this.onSelected,
  });

  final List<String> subjects;
  final String selected;
  final Map<String, ({String label, String emoji})> labels;
  final ValueChanged<String> onSelected;

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);

    return SizedBox(
      height: KidTouch.recommended * formFactor.density,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: subjects.length,
        separatorBuilder: (_, _) => SizedBox(width: KidSpacing.sm * formFactor.density),
        itemBuilder: (context, index) {
          final code = subjects[index];
          final label = labels[code] ?? (label: code, emoji: '⭐');
          final active = code == selected;

          return KidFocusable(
            onPressed: () => onSelected(code),
            borderRadius: KidRadius.pill,
            semanticLabel: label.label,
            child: Container(
              padding: EdgeInsets.symmetric(horizontal: KidSpacing.md * formFactor.density),
              alignment: Alignment.center,
              decoration: BoxDecoration(
                color: active ? KidColors.primary : theme.colorScheme.surface,
                borderRadius: KidRadius.pill,
                border: Border.all(color: active ? KidColors.primaryDark : theme.colorScheme.outline, width: 2),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(label.emoji, style: TextStyle(fontSize: 16 * formFactor.density)),
                  SizedBox(width: KidSpacing.xs * formFactor.density),
                  Text(
                    label.label,
                    style: theme.textTheme.titleMedium?.copyWith(
                      color: active ? Colors.white : theme.colorScheme.onSurface,
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }
}
