import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/providers.dart';
import '../../core/db/content_dao.dart';
import '../../core/platform/form_factor.dart';
import '../../design/components/kid_button.dart';
import '../../design/components/kid_scaffold.dart';
import '../../design/tokens.dart';

/// The sticker book.
///
/// A sticker is a mission the child has finished, so there is nothing to award
/// and nothing to sync: the book is the progress table, drawn as a page of
/// stickers. That also means it is right the moment a mission ends, offline,
/// and can never disagree with the map.
///
/// The empty spaces matter as much as the filled ones. A child can see what is
/// still to come, which is the reason a sticker book works at all.
class StickerBookScreen extends ConsumerWidget {
  const StickerBookScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final child = ref.watch(sessionProvider).activeChild;
    final formFactor = context.formFactor;

    if (child == null) {
      return KidScaffold(
        child: LeoMessage(
          title: 'Who is playing?',
          body: 'Pick an explorer to see their sticker book.',
          action: KidButton(label: 'Choose an explorer', onPressed: () => context.go('/profiles')),
        ),
      );
    }

    final book = ref.watch(stickerBookProvider(child.id));

    return KidScaffold(
      onBack: () => context.go('/map'),
      appBar: AppBar(
        title: const Text('My stickers'),
        leading: BackButton(onPressed: () => context.go('/map')),
      ),
      child: book.when(
        loading: () => const LeoLoading(message: 'Opening your sticker book…'),
        error: (error, _) => LeoMessage(
          title: 'The book will not open',
          body: '$error',
          action: KidButton(label: 'Back to the map', onPressed: () => context.go('/map')),
        ),
        data: (pages) {
          if (pages.isEmpty) {
            return LeoMessage(
              title: 'No stickers yet',
              body: 'Download a world and finish a mission — your first sticker '
                  'goes right here.',
              action: KidButton(label: 'Back to the map', onPressed: () => context.go('/map')),
            );
          }

          final earned = pages.fold<int>(0, (sum, page) => sum + page.earnedCount);
          final total = pages.fold<int>(0, (sum, page) => sum + page.slots.length);

          return ListView(
            children: [
              SizedBox(height: KidSpacing.sm * formFactor.density),
              Text(
                '$earned of $total stickers',
                style: Theme.of(context).textTheme.titleLarge,
              ),
              SizedBox(height: KidSpacing.md * formFactor.density),
              for (final page in pages) ...[
                _Page(page: page),
                SizedBox(height: KidSpacing.lg * formFactor.density),
              ],
            ],
          );
        },
      ),
    );
  }
}

class _Page extends StatelessWidget {
  const _Page({required this.page});

  final StickerPage page;

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);
    final palette = WorldPalette.resolve(themeColor: page.themeColor, slug: page.worldSlug);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Text(page.worldIcon ?? '🗺️', style: TextStyle(fontSize: 24 * formFactor.density)),
            SizedBox(width: KidSpacing.xs * formFactor.density),
            Expanded(child: Text(page.worldName, style: theme.textTheme.titleLarge)),
            Text(
              '${page.earnedCount}/${page.slots.length}',
              style: theme.textTheme.labelLarge?.copyWith(color: theme.colorScheme.onSurfaceVariant),
            ),
          ],
        ),
        SizedBox(height: KidSpacing.sm * formFactor.density),
        Wrap(
          spacing: KidSpacing.sm * formFactor.density,
          runSpacing: KidSpacing.sm * formFactor.density,
          children: [
            for (final slot in page.slots)
              _Sticker(
                slot: slot,
                earned: page.earned.contains(slot.missionId),
                palette: palette,
              ),
          ],
        ),
      ],
    );
  }
}

class _Sticker extends StatelessWidget {
  const _Sticker({required this.slot, required this.earned, required this.palette});

  final StickerSlot slot;
  final bool earned;
  final WorldPalette palette;

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final size = (formFactor.isTv ? 140.0 : 96.0);

    return Tooltip(
      message: earned ? slot.title : 'Not yet',
      child: Container(
        width: size,
        height: size,
        alignment: Alignment.center,
        decoration: BoxDecoration(
          color: earned ? null : KidColors.border.withValues(alpha: 0.35),
          gradient: earned
              ? LinearGradient(
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                  colors: [palette.base, palette.accent],
                )
              : null,
          borderRadius: KidRadius.card,
          border: Border.all(
            color: earned ? palette.accent : KidColors.border,
            width: 3,
            // A space waiting for a sticker reads as a space, not as a failure.
            style: earned ? BorderStyle.solid : BorderStyle.none,
          ),
        ),
        child: Text(
          earned ? (slot.worldIcon ?? '⭐') : '',
          style: TextStyle(fontSize: size * 0.42),
        ),
      ),
    );
  }
}

/// One world's page of the book.
class StickerPage {
  const StickerPage({
    required this.worldName,
    required this.slots,
    required this.earned,
    this.worldIcon,
    this.worldSlug,
    this.themeColor,
  });

  final String worldName;
  final List<StickerSlot> slots;
  final Set<int> earned;
  final String? worldIcon;
  final String? worldSlug;
  final String? themeColor;

  int get earnedCount => slots.where((slot) => earned.contains(slot.missionId)).length;
}

/// The book, assembled from what is downloaded and what has been finished.
final stickerBookProvider = FutureProvider.family<List<StickerPage>, int>((ref, childId) async {
  final slots = await ref.watch(contentDaoProvider).stickerSlots();
  final progress = await ref.watch(progressDaoProvider).progressFor(childId);

  final completed = progress.values
      .where((entry) => entry.isCompleted)
      .map((entry) => entry.missionId)
      .toSet();

  final pages = <String, List<StickerSlot>>{};

  for (final slot in slots) {
    pages.putIfAbsent(slot.worldName, () => []).add(slot);
  }

  return pages.entries.map((entry) {
    final first = entry.value.first;

    return StickerPage(
      worldName: entry.key,
      worldIcon: first.worldIcon,
      worldSlug: first.worldSlug,
      themeColor: first.themeColor,
      slots: entry.value,
      earned: completed,
    );
  }).toList();
});
