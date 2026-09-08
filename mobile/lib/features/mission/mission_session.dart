import 'dart:async';

import 'package:flutter/foundation.dart';

import '../../core/db/content_dao.dart';
import '../../core/db/local_database.dart';
import '../../core/db/progress_dao.dart';
import '../../core/models/child.dart';
import '../../core/models/learning_event.dart';
import '../../core/models/pack.dart';
import '../../core/scoring/question_scorer.dart';
import '../../core/sync/outbox.dart';
import '../../core/sync/sync_engine.dart';

/// What the answer cards are showing right now.
enum QuestionPhase { asking, correct, tryAgain, revealed }

/// Runs one attempt at a mission.
///
/// It owns three things: the questions drawn for this sitting, what the child
/// has done with them, and the events that describe it. Everything is written
/// to the outbox as it happens, so closing the app mid-mission loses at most
/// the question in progress.
class MissionSessionController extends ChangeNotifier {
  MissionSessionController({
    required this.child,
    required this.missionId,
    required ContentDao content,
    required ProgressDao progress,
    required Outbox outbox,
    required SyncEngine sync,
    int exclusionWindowDays = 7,
  })  : _content = content,
        _progress = progress,
        _outbox = outbox,
        _sync = sync,
        _exclusionWindowDays = exclusionWindowDays;

  final Child child;
  final int missionId;
  final ContentDao _content;
  final ProgressDao _progress;
  final Outbox _outbox;
  final SyncEngine _sync;
  final int _exclusionWindowDays;

  static const _scorer = QuestionScorer();

  /// Three tries, then the answer is shown. From the interaction guidelines:
  /// a child must never be stuck, and must never be told they are wrong.
  static const int maxAttempts = 3;

  PackMission? mission;
  List<PackQuestion> questions = const [];

  /// The pack's media table, so a question's picture key can be turned into a
  /// file or a URL while the mission is being played.
  Map<String, ResolvedMedia> media = const {};
  int index = 0;
  QuestionPhase phase = QuestionPhase.asking;
  int attempts = 0;
  int? chosenOptionId;
  bool loading = true;
  bool finished = false;
  String? error;
  MissionScore? result;
  bool showHint = false;

  final Map<int, Map<String, dynamic>> _answers = {};
  final Map<int, int> _questionAttempts = {};
  DateTime _startedAt = DateTime.now();
  DateTime _questionStartedAt = DateTime.now();
  Timer? _advanceTimer;

  PackQuestion? get current => index < questions.length ? questions[index] : null;

  int get total => questions.length;

  int get correctSoFar => _answers.values.where((a) => a['correct'] == true).length;

  Future<void> load() async {
    try {
      final row = await _content.mission(missionId);

      if (row == null) {
        error = 'That mission is not on this device yet.';
        loading = false;
        notifyListeners();
        return;
      }

      mission = PackMission(
        id: row['id'] as int,
        title: row['title'] as String? ?? 'Mission',
        slug: row['slug'] as String?,
        displayTitle: row['display_title'] as String?,
        description: row['description'] as String?,
        introText: row['intro_text'] as String?,
        introAudio: row['intro_audio'] as String?,
        outroText: row['outro_text'] as String?,
        videoPath: row['video_path'] as String?,
        questionsPerSession: row['questions_per_session'] as int? ?? 6,
        passThresholdPercent: row['pass_threshold'] as int? ?? 60,
        starsReward: row['stars_reward'] as int? ?? 3,
        estimatedMinutes: row['estimated_minutes'] as int? ?? 5,
        sortOrder: row['sort_order'] as int? ?? 0,
      );

      media = await _content.mediaFor(row['pack_id'] as String? ?? '');

      questions = await _content.drawSession(
        missionId: missionId,
        childId: child.id,
        count: mission!.questionsPerSession,
        exclusionWindowDays: _exclusionWindowDays,
      );

      if (questions.isEmpty) {
        error = 'This mission has no questions yet.';
      }

      loading = false;
      _startedAt = DateTime.now();
      _questionStartedAt = DateTime.now();
      notifyListeners();

      if (questions.isNotEmpty) {
        await _outbox.add(
          type: LearningEvent.missionStarted,
          childId: child.id,
          payload: {
            'mission_id': missionId,
            'question_ids': questions.map((q) => q.id).toList(),
          },
        );
      }
    } catch (e) {
      error = '$e';
      loading = false;
      notifyListeners();
    }
  }

  /// The child tapped an answer card.
  void chooseOption(int optionId) => submitResponse({'option_id': optionId}, chosenOptionId: optionId);

  /// Used by the practice-only types, where doing the activity is the answer.
  void completePractice({String mode = 'practice'}) => submitResponse({'mode': mode});

