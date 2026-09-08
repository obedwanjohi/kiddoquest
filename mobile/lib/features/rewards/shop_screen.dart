import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/providers.dart';
import '../../core/models/child.dart';
import '../../core/models/learning_event.dart';
import '../../core/platform/form_factor.dart';
import '../../design/components/counters.dart';
import '../../design/components/focus_ring.dart';
import '../../design/components/kid_scaffold.dart';
import '../../design/components/mascot_stage.dart';
import '../../design/tokens.dart';

/// What star coins are for.
///
/// Prices come from the server's config, so they can be changed without an app
/// release and the server can check a purchase against the same numbers the
/// child was shown. Buying works offline: the coins come off immediately, the
/// event goes to the outbox, and the server settles the ledger on sync. A child
/// never watches something they bought disappear.
class ShopScreen extends ConsumerStatefulWidget {
  const ShopScreen({super.key});

  @override
  ConsumerState<ShopScreen> createState() => _ShopScreenState();
}

class _ShopScreenState extends ConsumerState<ShopScreen> {
  bool _showingHats = false;
  String? _message;

  /// Characters and hats a child can wear, drawn from the same identifiers the
  /// website already stores on the child record.
  static const Map<String, String> _characterNames = {
    'char_panda': 'Pip the Panda',
    'char_unicorn': 'Uma the Unicorn',
    'char_koala': 'Koko the Koala',
    'char_dino': 'Rex the Dino',
    'char_robot': 'Beep the Robot',
    'char_dragon': 'Ignis the Dragon',
  };

  static const Map<String, String> _hatNames = {
    'hat_star': 'Star',
    'hat_party': 'Party',
    'hat_pirate': 'Pirate',
    'hat_sunglasses': 'Sunglasses',
    'hat_crown': 'Crown',
    'hat_superhero': 'Superhero',
    'hat_dino': 'Dino',
    'hat_astronaut': 'Astronaut',
  };

