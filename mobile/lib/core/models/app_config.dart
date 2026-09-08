/// Everything the server can change without shipping a new app: prices, how
/// many questions a level gets, which features are on, and the oldest build
/// still allowed to talk to the API.
class AppConfig {
  const AppConfig({
    this.minAppVersion = '1.0.0',
    this.timezone = 'Africa/Nairobi',
    this.currency = 'KES',
    this.plans = const [],
    this.subscriptionEnforced = false,
    this.offlineGraceDays = 7,
    this.paybill,
    this.questionsPerLevel = const {'PG': 6, 'PP1': 8, 'PP2': 8},
    this.questionsDefault = 8,
    this.passThresholdPercent = 60,
    this.exclusionWindowDays = 7,
    this.maxEventsPerSync = 200,
    this.features = const {},
    this.shopPrices = const {},
    this.chestEveryMissions = 0,
    this.chestCoins = 0,
    this.maintenanceBanner,
  });

  final String minAppVersion;
  final String timezone;
  final String currency;
  final List<PlanOption> plans;
  final bool subscriptionEnforced;
  final int offlineGraceDays;
  final String? paybill;
  final Map<String, int> questionsPerLevel;
  final int questionsDefault;
  final int passThresholdPercent;
  final int exclusionWindowDays;
  final int maxEventsPerSync;
  final Map<String, bool> features;

  /// Item id to price in coins, for hats and characters together. The server
  /// checks a purchase against these same numbers.
  final Map<String, int> shopPrices;

  /// A treasure chest every this many missions, worth this many coins. Zero
  /// either side switches chests off without an app release.
  final int chestEveryMissions;
  final int chestCoins;

  final String? maintenanceBanner;

  bool feature(String key, {bool fallback = false}) => features[key] ?? fallback;

  int questionsFor(String levelCode) => questionsPerLevel[levelCode] ?? questionsDefault;

  factory AppConfig.fromJson(Map<String, dynamic> json) {
    final subscription = (json['subscription'] as Map?)?.cast<String, dynamic>() ?? const {};
    final session = (json['session'] as Map?)?.cast<String, dynamic>() ?? const {};
    final sync = (json['sync'] as Map?)?.cast<String, dynamic>() ?? const {};
    final rewards = (json['rewards'] as Map?)?.cast<String, dynamic>() ?? const {};
    final chest = (rewards['chest'] as Map?)?.cast<String, dynamic>() ?? const {};

    return AppConfig(
      minAppVersion: json['min_app_version'] as String? ?? '1.0.0',
      timezone: json['timezone'] as String? ?? 'Africa/Nairobi',
      currency: json['currency'] as String? ?? 'KES',
      plans: ((json['plans'] as List?) ?? const [])
          .map((e) => PlanOption.fromJson((e as Map).cast<String, dynamic>()))
          .toList(),
      subscriptionEnforced: subscription['enforced'] as bool? ?? false,
      offlineGraceDays: (subscription['offline_grace_days'] as num?)?.toInt() ?? 7,
      paybill: subscription['paybill']?.toString(),
      questionsPerLevel: ((session['questions_per_level'] as Map?) ?? const {})
          .map((key, value) => MapEntry(key.toString(), (value as num).toInt())),
      questionsDefault: (session['questions_default'] as num?)?.toInt() ?? 8,
      passThresholdPercent: (session['pass_threshold_percent'] as num?)?.toInt() ?? 60,
      exclusionWindowDays: (session['exclusion_window_days'] as num?)?.toInt() ?? 7,
      maxEventsPerSync: (sync['max_events_per_request'] as num?)?.toInt() ?? 200,
      features: ((json['features'] as Map?) ?? const {})
          .map((key, value) => MapEntry(key.toString(), value == true)),
      shopPrices: _shopPrices(json['shop']),
      chestEveryMissions: (chest['every_missions'] as num?)?.toInt() ?? 0,
      chestCoins: (chest['coins'] as num?)?.toInt() ?? 0,
      maintenanceBanner: json['maintenance_banner'] as String?,
    );
  }
}

/// The server groups shop items by kind; the app only needs the prices.
Map<String, int> _shopPrices(dynamic shop) {
  if (shop is! Map) return const {};

  final prices = <String, int>{};

  for (final group in shop.values) {
    if (group is! Map) continue;

    group.forEach((key, value) {
      if (value is num) prices[key.toString()] = value.toInt();
    });
  }

  return prices;
}

class PlanOption {
  const PlanOption({
    required this.key,
    required this.name,
    required this.amount,
    required this.days,
    this.blurb,
    this.emoji,
    this.badge,
    this.highlight = false,
  });

  final String key;
  final String name;
  final int amount;
  final int days;
  final String? blurb;
  final String? emoji;
  final String? badge;
  final bool highlight;

  factory PlanOption.fromJson(Map<String, dynamic> json) => PlanOption(
        key: json['key'] as String,
        name: json['name'] as String? ?? '',
        amount: (json['amount'] as num?)?.toInt() ?? 0,
        days: (json['days'] as num?)?.toInt() ?? 30,
        blurb: json['blurb'] as String?,
        emoji: json['emoji'] as String?,
        badge: json['badge'] as String?,
        highlight: json['highlight'] as bool? ?? false,
      );
}
