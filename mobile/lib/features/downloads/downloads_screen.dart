import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../app/providers.dart';
import '../../core/models/pack.dart';
import '../../core/platform/form_factor.dart';
import '../../design/components/kid_button.dart';
import '../../design/components/kid_scaffold.dart';
import '../../design/tokens.dart';
import '../map/map_state.dart';

/// What is on this device, what is available, and how much room it all takes.
///
/// A parent on a metered Kenyan data bundle needs to see the megabytes before
/// they spend them, so every row states its size.
class DownloadsScreen extends ConsumerStatefulWidget {
  const DownloadsScreen({super.key});

  @override
  ConsumerState<DownloadsScreen> createState() => _DownloadsScreenState();
}

class _DownloadsScreenState extends ConsumerState<DownloadsScreen> {
  String? _busyPack;

  Future<void> _download(PackSummary pack) async {
    setState(() => _busyPack = pack.packId);
    final messenger = ScaffoldMessenger.of(context);

    try {
      await ref.read(contentRepositoryProvider).download(pack);
      ref.invalidate(installedPacksProvider);
      ref.invalidate(mapDataProvider);
    } catch (error) {
      messenger.showSnackBar(SnackBar(content: Text('Download failed. $error')));
    } finally {
      if (mounted) setState(() => _busyPack = null);
    }
  }

  Future<void> _remove(String packId) async {
    setState(() => _busyPack = packId);

    await ref.read(contentRepositoryProvider).remove(packId);
    ref.invalidate(installedPacksProvider);
    ref.invalidate(mapDataProvider);

    if (mounted) setState(() => _busyPack = null);
  }

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);
    final child = ref.watch(sessionProvider).activeChild;
    final level = child?.levelCode;
    final catalogAsync = ref.watch(catalogProvider(level));
    final installedAsync = ref.watch(installedPacksProvider);

    return KidScaffold(
      onBack: () => context.pop(),
      appBar: AppBar(
        title: const Text('Downloads'),
        leading: BackButton(onPressed: () => context.go(child == null ? '/profiles' : '/map')),
      ),
      child: installedAsync.when(
        loading: () => const LeoLoading(message: 'Checking what is on this device…'),
        error: (error, _) => LeoMessage(title: 'Could not read downloads', body: '$error'),
        data: (installed) {
          final installedIds = {
            for (final row in installed) row['pack_id'] as String: row,
          };

          final totalBytes = installed.fold<int>(0, (sum, row) => sum + ((row['bytes'] as int?) ?? 0));

          return ListView(
            padding: EdgeInsets.only(bottom: KidSpacing.xl * formFactor.density),
            children: [
              Container(
                padding: EdgeInsets.all(KidSpacing.md * formFactor.density),
                decoration: const BoxDecoration(
                  color: KidColors.primarySoft,
                  borderRadius: KidRadius.card,
                ),
                child: Row(
                  children: [
                    const Icon(Icons.sd_storage_rounded, color: KidColors.primaryDark),
                    SizedBox(width: KidSpacing.sm * formFactor.density),
                    Expanded(
                      child: Text(
                        '${installed.length} world${installed.length == 1 ? '' : 's'} on this device, '
                        '${(totalBytes / 1048576).toStringAsFixed(1)} MB',
                        style: theme.textTheme.titleMedium?.copyWith(color: KidColors.primaryDark),
                      ),
                    ),
                  ],
                ),
              ),
              SizedBox(height: KidSpacing.lg * formFactor.density),
              Text('Available worlds', style: theme.textTheme.titleLarge),
              SizedBox(height: KidSpacing.sm * formFactor.density),
              catalogAsync.when(
                loading: () => const Padding(
                  padding: EdgeInsets.all(KidSpacing.lg),
                  child: Center(child: CircularProgressIndicator()),
                ),
                error: (error, _) => Padding(
                  padding: const EdgeInsets.all(KidSpacing.md),
                  child: Text(
                    'Cannot reach the internet. Downloaded worlds still work.',
                    style: theme.textTheme.bodyLarge?.copyWith(color: theme.colorScheme.onSurfaceVariant),
                  ),
                ),
                data: (packs) => Column(
                  children: [
                    for (final pack in packs)
                      _PackRow(
                        pack: pack,
                        installedVersion: installedIds[pack.packId]?['version'] as int?,
                        busy: _busyPack == pack.packId,
                        onDownload: () => _download(pack),
                        onRemove: () => _remove(pack.packId),
                      ),
                  ],
                ),
              ),
            ],
          );
        },
      ),
    );
  }
}

class _PackRow extends StatelessWidget {
  const _PackRow({
    required this.pack,
    required this.busy,
    required this.onDownload,
    required this.onRemove,
    this.installedVersion,
  });

  final PackSummary pack;
  final int? installedVersion;
  final bool busy;
  final VoidCallback onDownload;
  final VoidCallback onRemove;

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);
    final installed = installedVersion != null;
    final needsUpdate = installed && installedVersion! < pack.version;

    return Container(
      margin: EdgeInsets.only(bottom: KidSpacing.sm * formFactor.density),
      padding: EdgeInsets.all(KidSpacing.md * formFactor.density),
      decoration: BoxDecoration(
        color: theme.colorScheme.surface,
        borderRadius: KidRadius.card,
        border: Border.all(color: theme.colorScheme.outline),
      ),
      child: Row(
        children: [
          Text(pack.icon ?? '🌍', style: TextStyle(fontSize: 28 * formFactor.density)),
          SizedBox(width: KidSpacing.md * formFactor.density),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(pack.name, style: theme.textTheme.titleMedium),
                Text(
                  [
                    '${pack.missions} missions',
                    '${pack.megabytesCore.toStringAsFixed(1)} MB',
                    if (pack.bytesVideo > 0) 'videos ${(pack.bytesVideo / 1048576).round()} MB',
                    if (!pack.isFree) 'subscription',
                  ].join(' · '),
                  style: theme.textTheme.labelSmall?.copyWith(color: theme.colorScheme.onSurfaceVariant),
                ),
              ],
            ),
          ),
          SizedBox(width: KidSpacing.sm * formFactor.density),
          if (busy)
            const SizedBox(width: 28, height: 28, child: CircularProgressIndicator(strokeWidth: 3))
          else if (needsUpdate)
            KidButton(label: 'Update', size: KidButtonSize.small, tone: KidButtonTone.amber, onPressed: onDownload)
          else if (installed)
            KidButton(label: 'Remove', size: KidButtonSize.small, tone: KidButtonTone.neutral, onPressed: onRemove)
          else
            KidButton(label: 'Get', size: KidButtonSize.small, onPressed: onDownload),
        ],
      ),
    );
  }
}
