import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/providers.dart';
import '../../core/audio/audio_director.dart';
import '../../core/models/extras.dart';
import '../../core/platform/form_factor.dart';
import '../../design/components/focus_ring.dart';
import '../../design/components/kid_button.dart';
import '../../design/components/kid_scaffold.dart';
import '../../design/components/mascot_stage.dart';
import '../../design/tokens.dart';

/// The songs hub.
///
/// A song either plays from the device or it does not exist for this child yet.
/// The website's hub embeds YouTube, which cannot work in a car with no signal
/// and sends a four-year-old out of the app into a place nobody is supervising.
/// Neither is acceptable here, so a song with no licensed recording is shown as
/// coming soon rather than as a link out.
///
/// Filling in a song's `audio` key on the server is all that is needed to turn
/// one of these into a real song; nothing on this screen changes.
class SongsScreen extends ConsumerStatefulWidget {
  const SongsScreen({super.key});

  @override
  ConsumerState<SongsScreen> createState() => _SongsScreenState();
}

class _SongsScreenState extends ConsumerState<SongsScreen> {
  int? _playing;

  // Held rather than read on the way out: reaching for a provider through ref
  // during dispose is reaching through a BuildContext that is already gone.
  late final AudioDirector _audio;

  @override
  void initState() {
    super.initState();
    _audio = ref.read(audioDirectorProvider);
  }

  @override
  void dispose() {
    _audio.stop();
    super.dispose();
  }

  Future<void> _play(Song song) async {
    final director = _audio;

    if (_playing == song.id) {
      await director.stop();
      setState(() => _playing = null);
      return;
    }

    setState(() => _playing = song.id);

    // A song's audio key is looked up in the same media table the missions use,
    // so a downloaded song is as offline as a downloaded world.
    final media = await ref.read(contentDaoProvider).media(song.audio!);

    await director.say(media: media, text: media == null ? song.title : null);
  }

  @override
  Widget build(BuildContext context) {
    final extras = ref.watch(extrasProvider);
    final formFactor = context.formFactor;

    return KidScaffold(
      onBack: () => context.go('/map'),
      appBar: AppBar(
        title: const Text('Songs'),
        leading: BackButton(onPressed: () => context.go('/map')),
      ),
      child: extras.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (_, _) => const _NoSongs(),
        data: (data) {
          if (!data.songsEnabled) {
            return const _SwitchedOff();
          }

          if (data.songs.isEmpty) {
            return const _NoSongs();
          }

          return GridView.builder(
            padding: EdgeInsets.symmetric(vertical: KidSpacing.md * formFactor.density),
            gridDelegate: SliverGridDelegateWithMaxCrossAxisExtent(
              maxCrossAxisExtent: formFactor.isTv ? 420 : 320,
              mainAxisSpacing: KidSpacing.md * formFactor.density,
              crossAxisSpacing: KidSpacing.md * formFactor.density,
              childAspectRatio: 1.6,
            ),
            itemCount: data.songs.length,
            itemBuilder: (context, index) {
              final song = data.songs[index];

              return _SongCard(
                song: song,
                playing: _playing == song.id,
                onPlay: song.playsOffline ? () => _play(song) : null,
              );
            },
          );
        },
      ),
    );
  }
}

class _SongCard extends StatelessWidget {
  const _SongCard({required this.song, required this.playing, this.onPlay});

  final Song song;
  final bool playing;
  final VoidCallback? onPlay;

  @override
  Widget build(BuildContext context) {
    final palette = WorldPalette.resolve(slug: song.palette);
    final formFactor = context.formFactor;
    final theme = Theme.of(context);
    final available = onPlay != null;

    return KidFocusable(
      onPressed: onPlay,
      borderRadius: KidRadius.card,
      semanticLabel: song.title,
      child: Opacity(
        opacity: available ? 1 : 0.65,
        child: Container(
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
                  Text(song.emoji, style: TextStyle(fontSize: 34 * formFactor.density)),
                  const Spacer(),
                  Icon(
                    available
                        ? (playing ? Icons.pause_circle_filled_rounded : Icons.play_circle_fill_rounded)
                        : Icons.hourglass_top_rounded,
                    size: 40 * formFactor.density,
                    color: Colors.white,
                  ),
                ],
              ),
              const Spacer(),
              Text(
                song.title,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: theme.textTheme.titleLarge?.copyWith(color: Colors.white),
              ),
              Text(
                available ? song.category : 'Coming soon',
                style: theme.textTheme.labelLarge?.copyWith(
                  color: Colors.white.withValues(alpha: 0.9),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _NoSongs extends StatelessWidget {
  const _NoSongs();

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const MascotStage(mood: MascotMood.thinking),
          SizedBox(height: KidSpacing.md * context.formFactor.density),
          Text('No songs here yet', style: Theme.of(context).textTheme.titleLarge),
          SizedBox(height: KidSpacing.xs * context.formFactor.density),
          Text(
            'Leo is still learning the words.',
            style: Theme.of(context).textTheme.bodyLarge,
          ),
          SizedBox(height: KidSpacing.md * context.formFactor.density),
          KidButton(
            label: 'Back to the map',
            autofocus: true,
            onPressed: () => context.go('/map'),
          ),
        ],
      ),
    );
  }
}

class _SwitchedOff extends StatelessWidget {
  const _SwitchedOff();

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const MascotStage(mood: MascotMood.idle),
          SizedBox(height: KidSpacing.md * context.formFactor.density),
          Text('Songs are switched off', style: Theme.of(context).textTheme.titleLarge),
          SizedBox(height: KidSpacing.xs * context.formFactor.density),
          Text(
            'A grown-up can turn them back on in the parent zone.',
            textAlign: TextAlign.center,
            style: Theme.of(context).textTheme.bodyLarge,
          ),
          SizedBox(height: KidSpacing.md * context.formFactor.density),
          KidButton(
            label: 'Back to the map',
            autofocus: true,
            onPressed: () => context.go('/map'),
          ),
        ],
      ),
    );
  }
}
