/// Everything a child does becomes one of these, written to the outbox before
/// anything else happens.
///
/// The device owns the id, so sending the same event twice is harmless and the
/// app can retry for as long as it takes to find a network.
class LearningEvent {
  const LearningEvent({
    required this.id,
    required this.type,
    required this.clientTs,
    required this.seq,
    this.childId,
    this.payload = const {},
  });

  final String id;
  final String type;
  final DateTime clientTs;
  final int seq;
  final int? childId;
  final Map<String, dynamic> payload;

  static const String sessionStarted = 'session_started';
  static const String sessionHeartbeat = 'session_heartbeat';
  static const String sessionEnded = 'session_ended';
  static const String missionStarted = 'mission_started';
  static const String questionAnswered = 'question_answered';
  static const String missionCompleted = 'mission_completed';
  static const String missionAbandoned = 'mission_abandoned';
  static const String videoWatched = 'video_watched';
  static const String shopPurchased = 'shop_purchased';
  static const String shopEquipped = 'shop_equipped';
  static const String devotionalViewed = 'devotional_viewed';
  static const String songsOpened = 'songs_opened';
  static const String badgeClaimed = 'badge_claimed';

  /// The timestamp carries the device's own offset on purpose: streaks and
  /// screen time are counted in the family's local day, not in UTC.
  Map<String, dynamic> toJson() => {
        'id': id,
        'type': type,
        'seq': seq,
        'client_ts': clientTs.toIso8601String(),
        if (childId != null) 'child_id': childId,
        'payload': payload,
      };

  factory LearningEvent.fromJson(Map<String, dynamic> json) => LearningEvent(
        id: json['id'] as String,
        type: json['type'] as String,
        seq: (json['seq'] as num?)?.toInt() ?? 0,
        clientTs: DateTime.tryParse(json['client_ts'] as String? ?? '') ?? DateTime.now(),
        childId: (json['child_id'] as num?)?.toInt(),
        payload: ((json['payload'] as Map?) ?? const {}).cast<String, dynamic>(),
      );
}

/// What came back from a sync attempt.
class SyncResult {
  const SyncResult({
    this.accepted = const [],
    this.rejected = const [],
    this.cursor,
    this.snapshotJson,
  });

  final List<String> accepted;
  final List<RejectedEvent> rejected;
  final String? cursor;
  final Map<String, dynamic>? snapshotJson;

  factory SyncResult.fromJson(Map<String, dynamic> json) => SyncResult(
        accepted: ((json['accepted'] as List?) ?? const []).map((e) => e.toString()).toList(),
        rejected: ((json['rejected'] as List?) ?? const [])
            .map((e) => RejectedEvent.fromJson((e as Map).cast<String, dynamic>()))
            .toList(),
        cursor: json['cursor'] as String?,
        snapshotJson: (json['snapshot'] as Map?)?.cast<String, dynamic>(),
      );
}

class RejectedEvent {
  const RejectedEvent({required this.id, required this.reason});

  final String id;
  final String reason;

  factory RejectedEvent.fromJson(Map<String, dynamic> json) => RejectedEvent(
        id: json['id']?.toString() ?? '',
        reason: json['reason'] as String? ?? 'unknown',
      );
}
