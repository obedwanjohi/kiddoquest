import 'package:flutter_test/flutter_test.dart';
import 'package:kiddoquest/core/models/app_config.dart';

/// The config endpoint is how prices, question caps and feature flags change
/// without an app release, so parsing it has to survive whatever the server
/// sends, including nothing at all.
void main() {
  test('it reads the shop prices the server groups by kind', () {
    final config = AppConfig.fromJson(const {
      'shop': {
        'hats': {'hat_star': 0, 'hat_crown': 50},
        'characters': {'char_panda': 0, 'char_dragon': 30},
      },
    });

    expect(config.shopPrices['hat_crown'], 50);
    expect(config.shopPrices['char_dragon'], 30);
    expect(config.shopPrices['hat_star'], 0);
    expect(config.shopPrices['nothing_like_this'], isNull);
  });

  test('it reads the question caps per level', () {
    final config = AppConfig.fromJson(const {
      'session': {
        'questions_per_level': {'PG': 6, 'PP1': 8},
        'questions_default': 8,
      },
    });

    expect(config.questionsFor('PG'), 6);
    expect(config.questionsFor('PP1'), 8);
    expect(config.questionsFor('GRADE9'), 8, reason: 'an unknown level falls back to the default');
  });

  test('it reads prices and the subscription switch', () {
    final config = AppConfig.fromJson(const {
      'currency': 'KES',
      'plans': [
        {'key': 'monthly', 'name': 'Monthly', 'amount': 200, 'days': 30},
        {'key': 'annual', 'name': 'Annual', 'amount': 1800, 'days': 365, 'badge': 'Save 25%'},
      ],
      'subscription': {'enforced': false, 'offline_grace_days': 7},
    });

    expect(config.plans, hasLength(2));
    expect(config.plans.first.amount, 200);
    expect(config.plans.last.badge, 'Save 25%');
    expect(config.subscriptionEnforced, isFalse);
    expect(config.offlineGraceDays, 7);
  });

  test('feature flags read as booleans and default to off', () {
    final config = AppConfig.fromJson(const {
      'features': {'ai_coach': true, 'treasure_chests': false},
    });

    expect(config.feature('ai_coach'), isTrue);
    expect(config.feature('treasure_chests'), isFalse);
    expect(config.feature('not_a_real_flag'), isFalse);
  });

  test('an empty reply still yields a usable config', () {
    final config = AppConfig.fromJson(const {});

    expect(config.minAppVersion, isNotEmpty);
    expect(config.questionsFor('PG'), greaterThan(0));
    expect(config.shopPrices, isEmpty);
    expect(config.plans, isEmpty);
  });
}
