import 'dart:async';
import 'dart:ui' as ui;

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/providers.dart';
import '../../core/models/child.dart';
import '../../core/models/extras.dart';
import '../../core/models/pack.dart';
import '../../core/network/api_exception.dart';
import '../../core/platform/form_factor.dart';
import '../../design/components/focus_ring.dart';
import '../../design/site/site_exit_bar.dart';
import '../../design/site/site_scaffold.dart';
import '../../design/site/tw.dart';
import '../../design/site/tw_motion.dart';
import '../screen_time/screen_time.dart';
import 'site_map.dart';

/// The adventure map, drawn the way the website draws it.
///
/// Mirrors `resources/views/kids/map.blade.php`: the sky-to-sunset canvas, the
/// welcome card, seven subject buttons with Songs locked until a mission is
/// passed, a coloured card per world and a winding trail of missions under it —
/// green with stars once done, glowing orange with PLAY! until then. The
/// morning devotional opens over the map once a day.
///
/// What the website does with a page load the app does with the pack system: a
/// world is fetched the first time a child taps one of its missions, so every
/// mission is on the map from the start and plays offline once opened.
class AdventureMapScreen extends ConsumerStatefulWidget {
  const AdventureMapScreen({super.key});

  @override
  ConsumerState<AdventureMapScreen> createState() => _AdventureMapScreenState();
}

