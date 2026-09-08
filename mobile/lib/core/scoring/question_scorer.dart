import 'dart:math' as math;

/// Decides whether an answer was right.
///
/// This is a deliberate duplicate of `MissionScoringService` on the server. The
/// app has to score instantly and offline; the server has to be the authority.
/// Both are run against `fixtures/scoring/*.json` so the two can never drift:
/// if they disagree, a test fails before a child ever sees it.
class QuestionScorer {
  const QuestionScorer();

  /// Every spelling of a type the database has ever used, mapped to the one the
  /// renderers know.
  static const Map<String, String> typeAliases = {
    'multiple-choice': 'multiple_choice',
    'multiple_choice': 'multiple_choice',
    'tap_answer': 'multiple_choice',
    'tap-answer': 'multiple_choice',
    'listen-choose': 'listen_choose',
    'listen_choose': 'listen_choose',
    'true-false': 'true_false',
    'true_false': 'true_false',
    'matching': 'matching',
    'drag-sort': 'drag_sort',
    'drag_sort': 'drag_sort',
    'drag-sequence': 'drag_sequence',
    'drag_sequence': 'drag_sequence',
    'speak-repeat': 'speak_repeat',
    'speak_repeat': 'speak_repeat',
    'fill-blank': 'fill_blank',
    'fill_blank': 'fill_blank',
    'count-objects': 'count_objects',
    'count_objects': 'count_objects',
    'complete-pattern': 'pattern',
    'complete_pattern': 'pattern',
    'pattern': 'pattern',
    'memory-match': 'memory_match',
    'memory_match': 'memory_match',
    'tracing': 'tracing',
    'spot-find': 'spot_find',
    'spot_find': 'spot_find',
  };

  static String normalizeType(String? raw) {
    final key = (raw ?? 'multiple_choice').trim().toLowerCase();
    return typeAliases[key] ?? typeAliases[key.replaceAll('-', '_')] ?? key.replaceAll('-', '_');
  }

  /// Returns null when the question is not scored at all.
  bool? isCorrect(Map<String, dynamic> question, Map<String, dynamic> answer) {
    final type = normalizeType(question['type'] as String?);
    final response = _responseOf(answer);
    final options = _optionsOf(question);

    switch (type) {
      case 'multiple_choice':
      case 'listen_choose':
      case 'true_false':
      case 'fill_blank':
      case 'count_objects':
      case 'pattern':
        return _singleChoice(options, response);
      case 'matching':
        return _matching(options, response);
      case 'drag_sort':
        return _dragSort(options, question, response);
      case 'drag_sequence':
        return _sequence(options, response);
      case 'memory_match':
        return _memoryMatch(options, response);
      case 'spot_find':
        return _spotFind(question, response);
      case 'speak_repeat':
        return _speakRepeat(response);
      case 'tracing':
        // Tracing is practice on the web too: doing it is passing it.
        return true;
      default:
        return _singleChoice(options, response);
    }
  }

  /// Score a whole attempt.
  MissionScore scoreMission({
    required List<Map<String, dynamic>> questions,
    required List<Map<String, dynamic>> answers,
    int passThresholdPercent = 60,
    int? cap,
  }) {
    final byId = <int, Map<String, dynamic>>{};
    for (final question in questions) {
      byId[(question['id'] as num).toInt()] = question;
    }

    var score = 0;
    var total = 0;
    final perQuestion = <int, bool>{};
    final seen = <int>{};

    for (final answer in answers) {
      final questionId = (answer['question_id'] as num?)?.toInt() ?? (answer['id'] as num?)?.toInt() ?? 0;
      final question = byId[questionId];

      if (question == null || !seen.add(questionId)) continue;
      if (cap != null && total >= cap) continue;

      final correct = isCorrect(question, answer);
      if (correct == null) continue;

      total++;
      perQuestion[questionId] = correct;
      if (correct) score++;
    }

    final percentage = total > 0 ? ((score / total) * 100).round() : 0;

    return MissionScore(
      score: score,
      total: total,
      percentage: percentage,
      stars: starsFor(score, total),
      passed: total > 0 && percentage >= passThresholdPercent,
      perQuestion: perQuestion,
    );
  }

  /// The same thresholds as the web quiz engine and the server: 90, 60, 30.
  static int starsFor(int score, int total) {
    if (total <= 0) return 0;
    final pct = (score / total) * 100;
    if (pct >= 90) return 3;
    if (pct >= 60) return 2;
    if (pct >= 30) return 1;
    return 0;
  }

  // ── per type ───────────────────────────────────────────────────────────────

  bool _singleChoice(List<Map<String, dynamic>> options, Map<String, dynamic> response) {
    final chosen = response['option_id'] ?? response['selected'];
    if (chosen == null) return false;

    final chosenId = (chosen is num) ? chosen.toInt() : int.tryParse(chosen.toString());
    if (chosenId == null) return false;

    for (final option in options) {
      if ((option['id'] as num?)?.toInt() == chosenId) {
        return option['is_correct'] == true;
      }
    }

    return false;
  }

