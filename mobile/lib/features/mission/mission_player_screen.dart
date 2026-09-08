import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/providers.dart';
import '../../core/models/pack.dart';
import '../../core/platform/form_factor.dart';
import '../../core/scoring/question_scorer.dart';
import '../../design/components/answer_card.dart';
import '../../design/components/focus_ring.dart';
import '../../design/components/kid_button.dart';
import '../../design/components/kid_scaffold.dart';
import '../../design/components/mascot_stage.dart';
import '../../design/components/pack_image.dart';
import '../../design/components/progress_bar.dart';
import '../../design/tokens.dart';
import '../map/map_state.dart';
import '../screen_time/screen_time.dart';
import 'mission_session.dart';
import 'renderers/drag_sequence_renderer.dart';
import 'renderers/drag_sort_renderer.dart';
import 'renderers/matching_renderer.dart';
import 'renderers/memory_match_renderer.dart';
import 'renderers/renderer_kit.dart';
import 'renderers/spot_find_renderer.dart';
import 'renderers/speak_repeat_renderer.dart';
import 'renderers/tracing_renderer.dart';

/// Playing a mission.
///
/// The layout follows the website's mission stage: Leo on one side asking the
/// question, the answers on the other. On a phone in portrait they stack; in
/// landscape and on a television they sit side by side, which is what keeps a
/// mission on one screen with no scrolling.
class MissionPlayerScreen extends ConsumerStatefulWidget {
  const MissionPlayerScreen({super.key, required this.missionId});

  final int missionId;

  @override
  ConsumerState<MissionPlayerScreen> createState() => _MissionPlayerScreenState();
}

class _MissionPlayerScreenState extends ConsumerState<MissionPlayerScreen> {
  MissionSessionController? _session;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _start());
  }

  Future<void> _start() async {
    final child = ref.read(sessionProvider).activeChild;
    if (child == null) return;

    final config = ref.read(appConfigProvider).value;

    final session = MissionSessionController(
      child: child,
      missionId: widget.missionId,
      content: ref.read(contentDaoProvider),
      progress: ref.read(progressDaoProvider),
      outbox: ref.read(outboxProvider),
      sync: ref.read(syncEngineProvider),
      exclusionWindowDays: config?.exclusionWindowDays ?? 7,
    );

    session.addListener(_onSessionChanged);
    setState(() => _session = session);

    await session.load();
  }

  void _onSessionChanged() {
    if (!mounted) return;

    final session = _session;
    if (session != null && session.finished && session.result != null) {
      // Refresh the map behind the celebration so stars and the time left are
      // already up to date when the child gets back to it.
      ref.invalidate(mapDataProvider);
      ref.invalidate(screenTimeProvider);
    }

    setState(() {});
  }

  @override
  void dispose() {
    _session?.removeListener(_onSessionChanged);
    _session?.dispose();
    super.dispose();
  }

  Future<void> _confirmLeave() async {
    final leave = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Leave this mission?'),
        content: const Text('Your progress is saved. You can come back any time.'),
        actions: [
          TextButton(onPressed: () => Navigator.of(context).pop(false), child: const Text('Keep playing')),
          TextButton(onPressed: () => Navigator.of(context).pop(true), child: const Text('Leave')),
        ],
      ),
    );

    if (leave == true && mounted) {
      await _session?.abandon();
      if (mounted) context.go('/map');
    }
  }

  @override
  Widget build(BuildContext context) {
    final session = _session;
    final formFactor = context.formFactor;

    if (session == null || session.loading) {
      return const KidScaffold(child: LeoLoading(message: 'Getting your mission ready…'));
    }

    if (session.error != null) {
      return KidScaffold(
        child: LeoMessage(
          title: 'We cannot start this one',
          body: session.error,
          action: KidButton(label: 'Back to the map', onPressed: () => context.go('/map')),
        ),
      );
    }

    if (session.finished && session.result != null) {
      return _CelebrationView(session: session);
    }

    final question = session.current;
    if (question == null) {
      return const KidScaffold(child: LeoLoading(message: 'Finishing up…'));
    }

    final wide = formFactor.isTv || MediaQuery.sizeOf(context).width > 720;

    final leo = _LeoColumn(session: session, question: question);
    final answers = _AnswerArea(session: session, question: question);

    return KidScaffold(
      onBack: _confirmLeave,
      background: KidColors.stageCream,
      child: Column(
        children: [
          SizedBox(height: KidSpacing.sm * formFactor.density),
          Row(
            children: [
              KidFocusable(
                onPressed: _confirmLeave,
                borderRadius: KidRadius.pill,
                semanticLabel: 'Leave mission',
                child: Container(
                  width: KidTouch.min * formFactor.density,
                  height: KidTouch.min * formFactor.density,
                  alignment: Alignment.center,
                  decoration: const BoxDecoration(color: Colors.white, shape: BoxShape.circle),
                  child: const Text('🏠', style: TextStyle(fontSize: 20)),
                ),
              ),
              SizedBox(width: KidSpacing.md * formFactor.density),
              Expanded(
                child: KidProgressBar(
                  current: session.index,
                  total: session.total,
                  accent: KidColors.primary,
                ),
              ),
              SizedBox(width: KidSpacing.md * formFactor.density),
              StarRow(stars: session.correctSoFar.clamp(0, 3), size: 16),
            ],
          ),
          SizedBox(height: KidSpacing.md * formFactor.density),
          Expanded(
            child: wide
                ? Row(
                    crossAxisAlignment: CrossAxisAlignment.center,
                    children: [
                      Expanded(flex: 4, child: leo),
                      SizedBox(width: KidSpacing.lg * formFactor.density),
                      Expanded(flex: 6, child: answers),
                    ],
                  )
                : Column(
                    children: [
                      leo,
                      SizedBox(height: KidSpacing.md * formFactor.density),
                      Expanded(child: answers),
                    ],
                  ),
          ),
        ],
      ),
    );
  }
}

