import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:video_player/video_player.dart';

import '../../app/providers.dart';
import '../../core/platform/form_factor.dart';
import '../../core/platform/local_files.dart';
import '../../design/components/kid_button.dart';
import '../../design/components/kid_scaffold.dart';
import '../../design/components/mascot_stage.dart';
import '../../design/tokens.dart';

/// The short film before a mission.
///
/// Skippable from the first second, deliberately: a child on their fifth run at
/// the same mission should not have to sit through the same ninety seconds, and
/// on a television the remote must always have somewhere to go. Whatever
/// happens — no file, a codec the device will not play, a download that never
/// finished — the child ends up in the mission rather than stuck here.
class MissionVideoScreen extends ConsumerStatefulWidget {
  const MissionVideoScreen({super.key, required this.missionId});

  final int missionId;

  @override
  ConsumerState<MissionVideoScreen> createState() => _MissionVideoScreenState();
}

class _MissionVideoScreenState extends ConsumerState<MissionVideoScreen> {
  VideoPlayerController? _controller;
  bool _loading = true;
  bool _failed = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _load());
  }

  Future<void> _load() async {
    try {
      final mission = await ref.read(contentDaoProvider).mission(widget.missionId);
      final key = mission?['video_path'] as String?;
      final media = key == null ? null : await ref.read(contentDaoProvider).media(key);

      if (media == null || !mounted) {
        _skip();
        return;
      }

      final controller = media.hasFile && supportsLocalFiles
          ? localFileVideo(media.localPath!)
          : VideoPlayerController.networkUrl(Uri.parse(media.url));

      await controller.initialize();
      await controller.setLooping(false);
      await controller.play();

      controller.addListener(_watchForTheEnd);

      if (!mounted) {
        await controller.dispose();
        return;
      }

      setState(() {
        _controller = controller;
        _loading = false;
      });
    } catch (_) {
      if (mounted) {
        setState(() {
          _loading = false;
          _failed = true;
        });
      }
    }
  }

  void _watchForTheEnd() {
    final controller = _controller;

    if (controller == null) return;

    final value = controller.value;

    if (value.isInitialized && !value.isPlaying && value.position >= value.duration) {
      _skip();
    }
  }

  void _skip() {
    if (!mounted) return;
    context.go('/mission/${widget.missionId}/play');
  }

  @override
  void dispose() {
    _controller?.removeListener(_watchForTheEnd);
    _controller?.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final controller = _controller;
    final formFactor = context.formFactor;

    if (_failed) {
      return KidScaffold(
        child: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const MascotStage(mood: MascotMood.thinking),
              SizedBox(height: KidSpacing.md * formFactor.density),
              Text(
                'The film will not play on this device.',
                style: Theme.of(context).textTheme.titleLarge,
              ),
              SizedBox(height: KidSpacing.md * formFactor.density),
              KidButton(
                label: 'Start the mission',
                autofocus: true,
                onPressed: _skip,
              ),
            ],
          ),
        ),
      );
    }

    return KidScaffold(
      background: Colors.black,
      padContent: false,
      onBack: _skip,
      child: Stack(
        alignment: Alignment.center,
        children: [
          if (_loading || controller == null)
            const CircularProgressIndicator()
          else
            Center(
              child: AspectRatio(
                aspectRatio: controller.value.aspectRatio,
                child: VideoPlayer(controller),
              ),
            ),
          Positioned(
            right: KidSpacing.md * formFactor.density,
            bottom: KidSpacing.md * formFactor.density,
            child: KidButton(
              label: 'Skip',
              icon: Icons.skip_next_rounded,
              tone: KidButtonTone.neutral,
              size: KidButtonSize.small,
              autofocus: true,
              onPressed: _skip,
            ),
          ),
        ],
      ),
    );
  }
}
