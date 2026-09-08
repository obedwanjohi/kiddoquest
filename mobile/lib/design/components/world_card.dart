import 'dart:math' as math;

import 'package:flutter/material.dart';

import '../../core/platform/form_factor.dart';
import '../tokens.dart';
import 'focus_ring.dart';
import 'progress_bar.dart';

/// How a world is doing on this device.
enum WorldDownloadState { notDownloaded, downloading, ready, updateAvailable }

/// A biome on the adventure map.
///
/// The website draws these as gradient cards with a serpentine trail of mission
/// nodes. That idea is kept; what changes is that the trail is drawn properly,
/// laid out for the shape of the screen, and every node is focusable so it works
/// from a sofa with a remote.
class WorldCard extends StatelessWidget {
  const WorldCard({
    super.key,
    required this.name,
    required this.palette,
    required this.missions,
    this.icon,
    this.subtitle,
    this.downloadState = WorldDownloadState.ready,
    this.downloadProgress,
    this.locked = false,
    this.onDownload,
    this.onMissionTap,
  });

  final String name;
  final WorldPalette palette;
  final List<MissionNodeData> missions;
  final String? icon;
  final String? subtitle;
  final WorldDownloadState downloadState;
  final double? downloadProgress;
  final bool locked;
  final VoidCallback? onDownload;
  final void Function(MissionNodeData mission)? onMissionTap;

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);
    final earned = missions.fold<int>(0, (sum, m) => sum + m.stars);
    final possible = missions.length * 3;

    return Container(
      padding: EdgeInsets.all(KidSpacing.md * formFactor.density),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [palette.base, palette.accent],
        ),
        borderRadius: KidRadius.card,
        boxShadow: KidShadows.soft,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Text(icon ?? '🗺️', style: TextStyle(fontSize: 30 * formFactor.density)),
              SizedBox(width: KidSpacing.sm * formFactor.density),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      name,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: theme.textTheme.titleLarge?.copyWith(color: Colors.white),
                    ),
                    if (subtitle != null)
                      Text(
                        subtitle!,
                        style: theme.textTheme.labelLarge?.copyWith(color: Colors.white.withValues(alpha: 0.85)),
                      ),
                  ],
                ),
              ),
              if (possible > 0)
                Container(
                  padding: EdgeInsets.symmetric(
                    horizontal: KidSpacing.sm * formFactor.density,
                    vertical: 4 * formFactor.density,
                  ),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.22),
                    borderRadius: KidRadius.pill,
                  ),
                  child: Text(
                    '⭐ $earned/$possible',
                    style: theme.textTheme.labelLarge?.copyWith(color: Colors.white),
                  ),
                ),
            ],
          ),
          SizedBox(height: KidSpacing.md * formFactor.density),
          if (downloadState != WorldDownloadState.ready)
            _DownloadStrip(
              state: downloadState,
              progress: downloadProgress,
              onDownload: onDownload,
            )
          else if (missions.isEmpty)
            Padding(
              padding: EdgeInsets.symmetric(vertical: KidSpacing.md * formFactor.density),
              child: Text(
                'New missions are on the way.',
                style: theme.textTheme.bodyLarge?.copyWith(color: Colors.white),
              ),
            )
          else
            MissionTrail(
              missions: missions,
              palette: palette,
              onMissionTap: onMissionTap,
            ),
        ],
      ),
    );
  }
}

class _DownloadStrip extends StatelessWidget {
  const _DownloadStrip({required this.state, this.progress, this.onDownload});