class _LeoColumn extends StatelessWidget {
  const _LeoColumn({required this.session, required this.question});

  final MissionSessionController session;
  final PackQuestion question;

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);

    final mood = switch (session.phase) {
      QuestionPhase.correct => MascotMood.cheering,
      QuestionPhase.tryAgain => MascotMood.encouraging,
      QuestionPhase.revealed => MascotMood.thinking,
      QuestionPhase.asking => MascotMood.talking,
    };

    final line = switch (session.phase) {
      QuestionPhase.correct => 'Yes! Well done!',
      QuestionPhase.tryAgain => 'Nearly. Have another go.',
      QuestionPhase.revealed => 'Here it is. Now you know!',
      QuestionPhase.asking => question.prompt ?? question.narrationText ?? '',
    };

    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        MascotStage(mood: mood, size: formFactor.isTv ? 140 : 88),
        SizedBox(height: KidSpacing.sm * formFactor.density),
        MascotSpeech(
          text: line,
          onReplay: () {
            // The audio director arrives with narration in the next phase; the
            // button is here now so its place in the layout is settled.
            HapticFeedback.selectionClick();
          },
        ),
        if (session.showHint && question.hint != null) ...[
          SizedBox(height: KidSpacing.sm * formFactor.density),
          Container(
            padding: EdgeInsets.all(KidSpacing.sm * formFactor.density),
            decoration: const BoxDecoration(color: KidColors.amberSoft, borderRadius: KidRadius.button),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Text('💡'),
                SizedBox(width: KidSpacing.xs * formFactor.density),
                Flexible(child: Text(question.hint!, style: theme.textTheme.bodyLarge)),
              ],
            ),
          ),
        ],
        if (question.image != null) ...[
          SizedBox(height: KidSpacing.md * formFactor.density),
          PackImage(mediaKey: question.image, media: session.media, height: 180),
        ],
        if (_countEmoji(question) != null) ...[
          SizedBox(height: KidSpacing.md * formFactor.density),
          Text(
            _countEmoji(question)!,
            textAlign: TextAlign.center,
            style: TextStyle(fontSize: 40 * formFactor.density),
          ),
        ],
      ],
    );
  }

  /// Counting questions often ship an emoji and a number rather than a picture.
  String? _countEmoji(PackQuestion question) {
    if (QuestionScorer.normalizeType(question.type) != 'count_objects') return null;

    final emoji = question.scoringConfig['emoji'] as String?;
    final count = (question.scoringConfig['count'] ?? question.scoringConfig['target_count']) as num?;

    if (emoji == null || count == null || count <= 0) return null;

    return List.filled(count.toInt().clamp(1, 12), emoji).join(' ');
  }
}

class _AnswerArea extends StatelessWidget {
  const _AnswerArea({required this.session, required this.question});

  final MissionSessionController session;
  final PackQuestion question;

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final type = QuestionScorer.normalizeType(question.type);

    // All thirteen types have a renderer. The tap-answer family shares the
    // answer-card grid below; the rest build their answer their own way and
    // hand it to the same scorer.
    const tapTypes = {'multiple_choice', 'listen_choose', 'true_false', 'fill_blank', 'count_objects', 'pattern'};

    if (!tapTypes.contains(type)) {
      final rendererContext = RendererContext.of(session, question);

      // Keyed on the attempt so that a second try starts from a clean board
      // rather than the half-built answer that did not work.
      final key = ValueKey('$type-${question.id}-${session.attempts}');

      return switch (type) {
        'matching' => MatchingRenderer(key: key, context_: rendererContext),
        'drag_sort' => DragSortRenderer(key: key, context_: rendererContext),
        'drag_sequence' => DragSequenceRenderer(key: key, context_: rendererContext),
        'memory_match' => MemoryMatchRenderer(key: key, context_: rendererContext),
        'spot_find' => SpotFindRenderer(key: key, context_: rendererContext),
        'tracing' => TracingRenderer(key: key, context_: rendererContext),
        'speak_repeat' => SpeakRepeatRenderer(key: key, context_: rendererContext),
        _ => _PracticeCard(session: session, type: type),
      };
    }