  bool _matching(List<Map<String, dynamic>> options, Map<String, dynamic> response) {
    final pairs = response['pairs'];
    if (pairs is! List || pairs.isEmpty) return false;

    final keys = <int, String>{};
    for (final option in options) {
      keys[(option['id'] as num).toInt()] = _key(option['match_key']);
    }

    final expected = keys.values.where((k) => k.isNotEmpty).toSet().length;
    var matched = 0;

    for (final pair in pairs) {
      int? left;
      int? right;

      if (pair is List && pair.length >= 2) {
        left = (pair[0] as num?)?.toInt();
        right = (pair[1] as num?)?.toInt();
      } else if (pair is Map) {
        left = (pair['left'] ?? pair['left_id']) is num ? ((pair['left'] ?? pair['left_id']) as num).toInt() : null;
        right = (pair['right'] ?? pair['right_id']) is num ? ((pair['right'] ?? pair['right_id']) as num).toInt() : null;
      }

      if (left == null || right == null) return false;

      final leftKey = keys[left];
      final rightKey = keys[right];

      if (leftKey == null || leftKey.isEmpty || leftKey != rightKey) return false;
      matched++;
    }

    return expected > 0 && matched >= expected;
  }

  bool _dragSort(List<Map<String, dynamic>> options, Map<String, dynamic> question, Map<String, dynamic> response) {
    final placements = response['placements'] ?? response['buckets'];
    if (placements is! Map || placements.isEmpty) return false;

    final categories = (question['scoring_config'] as Map?)?['categories'];

    for (final option in options) {
      final id = (option['id'] as num).toInt();
      var bucket = _key(option['match_key']);

      if (bucket.isEmpty && categories is Map) {
        bucket = _key(categories[id] ?? categories['$id']);
      }

      final placed = _key(placements[id] ?? placements['$id']);

      if (bucket.isEmpty || placed != bucket) return false;
    }

    return true;
  }

  bool _sequence(List<Map<String, dynamic>> options, Map<String, dynamic> response) {
    final order = response['order'] ?? response['slots'];
    if (order is! List || order.isEmpty) return false;

    final sorted = [...options]..sort(
        (a, b) => ((a['sort_order'] as num?)?.toInt() ?? 0).compareTo((b['sort_order'] as num?)?.toInt() ?? 0),
      );

    final expected = sorted.map((o) => (o['id'] as num).toInt()).toList();
    final given = order
        .map((value) => value is Map ? (value['id'] as num?)?.toInt() ?? 0 : (value as num?)?.toInt() ?? 0)
        .toList();

    if (expected.length != given.length) return false;

    for (var i = 0; i < expected.length; i++) {
      if (expected[i] != given[i]) return false;
    }

    return true;
  }

  bool _memoryMatch(List<Map<String, dynamic>> options, Map<String, dynamic> response) {
    final found = (response['pairs_found'] as num?)?.toInt() ?? 0;

    final keys = options.map((o) => _key(o['match_key'])).where((k) => k.isNotEmpty).toSet();
    final expected = keys.isNotEmpty ? keys.length : options.length ~/ 2;

    return expected > 0 && found >= expected;
  }

  bool _spotFind(Map<String, dynamic> question, Map<String, dynamic> response) {
    final metadata = (question['metadata'] as Map?) ?? const {};
    final hotspots = metadata['hotspots'];
    final hits = response['hits'];

    if (hotspots is! List || hotspots.isEmpty || hits is! List) return false;

    final radius = ((metadata['hotspot_radius'] ??
                (question['scoring_config'] as Map?)?['hotspot_radius'] ??
                12) as num)
        .toDouble();

    for (final spot in hotspots) {
      if (spot is! Map) return false;

      final sx = (spot['x'] as num?)?.toDouble() ?? -1;
      final sy = (spot['y'] as num?)?.toDouble() ?? -1;
      var hit = false;

      for (final candidate in hits) {
        if (candidate is! Map) continue;

        final dx = ((candidate['x'] as num?)?.toDouble() ?? 999) - sx;
        final dy = ((candidate['y'] as num?)?.toDouble() ?? 999) - sy;

        if (math.sqrt(dx * dx + dy * dy) <= radius) {
          hit = true;
          break;
        }
      }

      if (!hit) return false;
    }

    return true;
  }

  /// A child on a device with no working microphone still did the activity.
  /// Recognized counts, practice counts, only skipping does not.
  bool _speakRepeat(Map<String, dynamic> response) {
    final mode = (response['mode'] as String? ?? 'practice').toLowerCase();
    if (mode == 'recognized') return true;
    if (mode == 'skipped') return false;
    return true;
  }

  // ── helpers ────────────────────────────────────────────────────────────────

  Map<String, dynamic> _responseOf(Map<String, dynamic> answer) {
    final response = answer['response'];
    if (response is Map) return response.cast<String, dynamic>();
    return answer;
  }

  List<Map<String, dynamic>> _optionsOf(Map<String, dynamic> question) {
    final options = question['options'];
    if (options is! List) return const [];
    return options.whereType<Map>().map((e) => e.cast<String, dynamic>()).toList();
  }

  String _key(dynamic value) => value == null ? '' : value.toString().trim().toLowerCase();
}

class MissionScore {
  const MissionScore({
    required this.score,
    required this.total,
    required this.percentage,
    required this.stars,
    required this.passed,
    this.perQuestion = const {},
  });

  final int score;
  final int total;
  final int percentage;
  final int stars;
  final bool passed;
  final Map<int, bool> perQuestion;

  Map<String, dynamic> toJson() => {
        'score': score,
        'total': total,
        'percentage': percentage,
        'stars': stars,
        'passed': passed,
      };
}