  /// Every question type ends here.
  ///
  /// The renderer decides what a response looks like — an option id, a set of
  /// pairs, an order, a list of taps on a picture — and this decides what it is
  /// worth, using the same scorer the server uses. Keeping the judgement in one
  /// place is what stops thirteen renderers each inventing their own idea of
  /// "right".
  void submitResponse(Map<String, dynamic> response, {int? chosenOptionId}) {
    final question = current;
    if (question == null || phase != QuestionPhase.asking) return;

    attempts++;
    this.chosenOptionId = chosenOptionId;

    final correct = _scorer.isCorrect(question.toScorerJson(), {'response': response}) ?? false;

    if (correct) {
      _recordAnswer(question, response, correct: true);
      phase = QuestionPhase.correct;
      notifyListeners();
      _advanceTimer = Timer(const Duration(milliseconds: 1800), next);
      return;
    }

    if (attempts >= maxAttempts) {
      _recordAnswer(question, response, correct: false, revealed: true);
      phase = QuestionPhase.revealed;
      notifyListeners();
      _advanceTimer = Timer(const Duration(seconds: 3), next);
      return;
    }

    // Not right, but not over: grey it, offer the hint, let them try again.
    phase = QuestionPhase.tryAgain;
    showHint = question.hint != null && question.hint!.isNotEmpty;
    notifyListeners();

    Timer(const Duration(milliseconds: 700), () {
      if (phase == QuestionPhase.tryAgain) {
        phase = QuestionPhase.asking;
        this.chosenOptionId = null;
        notifyListeners();
      }
    });
  }

  void _recordAnswer(
    PackQuestion question,
    Map<String, dynamic> response, {
    required bool correct,
    bool revealed = false,
  }) {
    final elapsed = DateTime.now().difference(_questionStartedAt).inMilliseconds;

    _answers[question.id] = {
      'question_id': question.id,
      'type': question.type,
      'response': response,
      'correct': correct,
      'attempts': attempts,
      'time_ms': elapsed,
      if (revealed) 'revealed': true,
    };
    _questionAttempts[question.id] = attempts;

    unawaited(_outbox.add(
      type: LearningEvent.questionAnswered,
      childId: child.id,
      payload: {
        'question_id': question.id,
        'type': question.type,
        'response': response,
        'correct': correct,
        'attempts': attempts,
        'time_ms': elapsed,
      },
    ));
  }

  void next() {
    _advanceTimer?.cancel();

    if (index >= questions.length - 1) {
      unawaited(finish());
      return;
    }

    index++;
    attempts = 0;
    chosenOptionId = null;
    showHint = false;
    phase = QuestionPhase.asking;
    _questionStartedAt = DateTime.now();
    notifyListeners();
  }

  /// End the attempt: score it, save it, queue it, and ask for a sync.
  Future<void> finish() async {
    if (finished) return;
    finished = true;
    _advanceTimer?.cancel();

    final timeSpent = DateTime.now().difference(_startedAt).inSeconds;
    final answers = _answers.values.toList();

    result = _scorer.scoreMission(
      questions: questions.map((q) => q.toScorerJson()).toList(),
      answers: answers,
      passThresholdPercent: mission?.passThresholdPercent ?? 60,
    );

    await _progress.recordProgress(
      childId: child.id,
      missionId: missionId,
      passed: result!.passed,
      stars: result!.stars,
    );

    await _progress.logQuestionAttempts(
      childId: child.id,
      missionId: missionId,
      results: result!.perQuestion,
    );

    await _progress.addScreenTime(childId: child.id, seconds: timeSpent);

    await _outbox.add(
      type: LearningEvent.missionCompleted,
      childId: child.id,
      payload: {
        'mission_id': missionId,
        'score': result!.score,
        'total': result!.total,
        'stars': result!.stars,
        'time_spent': timeSpent,
        'answers': answers,
      },
    );

    notifyListeners();

    // A finished mission is the moment worth spending a request on. If there is
    // no network it simply stays in the outbox.
    unawaited(_sync.syncNow(childId: child.id, reason: 'mission-completed'));
  }

  /// The child left early. Their time still counts; no stars are awarded.
  Future<void> abandon() async {
    if (finished) return;
    finished = true;
    _advanceTimer?.cancel();

    final timeSpent = DateTime.now().difference(_startedAt).inSeconds;

    await _progress.addScreenTime(childId: child.id, seconds: timeSpent);
    await _outbox.add(
      type: LearningEvent.missionAbandoned,
      childId: child.id,
      payload: {
        'mission_id': missionId,
        'answered': _answers.length,
        'seconds': timeSpent,
      },
    );
  }

  @override
  void dispose() {
    _advanceTimer?.cancel();
    super.dispose();
  }
}
