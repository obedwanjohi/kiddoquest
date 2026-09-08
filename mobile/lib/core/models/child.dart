/// A child profile, as the server sees it.
///
/// Written by hand rather than generated: the app has one API and a handful of
/// models, and a build step that has to run before the project compiles is a
/// poor trade for that.
class Child {
  const Child({
    required this.id,
    required this.name,
    this.avatar = 'lion',
    this.level,
    this.favoriteColor,
    this.birthdate,
    this.totalStars = 0,
    this.starCoins = 0,
    this.streakDays = 0,
    this.unlockedItems = const [],
    this.equippedHat,
    this.dailyTimeLimitMinutes = 0,
    this.assignedMissionId,
    this.todayPlayedSeconds = 0,
    this.lastPlayedAt,
  });

  final int id;
  final String name;
  final String avatar;
  final String? level;
  final String? favoriteColor;
  final String? birthdate;
  final int totalStars;
  final int starCoins;
  final int streakDays;
  final List<String> unlockedItems;
  final String? equippedHat;
  final int dailyTimeLimitMinutes;
  final int? assignedMissionId;
  final int todayPlayedSeconds;
  final DateTime? lastPlayedAt;

  bool get hasTimeLimit => dailyTimeLimitMinutes > 0;

  int get minutesLeftToday {
    if (!hasTimeLimit) return 0;
    final used = (todayPlayedSeconds / 60).round();
    final left = dailyTimeLimitMinutes - used;
    return left < 0 ? 0 : left;
  }

  bool get outOfTime => hasTimeLimit && minutesLeftToday <= 0;

  /// The database stores "Play Group" and "PP1"; content packs use codes.
  String get levelCode {
    final value = (level ?? '').toUpperCase();
    if (value.contains('PLAY') || value == 'PG') return 'PG';
    if (value.contains('PP1')) return 'PP1';
    if (value.contains('PP2')) return 'PP2';
    return value.isEmpty ? 'PG' : value;
  }

  factory Child.fromJson(Map<String, dynamic> json) => Child(
        id: (json['id'] as num).toInt(),
        name: json['name'] as String? ?? 'Explorer',
        avatar: json['avatar'] as String? ?? 'lion',
        level: json['level'] as String?,
        favoriteColor: json['favorite_color'] as String?,
        birthdate: json['birthdate'] as String?,
        totalStars: (json['total_stars'] as num?)?.toInt() ?? 0,
        starCoins: (json['star_coins'] as num?)?.toInt() ?? 0,
        streakDays: (json['streak_days'] as num?)?.toInt() ?? 0,
        unlockedItems: (json['unlocked_items'] as List?)?.map((e) => e.toString()).toList() ?? const [],
        equippedHat: json['equipped_hat'] as String?,
        dailyTimeLimitMinutes: (json['daily_time_limit_minutes'] as num?)?.toInt() ?? 0,
        assignedMissionId: (json['assigned_mission_id'] as num?)?.toInt(),
        todayPlayedSeconds: (json['today_played_seconds'] as num?)?.toInt() ?? 0,
        lastPlayedAt: DateTime.tryParse(json['last_played_at'] as String? ?? ''),
      );

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        'avatar': avatar,
        'level': level,
        'favorite_color': favoriteColor,
        'birthdate': birthdate,
        'total_stars': totalStars,
        'star_coins': starCoins,
        'streak_days': streakDays,
        'unlocked_items': unlockedItems,
        'equipped_hat': equippedHat,
        'daily_time_limit_minutes': dailyTimeLimitMinutes,
        'assigned_mission_id': assignedMissionId,
        'today_played_seconds': todayPlayedSeconds,
        'last_played_at': lastPlayedAt?.toIso8601String(),
      };

  Child copyWith({
    String? name,
    String? avatar,
    String? level,
    int? totalStars,
    int? starCoins,
    int? streakDays,
    List<String>? unlockedItems,
    String? equippedHat,
    int? dailyTimeLimitMinutes,
    int? assignedMissionId,
    int? todayPlayedSeconds,
  }) =>
      Child(
        id: id,
        name: name ?? this.name,
        avatar: avatar ?? this.avatar,
        level: level ?? this.level,
        favoriteColor: favoriteColor,
        birthdate: birthdate,
        totalStars: totalStars ?? this.totalStars,
        starCoins: starCoins ?? this.starCoins,
        streakDays: streakDays ?? this.streakDays,
        unlockedItems: unlockedItems ?? this.unlockedItems,
        equippedHat: equippedHat ?? this.equippedHat,
        dailyTimeLimitMinutes: dailyTimeLimitMinutes ?? this.dailyTimeLimitMinutes,
        assignedMissionId: assignedMissionId ?? this.assignedMissionId,
        todayPlayedSeconds: todayPlayedSeconds ?? this.todayPlayedSeconds,
        lastPlayedAt: lastPlayedAt,
      );
}

class Guardian {
  const Guardian({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    this.hasCustomPin = false,
    this.enableDevotional = true,
    this.enableSongsHub = true,
  });

  final int id;
  final String name;
  final String email;
  final String? phone;
  final bool hasCustomPin;
  final bool enableDevotional;
  final bool enableSongsHub;

  factory Guardian.fromJson(Map<String, dynamic> json) => Guardian(
        id: (json['id'] as num).toInt(),
        name: json['name'] as String? ?? '',
        email: json['email'] as String? ?? '',
        phone: json['phone'] as String?,
        hasCustomPin: json['has_custom_pin'] as bool? ?? false,
        enableDevotional: json['enable_devotional'] as bool? ?? true,
        enableSongsHub: json['enable_songs_hub'] as bool? ?? true,
      );

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        'email': email,
        'phone': phone,
        'has_custom_pin': hasCustomPin,
        'enable_devotional': enableDevotional,
        'enable_songs_hub': enableSongsHub,
      };
}