  Future<void> _buy(Child child, String itemId, int cost, {required bool isHat}) async {
    final owned = child.unlockedItems.contains(itemId) || cost == 0;

    if (!owned && child.starCoins < cost) {
      setState(() => _message = 'You need ${cost - child.starCoins} more coins for that. Keep playing!');
      return;
    }

    final outbox = ref.read(outboxProvider);
    final progress = ref.read(progressDaoProvider);

    if (!owned) {
      await outbox.add(
        type: LearningEvent.shopPurchased,
        childId: child.id,
        payload: {'item_id': itemId, 'cost': cost},
      );
    }

    await outbox.add(
      type: LearningEvent.shopEquipped,
      childId: child.id,
      payload: {'item_id': itemId, 'type': isHat ? 'hat' : 'character'},
    );

    // Show the change now. The server recomputes the ledger on sync and its
    // snapshot wins, but a child should never wait for a network to see the hat
    // they just put on.
    final updated = child.copyWith(
      starCoins: owned ? child.starCoins : child.starCoins - cost,
      unlockedItems: child.unlockedItems.contains(itemId)
          ? child.unlockedItems
          : [...child.unlockedItems, itemId],
      equippedHat: isHat ? itemId : child.equippedHat,
      avatar: isHat ? child.avatar : itemId.replaceFirst('char_', ''),
    );

    await progress.saveChild(updated);
    ref.read(sessionProvider.notifier).updateActiveChild(updated);

    setState(() => _message = owned ? 'Looking good!' : 'Yours! Enjoy it.');

    unawaited(ref.read(syncEngineProvider).syncNow(childId: child.id, reason: 'shop'));
  }

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);
    final child = ref.watch(sessionProvider).activeChild;
    final config = ref.watch(appConfigProvider).value;

    if (child == null) {
      return const KidScaffold(
        child: LeoMessage(
          title: 'Who is shopping?',
          body: 'Pick an explorer first.',
        ),
      );
    }

    final prices = (config?.shopPrices ?? const {});
    final names = _showingHats ? _hatNames : _characterNames;

    return KidScaffold(
      onBack: () => context.go('/map'),
      child: Column(
        children: [
          SizedBox(height: KidSpacing.sm * formFactor.density),
          Row(
            children: [
              IconButton(
                onPressed: () => context.go('/map'),
                icon: const Icon(Icons.arrow_back_rounded),
                tooltip: 'Back to the map',
              ),
              Expanded(
                child: Text('Leo’s Shop 🛍️', style: theme.textTheme.titleLarge),
              ),
              IconButton(
                onPressed: () => context.go('/stickers'),
                icon: const Text('📔', style: TextStyle(fontSize: 22)),
                tooltip: 'My stickers',
              ),
              CoinCounter(coins: child.starCoins),
            ],
          ),
          SizedBox(height: KidSpacing.md * formFactor.density),
          _Tabs(
            showingHats: _showingHats,
            onChanged: (value) => setState(() {
              _showingHats = value;
              _message = null;
            }),
          ),
          if (_message != null) ...[
            SizedBox(height: KidSpacing.sm * formFactor.density),
            Text(_message!, textAlign: TextAlign.center, style: theme.textTheme.titleMedium),
          ],
          SizedBox(height: KidSpacing.md * formFactor.density),
          Expanded(
            child: GridView.builder(
              gridDelegate: SliverGridDelegateWithMaxCrossAxisExtent(
                maxCrossAxisExtent: formFactor.isTv ? 240 : 170,
                mainAxisSpacing: KidSpacing.md * formFactor.density,
                crossAxisSpacing: KidSpacing.md * formFactor.density,
                childAspectRatio: 0.85,
              ),
              itemCount: names.length,
              itemBuilder: (context, index) {
                final itemId = names.keys.elementAt(index);
                final cost = prices[itemId] ?? 0;
                final owned = cost == 0 || child.unlockedItems.contains(itemId);
                final worn = _showingHats
                    ? child.equippedHat == itemId
                    : child.avatar == itemId.replaceFirst('char_', '');

                return _ShopCard(
                  name: names[itemId]!,
                  emoji: _showingHats
                      ? (MascotStage.hats[itemId] ?? '🎩')
                      : MascotStage.emojiFor(itemId.replaceFirst('char_', '')),
                  cost: cost,
                  owned: owned,
                  worn: worn,
                  affordable: owned || child.starCoins >= cost,
                  autofocus: index == 0,
                  onPressed: () => _buy(child, itemId, cost, isHat: _showingHats),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}

class _Tabs extends StatelessWidget {
  const _Tabs({required this.showingHats, required this.onChanged});

  final bool showingHats;
  final ValueChanged<bool> onChanged;

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);

    Widget tab(String label, String emoji, bool active, VoidCallback onTap) {
      return KidFocusable(
        onPressed: onTap,
        borderRadius: KidRadius.pill,
        semanticLabel: label,
        child: Container(
          height: KidTouch.min * formFactor.density,
          padding: EdgeInsets.symmetric(horizontal: KidSpacing.lg * formFactor.density),
          alignment: Alignment.center,
          decoration: BoxDecoration(
            color: active ? KidColors.primary : theme.colorScheme.surface,
            borderRadius: KidRadius.pill,
            border: Border.all(color: active ? KidColors.primaryDark : theme.colorScheme.outline, width: 2),
          ),
          child: Text(
            '$emoji  $label',
            style: theme.textTheme.titleMedium?.copyWith(
              color: active ? Colors.white : theme.colorScheme.onSurface,
            ),
          ),
        ),
      );
    }

    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        tab('Characters', '🐼', !showingHats, () => onChanged(false)),
        SizedBox(width: KidSpacing.sm * formFactor.density),
        tab('Hats', '👑', showingHats, () => onChanged(true)),
      ],
    );
  }
}

class _ShopCard extends StatelessWidget {
  const _ShopCard({
    required this.name,
    required this.emoji,
    required this.cost,
    required this.owned,
    required this.worn,
    required this.affordable,
    required this.onPressed,
    this.autofocus = false,
  });

  final String name;
  final String emoji;
  final int cost;
  final bool owned;
  final bool worn;
  final bool affordable;
  final VoidCallback onPressed;
  final bool autofocus;

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);

    return KidFocusable(
      onPressed: onPressed,
      autofocus: autofocus,
      semanticLabel: worn ? '$name, worn' : (owned ? '$name, owned' : '$name, $cost coins'),
      child: Container(
        padding: EdgeInsets.all(KidSpacing.sm * formFactor.density),
        decoration: BoxDecoration(
          color: theme.colorScheme.surface,
          borderRadius: KidRadius.card,
          border: Border.all(
            color: worn ? KidColors.success : (affordable ? KidColors.primaryLight : KidColors.border),
            width: worn ? 4 : 3,
          ),
          boxShadow: KidShadows.edge(
            (worn ? KidColors.success : KidColors.primaryLight).withValues(alpha: 0.5),
          ),
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          mainAxisSize: MainAxisSize.min,
          children: [
            Flexible(
              child: FittedBox(
                child: Opacity(
                  opacity: affordable ? 1 : 0.45,
                  child: Text(emoji, style: TextStyle(fontSize: 54 * formFactor.density)),
                ),
              ),
            ),
            SizedBox(height: KidSpacing.xs * formFactor.density),
            Text(
              name,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: theme.textTheme.titleMedium,
            ),
            SizedBox(height: KidSpacing.xs * formFactor.density),
            if (worn)
              const KidCounter(icon: '✅', value: 'Wearing', background: KidColors.successSoft)
            else if (owned)
              const KidCounter(icon: '👕', value: 'Wear it', background: KidColors.primarySoft)
            else
              KidCounter(
                icon: '🪙',
                value: '$cost',
                background: affordable ? const Color(0xFFFDE68A) : const Color(0xFFF3F4F6),
              ),
          ],
        ),
      ),
    );
  }
}