  final WorldDownloadState state;
  final double? progress;
  final VoidCallback? onDownload;

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);

    final label = switch (state) {
      WorldDownloadState.notDownloaded => 'Tap to pack this world for offline play',
      WorldDownloadState.downloading => 'Packing your adventure…',
      WorldDownloadState.updateAvailable => 'New missions ready to download',
      WorldDownloadState.ready => '',
    };

    return KidFocusable(
      onPressed: state == WorldDownloadState.downloading ? null : onDownload,
      borderRadius: KidRadius.button,
      semanticLabel: label,
      child: Container(
        width: double.infinity,
        padding: EdgeInsets.all(KidSpacing.md * formFactor.density),
        decoration: BoxDecoration(
          color: Colors.white.withValues(alpha: 0.18),
          borderRadius: KidRadius.button,
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(
                  state == WorldDownloadState.downloading ? Icons.downloading_rounded : Icons.download_rounded,
                  color: Colors.white,
                ),
                SizedBox(width: KidSpacing.sm * formFactor.density),
                Expanded(
                  child: Text(label, style: theme.textTheme.bodyLarge?.copyWith(color: Colors.white)),
                ),
              ],
            ),
            if (state == WorldDownloadState.downloading) ...[
              SizedBox(height: KidSpacing.sm * formFactor.density),
              ClipRRect(
                borderRadius: KidRadius.pill,
                child: LinearProgressIndicator(
                  value: progress,
                  minHeight: 8,
                  backgroundColor: Colors.white24,
                  valueColor: const AlwaysStoppedAnimation(Colors.white),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

/// One stop on a world's path.
class MissionNodeData {
  const MissionNodeData({
    required this.id,
    required this.title,
    required this.stars,
    required this.state,
    this.isFocus = false,
  });

  final int id;
  final String title;
  final int stars;
  final MissionNodeState state;

  /// The mission a parent picked as tomorrow's focus.
  final bool isFocus;
}

enum MissionNodeState { completed, active, locked }

/// The stepping-stone trail. A vertical serpentine on a phone, a horizontal
/// path on a television so a family can read it from the sofa.
class MissionTrail extends StatelessWidget {
  const MissionTrail({
    super.key,
    required this.missions,
    required this.palette,
    this.onMissionTap,
  });

  final List<MissionNodeData> missions;
  final WorldPalette palette;
  final void Function(MissionNodeData mission)? onMissionTap;

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;

    if (formFactor.isTv) {
      return SizedBox(
        height: 150,
        child: ListView.separated(
          scrollDirection: Axis.horizontal,
          itemCount: missions.length,
          separatorBuilder: (_, _) => const SizedBox(width: KidSpacing.md),
          itemBuilder: (context, index) => MissionNode(
            data: missions[index],
            index: index,
            palette: palette,
            onTap: onMissionTap,
          ),
        ),
      );
    }

    // Phone and tablet: wrap the nodes and offset alternate rows so the path
    // reads as a winding trail rather than a table.
    return Wrap(
      spacing: KidSpacing.md * formFactor.density,
      runSpacing: KidSpacing.md * formFactor.density,
      children: [
        for (var i = 0; i < missions.length; i++)
          Transform.translate(
            offset: Offset(0, math.sin(i * 0.9) * 10),
            child: MissionNode(
              data: missions[i],
              index: i,
              palette: palette,
              onTap: onMissionTap,
            ),
          ),
      ],
    );
  }
}

class MissionNode extends StatelessWidget {
  const MissionNode({
    super.key,
    required this.data,
    required this.index,
    required this.palette,
    this.onTap,
  });

  final MissionNodeData data;
  final int index;
  final WorldPalette palette;
  final void Function(MissionNodeData mission)? onTap;

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);
    final diameter = (formFactor.isTv ? 96.0 : 72.0) * (formFactor.isTv ? 1.0 : formFactor.density);

    final (fill, edge, glyph) = switch (data.state) {
      MissionNodeState.completed => (KidColors.success, const Color(0xFF15803D), '✓'),
      MissionNodeState.active => (KidColors.amber, const Color(0xFFB45309), '🚀'),
      MissionNodeState.locked => (const Color(0xFFE5E7EB), const Color(0xFFC7CBD1), '🔒'),
    };

    return SizedBox(
      width: diameter + 24,
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          KidFocusable(
            onPressed: data.state == MissionNodeState.locked ? null : () => onTap?.call(data),
            enabled: data.state != MissionNodeState.locked,
            borderRadius: KidRadius.pill,
            semanticLabel: '${data.title}, ${data.state.name}',
            child: _PulseWhenActive(
              active: data.state == MissionNodeState.active,
              child: Container(
                width: diameter,
                height: diameter,
                alignment: Alignment.center,
                decoration: BoxDecoration(
                  color: fill,
                  shape: BoxShape.circle,
                  boxShadow: KidShadows.edge(edge),
                  border: data.isFocus ? Border.all(color: Colors.white, width: 3) : null,
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(glyph, style: TextStyle(fontSize: 24 * formFactor.density)),
                    if (data.state == MissionNodeState.completed)
                      StarRow(stars: data.stars, size: 11),
                  ],
                ),
              ),
            ),
          ),
          SizedBox(height: KidSpacing.xs * formFactor.density),
          Text(
            data.isFocus ? '📌 ${data.title}' : data.title,
            maxLines: 2,
            textAlign: TextAlign.center,
            overflow: TextOverflow.ellipsis,
            style: theme.textTheme.labelSmall?.copyWith(color: Colors.white),
          ),
        ],
      ),
    );
  }
}

/// The one node a child should tap next breathes gently. Nothing else on the
/// map moves, so the eye goes straight to it.
class _PulseWhenActive extends StatefulWidget {
  const _PulseWhenActive({required this.active, required this.child});

  final bool active;
  final Widget child;

  @override
  State<_PulseWhenActive> createState() => _PulseWhenActiveState();
}

class _PulseWhenActiveState extends State<_PulseWhenActive> with SingleTickerProviderStateMixin {
  late final AnimationController _controller = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 1400),
  );

  @override
  void initState() {
    super.initState();
    if (widget.active) _controller.repeat(reverse: true);
  }

  @override
  void didUpdateWidget(_PulseWhenActive oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.active && !_controller.isAnimating) {
      _controller.repeat(reverse: true);
    } else if (!widget.active && _controller.isAnimating) {
      _controller.stop();
      _controller.value = 0;
    }
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (!widget.active || (MediaQuery.maybeDisableAnimationsOf(context) ?? false)) {
      return widget.child;
    }

    return ScaleTransition(
      scale: Tween<double>(begin: 1.0, end: 1.06).animate(
        CurvedAnimation(parent: _controller, curve: Curves.easeInOut),
      ),
      child: widget.child,
    );
  }
}
