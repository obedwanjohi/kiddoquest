import 'child.dart';

/// The Parent Companion Zone, as the website builds it.
///
/// Every field here comes from `ParentDashboardService`, the same code the
/// website's dashboard renders from, so the numbers match what a parent sees
/// in a browser to the letter.
class ParentDashboard {
  const ParentDashboard({
    required this.guardian,
    this.children = const [],
    this.selectedChildId,
    this.report,
    this.missions = const [],
    this.fromCache = false,
  });

  final DashboardGuardian guardian;
  final List<Child> children;
  final int? selectedChildId;
  final DashboardReport? report;
  final List<FocusOption> missions;

  /// True when the device showed its last copy because the server was out of
  /// reach.
  final bool fromCache;

  Child? get selectedChild {
    for (final child in children) {
      if (child.id == selectedChildId) return child;
    }

    return children.isEmpty ? null : children.first;
  }

  factory ParentDashboard.fromJson(Map<String, dynamic> json, {bool fromCache = false}) => ParentDashboard(
        fromCache: fromCache,
        guardian: DashboardGuardian.fromJson(_map(json['guardian'])),
        children: _list(json['children']).map(Child.fromJson).toList(),
        selectedChildId: (json['selected_child_id'] as num?)?.toInt(),
        report: json['report'] is Map ? DashboardReport.fromJson(_map(json['report'])) : null,
        missions: _list(json['missions']).map(FocusOption.fromJson).toList(),
      );
}

class DashboardGuardian {
  const DashboardGuardian({
    this.enableDevotional = true,
    this.enableSongsHub = true,
    this.hasCustomPin = true,
    this.defaultPin = '1234',
  });

  final bool enableDevotional;
  final bool enableSongsHub;
  final bool hasCustomPin;
  final String defaultPin;

  factory DashboardGuardian.fromJson(Map<String, dynamic> json) => DashboardGuardian(
        enableDevotional: json['enable_devotional'] as bool? ?? true,
        enableSongsHub: json['enable_songs_hub'] as bool? ?? true,
        hasCustomPin: json['has_custom_pin'] as bool? ?? true,
        defaultPin: json['default_pin']?.toString() ?? '1234',
      );
}

class FocusOption {
  const FocusOption({required this.id, required this.title});

  final int id;
  final String title;

  factory FocusOption.fromJson(Map<String, dynamic> json) => FocusOption(
        id: (json['id'] as num).toInt(),
        title: json['title'] as String? ?? 'Mission',
      );
}

/// `$reports[$child->id]` on the website.
class DashboardReport {
  const DashboardReport({
    this.totalMissions = 0,
    this.passedMissions = 0,
    this.totalQuestions = 0,
    this.accuracyRate = 0,
    this.learningTimeToday = '0 mins',
    this.learningTimeWeek = '0 mins',
    this.streakDays = 1,
    this.canDoNow = const [],
    this.learningNext = const [],
    this.heatMap = const [],
    this.growthLabel = '🌱 Ready to Start',
    this.mistake = '',
    this.activity = '',
    this.hasStruggle = false,
    this.history = const [],
    this.assignedMission,
  });

  final int totalMissions;
  final int passedMissions;
  final int totalQuestions;
  final int accuracyRate;
  final String learningTimeToday;
  final String learningTimeWeek;
  final int streakDays;
  final List<String> canDoNow;
  final List<String> learningNext;
  final List<HeatRow> heatMap;
  final String growthLabel;
  final String mistake;
  final String activity;
  final bool hasStruggle;
  final List<HistoryRow> history;
  final FocusOption? assignedMission;

  factory DashboardReport.fromJson(Map<String, dynamic> json) {
    final action = _map(json['mistake_action']);
    final growth = _map(json['growth']);
    final assigned = json['assigned_mission'];

    return DashboardReport(
      totalMissions: _int(json['total_missions']),
      passedMissions: _int(json['passed_missions']),
      totalQuestions: _int(json['total_questions']),
      accuracyRate: _int(json['accuracy_rate']),
      learningTimeToday: json['learning_time_today']?.toString() ?? '0 mins',
      learningTimeWeek: json['learning_time_week']?.toString() ?? '0 mins',
      streakDays: _int(json['streak_days'], fallback: 1),
      canDoNow: _strings(json['can_do_now']),
      learningNext: _strings(json['learning_next']),
      heatMap: _list(json['skills_heat_map']).map(HeatRow.fromJson).toList(),
      growthLabel: growth['growth_label']?.toString() ?? '🌱 Ready to Start',
      mistake: action['mistake']?.toString() ?? '',
      activity: action['activity']?.toString() ?? '',
      hasStruggle: action['has_struggle'] == true,
      history: _list(json['mission_history']).map(HistoryRow.fromJson).toList(),
      assignedMission: assigned is Map ? FocusOption.fromJson(assigned.cast<String, dynamic>()) : null,
    );
  }
}

class HeatRow {
  const HeatRow({required this.name, this.score = 0, this.bar = 0, this.total = 8});

  final String name;
  final int score;
  final int bar;
  final int total;

  factory HeatRow.fromJson(Map<String, dynamic> json) => HeatRow(
        name: json['name']?.toString() ?? '',
        score: _int(json['score']),
        bar: _int(json['bar']),
        total: _int(json['total'], fallback: 8),
      );
}

class HistoryRow {
  const HistoryRow({
    required this.title,
    this.attemptsCount = 0,
    this.bestStars = 0,
    this.lastPlayed = '',
    this.attempts = const [],
    this.mistakes = const [],
  });

  final String title;
  final int attemptsCount;
  final int bestStars;
  final String lastPlayed;
  final List<AttemptRow> attempts;
  final List<String> mistakes;

  factory HistoryRow.fromJson(Map<String, dynamic> json) => HistoryRow(
        title: json['mission_title']?.toString() ?? 'Mission',
        attemptsCount: _int(json['attempts_count']),
        bestStars: _int(json['best_stars']),
        lastPlayed: json['last_played']?.toString() ?? '',
        attempts: _list(json['attempts']).map(AttemptRow.fromJson).toList(),
        mistakes: _strings(json['mistakes']),
      );
}

class AttemptRow {
  const AttemptRow({required this.attempt, required this.score, required this.date});

  final int attempt;
  final String score;
  final String date;

  factory AttemptRow.fromJson(Map<String, dynamic> json) => AttemptRow(
        attempt: _int(json['attempt']),
        score: json['score']?.toString() ?? '',
        date: json['date']?.toString() ?? '',
      );
}

Map<String, dynamic> _map(Object? value) => value is Map ? value.cast<String, dynamic>() : <String, dynamic>{};

List<Map<String, dynamic>> _list(Object? value) =>
    value is List ? value.whereType<Map>().map((e) => e.cast<String, dynamic>()).toList() : const [];

List<String> _strings(Object? value) => value is List ? value.map((e) => e.toString()).toList() : const [];

int _int(Object? value, {int fallback = 0}) {
  if (value is num) return value.toInt();
  if (value is String) return int.tryParse(value) ?? fallback;
  return fallback;
}