class _AdventureMapScreenState extends ConsumerState<AdventureMapScreen> {
  String _subject = 'all';
  bool _checkedDevotional = false;
  String? _packing;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _maybeShowDevotional());
  }

  /// The devotional, once a day, over the map — as the website shows it.
  Future<void> _maybeShowDevotional() async {
    if (_checkedDevotional) return;
    _checkedDevotional = true;

    final Extras extras;

    try {
      extras = await ref.read(extrasProvider.future);
    } catch (_) {
      return;
    }

    final devotional = extras.devotionalFor(DateTime.now());

    if (!extras.devotionalEnabled || devotional == null) return;

    final today = DateTime.now();
    final key = 'devotional_seen:${today.year}-${today.month}-${today.day}';
    final progress = ref.read(progressDaoProvider);

    if (await progress.flag(key) || !mounted) return;

    await progress.setFlag(key);

    if (!mounted) return;

    await showDialog<void>(
      context: context,
      barrierColor: Tw.slate950.withValues(alpha: 0.6),
      builder: (context) => DevotionalDialog(devotional: devotional),
    );
  }

  /// Open a mission, fetching its world first if this device does not have it.
  Future<void> _openMission(Child child, SiteWorld world, SiteMission mission) async {
    final screenTime = ref.read(screenTimeProvider(child)).value;

    if (screenTime?.isOver ?? false) {
      context.go('/time-up');
      return;
    }

    final content = ref.read(contentDaoProvider);
    final packId = world.packId;

    if (packId != null) {
      final installed = await content.installedVersion(packId);
      final wanted = world.packVersion ?? 0;

      if (installed == null || installed < wanted) {
        setState(() => _packing = world.name);

        try {
          final repository = ref.read(contentRepositoryProvider);

          await repository.download(
            PackSummary(packId: packId, version: wanted, name: world.name, sha256: world.packSha256),
            // Questions first so the child can start; pictures and sounds follow.
            withMedia: false,
          );

          unawaited(repository.downloadMedia(packId).catchError((_) {}));
        } on ApiException catch (error) {
          if (mounted) {
            setState(() => _packing = null);
            _say(error.isOffline
                ? 'This world needs the internet the first time. Once it is open, it works offline.'
                : error.message);
          }
          return;
        }

        if (!mounted) return;
        setState(() => _packing = null);
      }
    }

    if (await content.mission(mission.id) == null) {
      _say('Leo is still packing this mission. Try again in a little while!');
      return;
    }

    if (mounted) context.go('/mission/${mission.id}');
  }

  void _say(String message) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message)));
  }

  Future<void> _songsLocked() {
    return showDialog<void>(
      context: context,
      builder: (context) => AlertDialog(
        content: Text(
          '🔒 Leo says: Complete at least 1 mission today to unlock your Music & Songs Hub! 🚀',
          style: Tw.text(Tw.sm, color: Tw.slate800),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.of(context).pop(), child: const Text('OK')),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final session = ref.watch(sessionProvider);
    final child = session.activeChild;

    if (child == null) {
      return SiteScaffold(
        decoration: const BoxDecoration(color: Tw.sky100),
        child: Center(
          child: FilledButton(
            onPressed: () => context.go('/profiles'),
            child: const Text('Choose an explorer'),
          ),
        ),
      );
    }

    final mapAsync = ref.watch(siteMapProvider(child));
    final screenTime = ref.watch(screenTimeProvider(child)).value;

    return SiteScaffold(
      safeTop: false,
      onBack: () {
        ref.read(sessionProvider.notifier).leaveChild();
        context.go('/profiles');
      },
      decoration: const BoxDecoration(color: Tw.sky100),
      child: Stack(
        children: [
          Positioned.fill(
            child: mapAsync.when(
              loading: () => const _CanvasMessage(emoji: '🗺️', text: 'Rolling out the map…'),
              error: (error, _) => _CanvasMessage(
                emoji: '🧭',
                text: 'The map got stuck. $error',
                action: () => ref.invalidate(siteMapProvider(child)),
              ),
              data: (map) => _MapBody(
                child: child,
                map: map,
                subject: _subject,
                songsUnlocked: map.songsUnlocked || (screenTime?.isOver ?? false),
                onSubject: (value) => setState(() => _subject = value),
                onSongs: () => context.go('/songs'),
                onSongsLocked: _songsLocked,
                onShop: () => context.go('/shop'),
                onMission: (world, mission) => _openMission(child, world, mission),
                onRefresh: () async => ref.invalidate(siteMapProvider(child)),
              ),
            ),
          ),

          // The fixed exit bar.
          Positioned(
            top: 0,
            left: 0,
            right: 0,
            child: SiteExitBar(
              onMap: true,
              title: 'Adventure Map',
              stars: child.totalStars,
              coins: child.starCoins,
              remainingMinutes: child.hasTimeLimit ? (screenTime?.minutesLeft ?? child.minutesLeftToday) : null,
              onHome: () {
                ref.read(sessionProvider.notifier).leaveChild();
                context.go('/profiles');
              },
              onShop: () => context.go('/shop'),
              onParentZone: () => context.go('/parent'),
            ),
          ),

          if (_packing != null) _PackingOverlay(worldName: _packing!),
        ],
      ),
    );
  }
}

class _MapBody extends StatelessWidget {
  const _MapBody({
    required this.child,
    required this.map,
    required this.subject,
    required this.songsUnlocked,
    required this.onSubject,
    required this.onSongs,
    required this.onSongsLocked,
    required this.onShop,
    required this.onMission,
    required this.onRefresh,
  });

  final Child child;
  final SiteMap map;
  final String subject;
  final bool songsUnlocked;
  final ValueChanged<String> onSubject;
  final VoidCallback onSongs;
  final VoidCallback onSongsLocked;
  final VoidCallback onShop;
  final void Function(SiteWorld world, SiteMission mission) onMission;
  final Future<void> Function() onRefresh;

  @override
  Widget build(BuildContext context) {
    final sm = Tw.isSm(context);
    final top = MediaQuery.paddingOf(context).top;
    final worlds = map.forSubject(subject);

    return RefreshIndicator(
      onRefresh: onRefresh,
      edgeOffset: top + 60,
      child: SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        child: ConstrainedBox(
          constraints: BoxConstraints(minHeight: MediaQuery.sizeOf(context).height),
          child: CustomPaint(
            painter: const _MapCanvasPainter(),
            child: Stack(
              children: [
                // Floating clouds and scenery.
                Positioned(top: top + 112, left: 12, child: _Scenery('☁️', size: sm ? 48 : 36, opacity: 0.3, pulse: true)),
                Positioned(top: top + 192, right: 16, child: _Scenery('☁️', size: sm ? 60 : 48, opacity: 0.3, pulse: true)),
                Positioned(top: top + 480, left: 16, child: const _Scenery('🌴', size: 30, opacity: 0.25)),
                Positioned(top: top + 800, right: 16, child: const _Scenery('🦒', size: 30, opacity: 0.25)),

                Padding(
                  padding: EdgeInsets.fromLTRB(sm ? 16 : 12, top + (sm ? 96 : 80), sm ? 16 : 12, 112),
                  child: Center(
                    child: ConstrainedBox(
                      constraints: const BoxConstraints(maxWidth: 512),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          const SizedBox(height: 8),
                          _HeroCard(child: child, onShop: onShop),
                          const SizedBox(height: 16),
                          _SubjectTabs(
                            selected: subject,
                            songsUnlocked: songsUnlocked,
                            onSelect: onSubject,
                            onSongs: onSongs,
                            onSongsLocked: onSongsLocked,
                          ),
                          const SizedBox(height: 24),
                          if (map.worlds.isEmpty) _NoWorlds(level: child.level),
                          for (final world in worlds) ...[
                            _WorldCard(world: world),
                            const SizedBox(height: 20),
                            _MissionTrail(world: world, onMission: (mission) => onMission(world, mission)),
                            const SizedBox(height: 40),
                          ],
                          if (map.offline)
                            Text(
                              'Playing offline. Everything you do is saved and will upload later.',
                              textAlign: TextAlign.center,
                              style: Tw.text(11, color: Tw.slate600),
                            ),
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

/// `.map-canvas`: radial-gradient(circle at 50% 10%, #E0F2FE 0%, #D1FAE5 35%,
/// #FEF3C7 75%, #FEE2E2 100%), stretched over the whole scrolling page.
class _MapCanvasPainter extends CustomPainter {
  const _MapCanvasPainter();

  @override
  void paint(Canvas canvas, Size size) {
    final center = Offset(size.width * 0.5, size.height * 0.1);

    // CSS `circle` with no size is `farthest-corner`.
    final radius = [
      Offset.zero,
      Offset(size.width, 0),
      Offset(0, size.height),
      Offset(size.width, size.height),
    ].map((corner) => (corner - center).distance).reduce((a, b) => a > b ? a : b);

    final paint = Paint()
      ..shader = ui.Gradient.radial(
        center,
        radius,
        const [Tw.sky100, Tw.emerald100, Tw.amber100, Tw.red100],
        const [0, 0.35, 0.75, 1],
      );

    canvas.drawRect(Offset.zero & size, paint);
  }

  @override
  bool shouldRepaint(_MapCanvasPainter oldDelegate) => false;
}

class _HeroCard extends StatelessWidget {
  const _HeroCard({required this.child, required this.onShop});

  final Child child;
  final VoidCallback onShop;

  @override
  Widget build(BuildContext context) {
    final sm = Tw.isSm(context);
    final tile = sm ? 56.0 : 48.0;
    final hat = child.equippedHatEmoji;
    final streak = child.streakDays > 0 ? child.streakDays : 1;

    return Container(
      padding: EdgeInsets.all(sm ? 16 : 14),
      decoration: BoxDecoration(
        color: Tw.white.withValues(alpha: 0.95),
        borderRadius: BorderRadius.circular(sm ? Tw.rounded3xl : Tw.rounded2xl),
        border: Border.all(color: Tw.white, width: 2),
        boxShadow: Tw.shadowLg,
      ),
      child: Row(
        children: [
          Stack(
            clipBehavior: Clip.none,
            alignment: Alignment.topCenter,
            children: [
              Container(
                width: tile,
                height: tile,
                alignment: Alignment.center,
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(Tw.rounded2xl),
                  gradient: const LinearGradient(
                    begin: Alignment.bottomLeft,
                    end: Alignment.topRight,
                    colors: [Tw.amber400, Tw.yellow300],
                  ),
                  border: Border.all(color: Tw.amber200, width: 2),
                ),
                child: Text(child.avatarEmoji, style: TextStyle(fontSize: sm ? 30 : 24)),
              ),
              if (hat != null) Positioned(top: -12, child: Text(hat, style: const TextStyle(fontSize: 16))),
            ],
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(color: Tw.purple100, borderRadius: BorderRadius.circular(999)),
                      child: Text((child.level ?? 'PP1').toUpperCase(), style: Tw.label(9, color: Tw.purple800)),
                    ),
                    const SizedBox(width: 4),
                    const Text('✨', style: TextStyle(fontSize: 10)),
                  ],
                ),
                const SizedBox(height: 2),
                Text(
                  'Welcome, ${child.name}!',
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: Tw.display(sm ? Tw.lg : Tw.base, color: Tw.slate900),
                ),
                Text(
                  'Playing as ${child.avatarName}',
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: Tw.text(11, color: Tw.slate600),
                ),
              ],
            ),
          ),
          const SizedBox(width: 10),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 2),
                decoration: BoxDecoration(
                  color: Tw.amber50,
                  borderRadius: BorderRadius.circular(Tw.roundedXl),
                  border: Border.all(color: Tw.amber200),
                  boxShadow: Tw.shadowSm,
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const TwBounce(child: Text('🔥', style: TextStyle(fontSize: 12))),
                    const SizedBox(width: 4),
                    Text(
                      '$streak Day${streak > 1 ? 's' : ''}!',
                      style: Tw.text(11, color: Tw.amber900, weight: FontWeight.w900),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 4),
              KidFocusable(
                onPressed: onShop,
                semanticLabel: 'Shop',
                borderRadius: BorderRadius.circular(8),
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 2, vertical: 2),
                  child: Text('🛍️ Shop ➔', style: Tw.text(10, color: Tw.purple700, weight: FontWeight.w900)),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

/// The seven subject buttons.
class _SubjectTabs extends StatelessWidget {
  const _SubjectTabs({
    required this.selected,
    required this.songsUnlocked,
    required this.onSelect,
    required this.onSongs,
    required this.onSongsLocked,
  });

  final String selected;
  final bool songsUnlocked;
  final ValueChanged<String> onSelect;
  final VoidCallback onSongs;
  final VoidCallback onSongsLocked;

  static const List<({String key, String emoji, String label, Color fill, Color text, Color edge})> subjects = [
    (key: 'all', emoji: '🗺️', label: 'All', fill: Tw.indigo600, text: Tw.white, edge: Tw.indigo800),
    (key: 'math', emoji: '🔢', label: 'Math', fill: Tw.amber500, text: Tw.slate950, edge: Tw.amber700),
    (key: 'english', emoji: '📖', label: 'Phonics', fill: Tw.sky500, text: Tw.white, edge: Tw.sky700),
    (key: 'speak', emoji: '🎙️', label: 'Speak', fill: Tw.pink600, text: Tw.white, edge: Tw.pink800),
    (key: 'tracing', emoji: '✏️', label: 'Tracing', fill: Tw.purple600, text: Tw.white, edge: Tw.purple900),
    (key: 'cre', emoji: '✝️', label: 'Values', fill: Tw.emerald600, text: Tw.white, edge: Tw.emerald800),
  ];

  @override
  Widget build(BuildContext context) {
    final sm = Tw.isSm(context);
    final gap = sm ? 6.0 : 4.0;

    return Row(
      children: [
        for (final subject in subjects) ...[
          Expanded(
            child: _SubjectButton(
              emoji: subject.emoji,
              label: subject.label,
              active: selected == subject.key,
              fill: subject.fill,
              text: subject.text,
              edge: subject.edge,
              onPressed: () => onSelect(subject.key),
            ),
          ),
          SizedBox(width: gap),
        ],
        Expanded(
          child: songsUnlocked
              ? _SubjectButton(
                  emoji: '🎵',
                  label: 'Songs',
                  active: true,
                  gradient: const LinearGradient(
                    begin: Alignment.bottomLeft,
                    end: Alignment.topRight,
                    colors: [Tw.pink500, Tw.purple600],
                  ),
                  text: Tw.white,
                  edge: Tw.pink900,
                  bounceEmoji: true,
                  onPressed: onSongs,
                )
              : _SubjectButton(
                  emoji: '🔒',
                  label: 'Songs',
                  active: false,
                  locked: true,
                  onPressed: onSongsLocked,
                ),
        ),
      ],
    );
  }
}

class _SubjectButton extends StatelessWidget {
  const _SubjectButton({
    required this.emoji,
    required this.label,
    required this.active,
    required this.onPressed,
    this.fill,
    this.gradient,
    this.text = Tw.white,
    this.edge = Tw.slate300,
    this.locked = false,
    this.bounceEmoji = false,
  });

  final String emoji;
  final String label;
  final bool active;
  final VoidCallback onPressed;
  final Color? fill;
  final Gradient? gradient;
  final Color text;
  final Color edge;
  final bool locked;
  final bool bounceEmoji;

  @override
  Widget build(BuildContext context) {
    final sm = Tw.isSm(context);
    final glyph = Text(emoji, style: TextStyle(fontSize: sm ? 20 : 16));

    final Color background;
    final Color foreground;

    if (locked) {
      background = Tw.slate100;
      foreground = Tw.slate400;
    } else if (active) {
      background = fill ?? Tw.indigo600;
      foreground = text;
    } else {
      background = Tw.white;
      foreground = Tw.slate700;
    }

    return KidFocusable(
      onPressed: onPressed,
      semanticLabel: label,
      borderRadius: BorderRadius.circular(Tw.rounded2xl),
      child: AnimatedSlide(
        duration: const Duration(milliseconds: 150),
        offset: Offset(0, active && !locked ? -0.03 : 0),
        child: Opacity(
          opacity: locked ? 0.75 : 1,
          child: Container(
            padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 2),
            decoration: BoxDecoration(
              color: gradient == null ? background : null,
              gradient: active ? gradient : null,
              borderRadius: BorderRadius.circular(Tw.rounded2xl),
              border: active && !locked ? null : Border.all(color: Tw.slate200),
              boxShadow: active && !locked ? Tw.edge(edge) : Tw.shadowSm,
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                bounceEmoji ? TwBounce(child: glyph) : glyph,
                const SizedBox(height: 2),
                Text(
                  label,
                  maxLines: 1,
                  overflow: TextOverflow.fade,
                  softWrap: false,
                  style: Tw.text(sm ? 10 : 8, color: foreground, weight: FontWeight.w900, height: 1.25),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

/// A world's colours, by slug, exactly as the website's `$worldThemes`.
class _WorldTheme {
  const _WorldTheme(this.from, this.to, this.accentBackground, this.accentText, this.icon);

  final Color from;
  final Color to;
  final Color accentBackground;
  final Color accentText;
  final String icon;

  static const Map<String, _WorldTheme> bySlug = {
    'whispering-forest': _WorldTheme(Tw.emerald500, Tw.teal700, Tw.emerald100, Tw.emerald900, '🌲'),
    'safari-plains': _WorldTheme(Tw.amber500, Tw.orange600, Tw.amber100, Tw.amber950, '🦁'),
    'ocean-cove': _WorldTheme(Tw.blue500, Tw.indigo700, Tw.blue100, Tw.blue900, '🌊'),
    'castle-of-discovery': _WorldTheme(Tw.purple500, Tw.pink600, Tw.purple100, Tw.purple950, '🏰'),
  };

  static const _WorldTheme fallback = _WorldTheme(Tw.purple600, Tw.indigo700, Tw.purple100, Tw.purple900, '⭐');

  static _WorldTheme of(String? slug) => bySlug[slug] ?? fallback;
}

class _WorldCard extends StatelessWidget {
  const _WorldCard({required this.world});

  final SiteWorld world;

  @override
  Widget build(BuildContext context) {
    final sm = Tw.isSm(context);
    final theme = _WorldTheme.of(world.slug);
    final icon = (world.icon?.isNotEmpty ?? false) ? world.icon! : theme.icon;
    final radius = BorderRadius.circular(sm ? Tw.rounded3xl : Tw.rounded2xl);

    return Container(
      decoration: BoxDecoration(
        borderRadius: radius,
        gradient: LinearGradient(colors: [theme.from, theme.to]),
        border: Border.all(color: Tw.white, width: 2),
        boxShadow: Tw.shadowXl,
      ),
      child: ClipRRect(
        borderRadius: radius,
        child: Stack(
          children: [
            Positioned(
              right: -16,
              bottom: -16,
              child: IgnorePointer(
                child: Opacity(opacity: 0.25, child: Text(icon, style: TextStyle(fontSize: sm ? 72 : 60))),
              ),
            ),
            Padding(
              padding: EdgeInsets.all(sm ? 20 : 16),
              child: Row(
                children: [
                  Text(
                    icon,
                    style: TextStyle(
                      fontSize: sm ? 36 : 30,
                      shadows: const [Shadow(color: Color(0x33000000), blurRadius: 3, offset: Offset(0, 1))],
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 2),
                          decoration: BoxDecoration(
                            color: theme.accentBackground,
                            borderRadius: BorderRadius.circular(999),
                          ),
                          child: Text(
                            (world.subjectName ?? 'CBC World').toUpperCase(),
                            style: Tw.label(sm ? 10 : 9, color: theme.accentText),
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          world.name,
                          style: Tw.display(sm ? Tw.xl : Tw.lg, color: Tw.white, height: 1.2),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          '${world.completedCount} / ${world.missions.isEmpty ? 1 : world.missions.length} Missions Mastered ⭐',
                          style: Tw.text(11, color: Tw.white.withValues(alpha: 0.8)),
                        ),
                      ],
                    ),
                  ),
                  const Text('🏆', style: TextStyle(fontSize: 24)),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

/// The winding stepping-stone path: centre, left, centre, right.
class _MissionTrail extends StatelessWidget {
  const _MissionTrail({required this.world, required this.onMission});

  final SiteWorld world;
  final ValueChanged<SiteMission> onMission;

  @override
  Widget build(BuildContext context) {
    final sm = Tw.isSm(context);
    final inset = sm ? 48.0 : 24.0;

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Column(
        children: [
          for (var i = 0; i < world.missions.length; i++) ...[
            if (i > 0) ...[
              const SizedBox(height: 8),
              const _TrailLine(),
              const SizedBox(height: 8),
            ],
            Padding(
              padding: EdgeInsets.only(
                left: i % 4 == 1 ? inset : 0,
                right: i % 4 == 3 ? inset : 0,
              ),
              child: Align(
                alignment: switch (i % 4) {
                  1 => Alignment.centerLeft,
                  3 => Alignment.centerRight,
                  _ => Alignment.center,
                },
                child: world.missions[i].isCompleted
                    ? _CompletedNode(
                        mission: world.missions[i],
                        level: i + 1,
                        onPressed: () => onMission(world.missions[i]),
                      )
                    : _PlayNode(
                        mission: world.missions[i],
                        level: i + 1,
                        onPressed: () => onMission(world.missions[i]),
                      ),
              ),
            ),
          ],
        ],
      ),
    );
  }
}

/// `.trail-line`: a 6px column of emerald dashes, 6 on and 8 off.
class _TrailLine extends StatelessWidget {
  const _TrailLine();

  @override
  Widget build(BuildContext context) {
    return Opacity(
      opacity: 0.6,
      child: CustomPaint(size: const Size(6, 38), painter: _DashPainter()),
    );
  }
}

class _DashPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()..color = Tw.emerald500;

    for (var y = 0.0; y < size.height; y += 14) {
      canvas.drawRect(Rect.fromLTWH(0, y, size.width, (size.height - y).clamp(0, 6)), paint);
    }
  }

  @override
  bool shouldRepaint(_DashPainter oldDelegate) => false;
}

class _CompletedNode extends StatelessWidget {
  const _CompletedNode({required this.mission, required this.level, required this.onPressed});

  final SiteMission mission;
  final int level;
  final VoidCallback onPressed;

  @override
  Widget build(BuildContext context) {
    final sm = Tw.isSm(context);
    final size = sm ? 84.0 : 72.0;

    return KidFocusable(
      onPressed: onPressed,
      semanticLabel: '${mission.title}, finished, ${mission.stars} stars',
      borderRadius: BorderRadius.circular(size),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: size,
            height: size,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              gradient: const LinearGradient(
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
                colors: [Tw.emerald400, Tw.emerald600],
              ),
              border: Border.all(color: Tw.white, width: 4),
              boxShadow: const [
                BoxShadow(color: Tw.emerald700, offset: Offset(0, 6)),
                BoxShadow(color: Color(0x4D059669), blurRadius: 18, offset: Offset(0, 10)),
              ],
            ),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text('✓', style: Tw.text(sm ? Tw.x2l : Tw.xl, color: Tw.white, weight: FontWeight.w900, height: 1)),
                const SizedBox(height: 2),
                Text('Lv $level', style: Tw.text(9, color: Tw.white, weight: FontWeight.w900, height: 1)),
              ],
            ),
          ),
          const SizedBox(height: 4),
          Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              for (var star = 1; star <= 3; star++)
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 1),
                  child: _Star(earned: mission.stars >= star),
                ),
            ],
          ),
          const SizedBox(height: 2),
          ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 120),
            child: Text(
              mission.title,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              textAlign: TextAlign.center,
              style: Tw.text(11, color: Tw.slate800, weight: FontWeight.w900),
            ),
          ),
        ],
      ),
    );
  }
}

/// A star that is gold when earned and a pale outline of itself when not —
/// what the website's `text-amber-400` / `text-slate-300` is reaching for.
class _Star extends StatelessWidget {
  const _Star({required this.earned});

  final bool earned;

  @override
  Widget build(BuildContext context) {
    const star = Text('⭐', style: TextStyle(fontSize: 12));

    if (earned) return star;

    return const Opacity(
      opacity: 0.35,
      child: ColorFiltered(
        colorFilter: ColorFilter.matrix(<double>[
          0.2126, 0.7152, 0.0722, 0, 0,
          0.2126, 0.7152, 0.0722, 0, 0,
          0.2126, 0.7152, 0.0722, 0, 0,
          0, 0, 0, 1, 0,
        ]),
        child: star,
      ),
    );
  }
}

class _PlayNode extends StatelessWidget {
  const _PlayNode({required this.mission, required this.level, required this.onPressed});

  final SiteMission mission;
  final int level;
  final VoidCallback onPressed;

  @override
  Widget build(BuildContext context) {
    final sm = Tw.isSm(context);
    final size = sm ? 84.0 : 72.0;

    return KidFocusable(
      onPressed: onPressed,
      semanticLabel: '${mission.title}, play',
      borderRadius: BorderRadius.circular(size),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Stack(
            clipBehavior: Clip.none,
            alignment: Alignment.topCenter,
            children: [
              Padding(
                padding: const EdgeInsets.only(top: 14),
                child: TwBreathe(
                  child: Container(
                    width: size,
                    height: size,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      gradient: const LinearGradient(
                        begin: Alignment.topCenter,
                        end: Alignment.bottomCenter,
                        colors: [Tw.amber400, Tw.amber500],
                      ),
                      border: Border.all(color: Tw.white, width: 4),
                      boxShadow: const [
                        BoxShadow(color: Tw.amber600, offset: Offset(0, 7)),
                        BoxShadow(color: Color(0x99F59E0B), blurRadius: 20),
                      ],
                    ),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Text('⭐', style: TextStyle(fontSize: sm ? 24 : 20, height: 1)),
                        const SizedBox(height: 2),
                        Text('Lv $level', style: Tw.text(9, color: Tw.white, weight: FontWeight.w900, height: 1)),
                      ],
                    ),
                  ),
                ),
              ),
              // The "tap to play" bubble.
              Positioned(
                top: -10,
                child: TwBounce(
                  distance: 6,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 2),
                    decoration: BoxDecoration(
                      color: Tw.amber500,
                      borderRadius: BorderRadius.circular(999),
                      border: Border.all(color: Tw.white),
                      boxShadow: Tw.shadowMd,
                    ),
                    child: Text('PLAY! 🚀', style: Tw.text(9, color: Tw.slate950, weight: FontWeight.w900)),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),
          Container(
            constraints: const BoxConstraints(maxWidth: 130),
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
            decoration: BoxDecoration(
              color: Tw.white.withValues(alpha: 0.95),
              borderRadius: BorderRadius.circular(999),
              border: Border.all(color: Tw.amber200),
              boxShadow: Tw.shadowSm,
            ),
            child: Text(
              mission.title,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              textAlign: TextAlign.center,
              style: Tw.text(11, color: Tw.amber900, weight: FontWeight.w900),
            ),
          ),
        ],
      ),
    );
  }
}

class _NoWorlds extends StatelessWidget {
  const _NoWorlds({this.level});

  final String? level;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Container(
        constraints: const BoxConstraints(maxWidth: 384),
        margin: const EdgeInsets.symmetric(vertical: 32),
        padding: const EdgeInsets.all(32),
        decoration: BoxDecoration(
          color: Tw.white.withValues(alpha: 0.9),
          borderRadius: BorderRadius.circular(Tw.rounded3xl),
          border: Border.all(color: Tw.purple200, width: 2),
          boxShadow: Tw.shadowSm,
        ),
        child: Column(
          children: [
            const TwBounce(child: Text('🏝️', style: TextStyle(fontSize: 48))),
            const SizedBox(height: 12),
            Text('No Worlds For This Level Yet', textAlign: TextAlign.center, style: Tw.display(Tw.base, color: Tw.slate800)),
            const SizedBox(height: 4),
            Text.rich(
              TextSpan(
                text: 'Adventure worlds for ',
                style: Tw.text(Tw.xs, color: Tw.slate500),
                children: [
                  TextSpan(text: level ?? 'your level', style: const TextStyle(color: Tw.purple700, fontWeight: FontWeight.w900)),
                  const TextSpan(text: ' are coming soon!'),
                ],
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}

class _Scenery extends StatelessWidget {
  const _Scenery(this.emoji, {required this.size, required this.opacity, this.pulse = false});

  final String emoji;
  final double size;
  final double opacity;
  final bool pulse;

  @override
  Widget build(BuildContext context) {
    final glyph = IgnorePointer(
      child: Opacity(opacity: opacity, child: Text(emoji, style: TextStyle(fontSize: size))),
    );

    return pulse ? TwPulse(child: glyph) : glyph;
  }
}

class _CanvasMessage extends StatelessWidget {
  const _CanvasMessage({required this.emoji, required this.text, this.action});

  final String emoji;
  final String text;
  final VoidCallback? action;

  @override
  Widget build(BuildContext context) {
    return CustomPaint(
      painter: const _MapCanvasPainter(),
      child: Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TwBounce(child: Text(emoji, style: const TextStyle(fontSize: 48))),
              const SizedBox(height: 12),
              Text(text, textAlign: TextAlign.center, style: Tw.display(Tw.base, color: Tw.slate800)),
              if (action != null) ...[
                const SizedBox(height: 12),
                FilledButton(onPressed: action, child: const Text('Try again')),
              ],
            ],
          ),
        ),
      ),
    );
  }
}

class _PackingOverlay extends StatelessWidget {
  const _PackingOverlay({required this.worldName});

  final String worldName;

  @override
  Widget build(BuildContext context) {
    return Positioned.fill(
      child: ColoredBox(
        color: Tw.slate950.withValues(alpha: 0.45),
        child: Center(
          child: Container(
            margin: const EdgeInsets.all(32),
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              color: Tw.white,
              borderRadius: BorderRadius.circular(Tw.rounded3xl),
              border: Border.all(color: Tw.amber300, width: 4),
              boxShadow: Tw.shadow2xl,
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const TwBounce(child: Text('🎒', style: TextStyle(fontSize: 48))),
                const SizedBox(height: 12),
                Text('Packing your adventure…', style: Tw.display(Tw.lg)),
                const SizedBox(height: 4),
                Text(worldName, textAlign: TextAlign.center, style: Tw.text(Tw.xs, color: Tw.slate600)),
                const SizedBox(height: 16),
                const SizedBox(width: 160, child: LinearProgressIndicator(color: Tw.amber500, backgroundColor: Tw.amber100)),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

/// The morning devotional, over the map — the website's pop-up, exactly.
class DevotionalDialog extends ConsumerStatefulWidget {
  const DevotionalDialog({super.key, required this.devotional});

  final Devotional devotional;

  @override
  ConsumerState<DevotionalDialog> createState() => _DevotionalDialogState();
}

class _DevotionalDialogState extends ConsumerState<DevotionalDialog> {
  bool _playing = false;
  Timer? _autoplay;

  @override
  void initState() {
    super.initState();
    // The website starts reading 800ms after the pop-up opens.
    _autoplay = Timer(const Duration(milliseconds: 800), _toggle);
  }

  @override
  void dispose() {
    _autoplay?.cancel();
    super.dispose();
  }

  Future<void> _toggle() async {
    final audio = ref.read(audioDirectorProvider);

    if (_playing) {
      await audio.stop();
      if (mounted) setState(() => _playing = false);
      return;
    }

    setState(() => _playing = true);
    await audio.say(text: widget.devotional.spoken);
  }

  Future<void> _close() async {
    _autoplay?.cancel();
    await ref.read(audioDirectorProvider).stop();
    if (mounted) Navigator.of(context).pop();
  }

  @override
  Widget build(BuildContext context) {
    final sm = Tw.isSm(context);
    final devotional = widget.devotional;

    return Dialog(
      backgroundColor: Colors.transparent,
      insetPadding: const EdgeInsets.all(16),
      child: Container(
        constraints: const BoxConstraints(maxWidth: 384),
        padding: EdgeInsets.all(sm ? 24 : 20),
        decoration: BoxDecoration(
          color: Tw.white,
          borderRadius: BorderRadius.circular(Tw.rounded3xl),
          border: Border.all(color: Tw.amber300, width: 4),
          boxShadow: Tw.shadow2xl,
        ),
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              KidFocusable(
                onPressed: _toggle,
                semanticLabel: 'Tap to listen to verse and prayer',
                borderRadius: BorderRadius.circular(Tw.rounded2xl),
                child: Stack(
                  clipBehavior: Clip.none,
                  children: [
                    Container(
                      width: 64,
                      height: 64,
                      alignment: Alignment.center,
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(Tw.rounded2xl),
                        gradient: const LinearGradient(
                          begin: Alignment.bottomLeft,
                          end: Alignment.topRight,
                          colors: [Tw.amber400, Tw.yellow300],
                        ),
                        border: Border.all(color: Tw.amber200, width: 2),
                        boxShadow: Tw.shadowMd,
                      ),
                      child: Text(_playing ? '🔊' : '🔈', style: const TextStyle(fontSize: 30)),
                    ),
                    if (_playing)
                      Positioned(
                        top: -4,
                        right: -4,
                        child: SizedBox(
                          width: 16,
                          height: 16,
                          child: Stack(
                            alignment: Alignment.center,
                            children: [
                              const TwPing(color: Tw.emerald400, size: 16),
                              Container(
                                width: 16,
                                height: 16,
                                alignment: Alignment.center,
                                decoration: const BoxDecoration(color: Tw.emerald500, shape: BoxShape.circle),
                                child: Text('♪', style: Tw.text(9, color: Tw.white)),
                              ),
                            ],
                          ),
                        ),
                      ),
                  ],
                ),
              ),
              const SizedBox(height: 8),
              KidFocusable(
                onPressed: _toggle,
                semanticLabel: 'Listen out loud',
                borderRadius: BorderRadius.circular(999),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                  decoration: BoxDecoration(color: Tw.purple100, borderRadius: BorderRadius.circular(999)),
                  child: Text(
                    _playing ? '🔊 SPEAKING VERSE & PRAYER...' : '🔊 TAP SPEAKER TO LISTEN OUT LOUD',
                    style: Tw.label(10, color: Tw.purple900),
                  ),
                ),
              ),
              const SizedBox(height: 12),
              Text(
                '"${devotional.verseText}"',
                textAlign: TextAlign.center,
                style: Tw.display(sm ? Tw.lg : Tw.base, color: Tw.slate900, height: 1.35),
              ),
              const SizedBox(height: 4),
              Text('— ${devotional.verseRef}', style: Tw.text(Tw.xs, color: Tw.purple700, weight: FontWeight.w900)),
              const SizedBox(height: 12),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Tw.amber50,
                  borderRadius: BorderRadius.circular(Tw.rounded2xl),
                  border: Border.all(color: Tw.amber200),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text.rich(
                      TextSpan(
                        text: '✨ ',
                        style: Tw.text(Tw.xs, color: Tw.amber950, height: 1.6),
                        children: [
                          const TextSpan(text: "Today's Thought:", style: TextStyle(fontWeight: FontWeight.w900)),
                          TextSpan(text: ' ${devotional.teaching}'),
                        ],
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text.rich(
                      TextSpan(
                        text: '🙏 ',
                        style: Tw.text(Tw.xs, color: Tw.purple900, height: 1.6),
                        children: [
                          const TextSpan(text: 'Short Prayer:', style: TextStyle(fontWeight: FontWeight.w900)),
                          TextSpan(text: ' "${devotional.prayer}"'),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 12),
              KidFocusable(
                onPressed: _close,
                autofocus: context.formFactor.isTv,
                semanticLabel: 'Start Learning Adventure',
                borderRadius: BorderRadius.circular(Tw.rounded2xl),
                child: Container(
                  width: double.infinity,
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  alignment: Alignment.center,
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(Tw.rounded2xl),
                    gradient: const LinearGradient(colors: [Tw.emerald500, Tw.teal600]),
                    boxShadow: Tw.shadowMd,
                  ),
                  child: Text('Start Learning Adventure! 🚀', style: Tw.text(Tw.sm, color: Tw.white, weight: FontWeight.w900)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
