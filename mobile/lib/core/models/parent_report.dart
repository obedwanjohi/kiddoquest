import 'snapshot.dart';

/// What the parent dashboard shows.
///
/// The whole report arrives in one response and is cached verbatim, so a parent
/// on a matatu with no signal still sees last night's numbers rather than a
/// spinner. Every field is tolerant of being missing: a report from an older
/// server should degrade to fewer cards, never to a crash.
class ParentReport {
  const ParentReport({
    required this.childName,
    required this.rangeDays,
    required this.overview,
    required this.progress,
    required this.history,
    required this.support,
    this.badges = const [],
    this.streak = 0,
    this.stars = 0,
    this.coins = 0,
    this.dailyLimitMinutes = 0,
    this.generatedAt,
  });

  final String childName;
  final int rangeDays;
  final ReportOverview overview;
  final ReportProgress progress;
  final List<ReportAttempt> history;
  final ReportSupport support;
  final List<EarnedBadge> badges;
  final int streak;
  final int stars;
  final int coins;
  final int dailyLimitMinutes;
  final DateTime? generatedAt;

  bool get hasActivity => overview.questionsAnswered > 0 || history.isNotEmpty;

  factory ParentReport.fromJson(Map<String, dynamic> json) {
    final child = _map(json['child']);

    return ParentReport(
      childName: child['name'] as String? ?? 'Your child',
      rangeDays: _int(json['range_days'], 7),
      overview: ReportOverview.fromJson(_map(json['overview'])),
      progress: ReportProgress.fromJson(_map(json['progress'])),
      history: _list(json['history']).map(ReportAttempt.fromJson).toList(),
      support: ReportSupport.fromJson(_map(json['support'])),
      badges: _list(json['badges']).map(EarnedBadge.fromJson).toList(),
      streak: _int(child['streak'], 0),
      stars: _int(child['stars'], 0),
      coins: _int(child['coins'], 0),
      dailyLimitMinutes: _int(child['daily_limit_minutes'], 0),
      generatedAt: DateTime.tryParse(json['generated_at'] as String? ?? ''),
    );
  }
}

class ReportOverview {
  const ReportOverview({
    this.minutesToday = 0,
    this.minutesInRange = 0,
    this.missionsCompleted = 0,
    this.missionsPassed = 0,
    this.questionsAnswered = 0,
    this.accuracyPercent,
    this.starsInRange = 0,
    this.daysPlayed = 0,
    this.daysInRange = 7,
    this.daily = const [],
  });

  final int minutesToday;
  final int minutesInRange;
  final int missionsCompleted;
  final int missionsPassed;
  final int questionsAnswered;
  final int? accuracyPercent;
  final int starsInRange;
  final int daysPlayed;
  final int daysInRange;
  final List<ReportDay> daily;

  factory ReportOverview.fromJson(Map<String, dynamic> json) => ReportOverview(
        minutesToday: _int(json['minutes_today'], 0),
        minutesInRange: _int(json['minutes_in_range'], 0),
        missionsCompleted: _int(json['missions_completed'], 0),
        missionsPassed: _int(json['missions_passed'], 0),
        questionsAnswered: _int(json['questions_answered'], 0),
        accuracyPercent: json['accuracy_percent'] == null ? null : _int(json['accuracy_percent'], 0),
        starsInRange: _int(json['stars_in_range'], 0),
        daysPlayed: _int(json['days_played'], 0),
        daysInRange: _int(json['days_in_range'], 7),
        daily: _list(json['daily']).map(ReportDay.fromJson).toList(),
      );
}

class ReportDay {
  const ReportDay({required this.day, this.minutes = 0, this.missions = 0, this.stars = 0});

  final String day;
  final int minutes;
  final int missions;
  final int stars;

  factory ReportDay.fromJson(Map<String, dynamic> json) => ReportDay(
        day: json['day'] as String? ?? '',
        minutes: _int(json['minutes'], 0),
        missions: _int(json['missions'], 0),
        stars: _int(json['stars'], 0),
      );
}

class ReportProgress {
  const ReportProgress({this.canDoNow = const [], this.learningNext = const [], this.subjects = const []});

  final List<String> canDoNow;
  final List<ReportNextMission> learningNext;
  final List<ReportSubject> subjects;

  factory ReportProgress.fromJson(Map<String, dynamic> json) => ReportProgress(
        canDoNow: ((json['can_do_now'] as List?) ?? const []).map((e) => e.toString()).toList(),
        learningNext: _list(json['learning_next']).map(ReportNextMission.fromJson).toList(),
        subjects: _list(json['subjects']).map(ReportSubject.fromJson).toList(),
      );
}

class ReportNextMission {
  const ReportNextMission({required this.id, required this.title});

  final int id;
  final String title;

  factory ReportNextMission.fromJson(Map<String, dynamic> json) => ReportNextMission(
        id: _int(json['id'], 0),
        title: json['title'] as String? ?? 'A mission',
      );
}

class ReportSubject {
  const ReportSubject({required this.name, this.code, this.answered = 0, this.accuracyPercent = 0});

  final String name;
  final String? code;
  final int answered;
  final int accuracyPercent;

  factory ReportSubject.fromJson(Map<String, dynamic> json) => ReportSubject(
        name: json['name'] as String? ?? 'Other',
        code: json['code'] as String?,
        answered: _int(json['answered'], 0),
        accuracyPercent: _int(json['accuracy_percent'], 0),
      );
}

class ReportAttempt {
  const ReportAttempt({
    required this.missionId,
    required this.title,
    this.score = 0,
    this.total = 0,
    this.percentage = 0,
    this.stars = 0,
    this.passed = false,
    this.minutes = 0,
    this.completedAt,
    this.mistakes = const [],
  });

  final int missionId;
  final String title;
  final int score;
  final int total;
  final int percentage;
  final int stars;
  final bool passed;
  final int minutes;
  final DateTime? completedAt;
  final List<String> mistakes;

  factory ReportAttempt.fromJson(Map<String, dynamic> json) => ReportAttempt(
        missionId: _int(json['mission_id'], 0),
        title: json['title'] as String? ?? 'Mission',
        score: _int(json['score'], 0),
        total: _int(json['total'], 0),
        percentage: _int(json['percentage'], 0),
        stars: _int(json['stars'], 0),
        passed: json['passed'] == true,
        minutes: _int(json['minutes'], 0),
        completedAt: DateTime.tryParse(json['completed_at'] as String? ?? ''),
        mistakes: ((json['mistakes'] as List?) ?? const []).map((e) => e.toString()).toList(),
      );
}

class ReportSupport {
  const ReportSupport({
    this.hasStruggle = false,
    this.headline = '',
    this.activity = '',
    this.missionId,
  });

  final bool hasStruggle;
  final String headline;
  final String activity;
  final int? missionId;

  factory ReportSupport.fromJson(Map<String, dynamic> json) => ReportSupport(
        hasStruggle: json['has_struggle'] == true,
        headline: json['headline'] as String? ?? '',
        activity: json['activity'] as String? ?? '',
        missionId: json['mission_id'] == null ? null : _int(json['mission_id'], 0),
      );
}

Map<String, dynamic> _map(Object? value) =>
    value is Map ? value.cast<String, dynamic>() : <String, dynamic>{};

List<Map<String, dynamic>> _list(Object? value) => value is List
    ? value.whereType<Map>().map((e) => e.cast<String, dynamic>()).toList()
    : const [];

int _int(Object? value, int fallback) {
  if (value is num) return value.toInt();
  if (value is String) return int.tryParse(value) ?? fallback;
  return fallback;
}