    final options = [...question.options]..sort((a, b) => a.sortOrder.compareTo(b.sortOrder));
    final columns = options.length <= 2 ? options.length : formFactor.answerColumns;

    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: columns.clamp(1, 4),
        mainAxisSpacing: KidSpacing.md * formFactor.density,
        crossAxisSpacing: KidSpacing.md * formFactor.density,
        childAspectRatio: options.any((o) => o.image != null) ? 0.9 : 1.6,
      ),
      itemCount: options.length,
      itemBuilder: (context, index) {
        final option = options[index];

        final state = switch (session.phase) {
          QuestionPhase.correct when option.id == session.chosenOptionId => AnswerState.correct,
          QuestionPhase.tryAgain when option.id == session.chosenOptionId => AnswerState.gentleWrong,
          QuestionPhase.revealed when option.isCorrect => AnswerState.revealed,
          QuestionPhase.revealed => AnswerState.disabled,
          _ => AnswerState.idle,
        };

        return AnswerCard(
          state: state,
          index: index,
          label: option.text,
          image: PackImage(mediaKey: option.image, media: session.media),
          hasImage: option.image != null,
          autofocus: index == 0 && session.phase == QuestionPhase.asking,
          onPressed: () => session.chooseOption(option.id),
        );
      },
    );
  }
}

/// A holding card for the question types whose renderers are still to come.
/// The child is not stopped: they see the word, do the activity, and move on.
class _PracticeCard extends StatelessWidget {
  const _PracticeCard({required this.session, required this.type});

  final MissionSessionController session;
  final String type;

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);

    final label = switch (type) {
      'tracing' => 'Trace it with your finger, then tap Done',
      'speak_repeat' => 'Say the word out loud, then tap Done',
      'matching' => 'Match the pairs, then tap Done',
      'drag_sort' => 'Sort them into groups, then tap Done',
      'drag_sequence' => 'Put them in order, then tap Done',
      'memory_match' => 'Find the pairs, then tap Done',
      'spot_find' => 'Find the hidden things, then tap Done',
      _ => 'Have a go, then tap Done',
    };

    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            padding: EdgeInsets.all(KidSpacing.lg * formFactor.density),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: KidRadius.card,
              border: Border.all(color: KidColors.primaryLight, width: 3),
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text('✏️', style: TextStyle(fontSize: 44 * formFactor.density)),
                SizedBox(height: KidSpacing.sm * formFactor.density),
                Text(label, textAlign: TextAlign.center, style: theme.textTheme.titleMedium),
              ],
            ),
          ),
          SizedBox(height: KidSpacing.lg * formFactor.density),
          KidButton(
            label: 'Done!',
            tone: KidButtonTone.success,
            autofocus: true,
            onPressed: session.completePractice,
          ),
        ],
      ),
    );
  }
}

/// What a child sees when the mission ends.
class _CelebrationView extends ConsumerWidget {
  const _CelebrationView({required this.session});

  final MissionSessionController session;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);
    final result = session.result!;

    final headline = switch (result.stars) {
      3 => 'PERFECT!',
      2 => 'Awesome work!',
      1 => 'Nice try!',
      _ => 'Keep going!',
    };

    return KidScaffold(
      onBack: () => context.go('/map'),
      child: Center(
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              MascotStage(
                mood: result.stars >= 2 ? MascotMood.cheering : MascotMood.encouraging,
                size: formFactor.isTv ? 180 : 120,
              ),
              SizedBox(height: KidSpacing.md * formFactor.density),
              Text(headline, style: theme.textTheme.displayLarge),
              SizedBox(height: KidSpacing.sm * formFactor.density),
              StarRow(stars: result.stars, size: 44),
              SizedBox(height: KidSpacing.md * formFactor.density),
              Text(
                '${result.score} of ${result.total} right',
                style: theme.textTheme.titleLarge?.copyWith(color: theme.colorScheme.onSurfaceVariant),
              ),
              SizedBox(height: KidSpacing.lg * formFactor.density),
              Text(
                session.mission?.outroText ?? 'Your stars are on their way to your grown-up.',
                textAlign: TextAlign.center,
                style: theme.textTheme.bodyLarge,
              ),
              SizedBox(height: KidSpacing.xl * formFactor.density),
              KidButton(
                label: 'Back to the map',
                icon: Icons.map_rounded,
                autofocus: true,
                onPressed: () => context.go('/map'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
