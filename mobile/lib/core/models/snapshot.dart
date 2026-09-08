import 'child.dart';

/// The server's word on where a child stands.
///
/// The app keeps its own provisional copy so play is instant offline; when a
/// snapshot arrives it wins, because the server is the ledger.
class ChildSnapshot {
  const ChildSnapshot({
    required this.child,
    this.progress = const [],
    this.badges = const [],
    this.entitlement = const Entitlement(),
    this.contentVersions = const {},
    this.serverTime,
  });

  final Child child;
  final List<ProgressEntry> progress;
  final List<EarnedBadge> badges;
  final Entitlement entitlement;
  final Map<String, int> contentVersions;
  final DateTime? serverTime;

  factory ChildSnapshot.fromJson(Map<String, dynamic> json) => ChildSnapshot(
        child: Child.fromJson((json['child'] as Map).cast<String, dynamic>()),
        progress: ((json['progress'] as List?) ?? const [])
            .map((e) => ProgressEntry.fromJson((e as Map).cast<String, dynamic>()))
            .toList(),
        badges: ((json['badges'] as List?) ?? const [])
            .map((e) => EarnedBadge.fromJson((e as Map).cast<String, dynamic>()))
            .toList(),
        entitlement: Entitlement.fromJson(
          (json['entitlement'] as Map?)?.cast<String, dynamic>() ?? const {},
        ),
        contentVersions: ((json['content_versions'] as Map?) ?? const {})
            .map((key, value) => MapEntry(key.toString(), (value as num).toInt())),
        serverTime: DateTime.tryParse(json['server_time'] as String? ?? ''),
      );
}

/// Something a child has earned once and keeps. The server decides these, so a
/// device cannot award itself one.
class EarnedBadge {
  const EarnedBadge({required this.key, required this.name, this.icon, this.blurb, this.awardedAt});

  final String key;
  final String name;
  final String? icon;
  final String? blurb;
  final DateTime? awardedAt;

  factory EarnedBadge.fromJson(Map<String, dynamic> json) => EarnedBadge(
        key: json['key'] as String? ?? '',
        name: json['name'] as String? ?? '',
        icon: json['icon'] as String?,
        blurb: json['blurb'] as String?,
        awardedAt: DateTime.tryParse(json['awarded_at'] as String? ?? ''),
      );

  Map<String, dynamic> toJson() => {
        'key': key,
        'name': name,
        'icon': icon,
        'blurb': blurb,
        'awarded_at': awardedAt?.toIso8601String(),
      };
}

class ProgressEntry {
  const ProgressEntry({
    required this.missionId,
    required this.status,
    this.starsEarned = 0,
    this.completedAt,
  });

  final int missionId;
  final String status;
  final int starsEarned;
  final DateTime? completedAt;

  bool get isCompleted => status == 'completed';

  factory ProgressEntry.fromJson(Map<String, dynamic> json) => ProgressEntry(
        missionId: (json['mission_id'] as num).toInt(),
        status: json['status'] as String? ?? 'in_progress',
        starsEarned: (json['stars_earned'] as num?)?.toInt() ?? 0,
        completedAt: DateTime.tryParse(json['completed_at'] as String? ?? ''),
      );
}

/// What the family has paid for, and how long paid worlds keep working after
/// the subscription lapses while they are away from the internet.
class Entitlement {
  const Entitlement({
    this.plan,
    this.status = 'none',
    this.expiresAt,
    this.offlineGraceDays = 7,
    this.enforced = false,
  });

  final String? plan;
  final String status;
  final DateTime? expiresAt;
  final int offlineGraceDays;
  final bool enforced;

  bool get isActive => status == 'active' && (expiresAt?.isAfter(DateTime.now()) ?? false);

  /// Paid worlds stay playable through the grace window, which is what makes a
  /// dropped connection on a Sunday afternoon a non-event.
  bool get isWithinGrace {
    if (isActive) return true;
    final expiry = expiresAt;
    if (expiry == null) return false;
    return DateTime.now().isBefore(expiry.add(Duration(days: offlineGraceDays)));
  }

  bool allows({required bool isFreeWorld}) {
    if (isFreeWorld || !enforced) return true;
    return isWithinGrace;
  }

  factory Entitlement.fromJson(Map<String, dynamic> json) => Entitlement(
        plan: json['plan'] as String?,
        status: json['status'] as String? ?? 'none',
        expiresAt: DateTime.tryParse(json['expires_at'] as String? ?? ''),
        offlineGraceDays: (json['offline_grace_days'] as num?)?.toInt() ?? 7,
        enforced: json['enforced'] as bool? ?? false,
      );

  Map<String, dynamic> toJson() => {
        'plan': plan,
        'status': status,
        'expires_at': expiresAt?.toIso8601String(),
        'offline_grace_days': offlineGraceDays,
        'enforced': enforced,
      };
}
