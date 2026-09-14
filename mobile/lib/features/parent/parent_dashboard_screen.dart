import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../app/providers.dart';
import '../../core/models/child.dart';
import '../../core/models/parent_dashboard.dart';
import '../../core/network/api_exception.dart';
import '../../core/network/api_client.dart';
import '../../design/components/focus_ring.dart';
import '../../design/site/site_scaffold.dart';
import '../../design/site/tw.dart';
import '../../design/site/tw_motion.dart';

part 'dashboard_overview_tab.dart';
part 'dashboard_progress_tab.dart';
part 'dashboard_support_tab.dart';
part 'dashboard_controls_tab.dart';

/// The Parent Companion Zone, drawn and behaving the way the website's does.
///
/// Mirrors `resources/views/parent/dashboard.blade.php`: the navy page, the
/// sticky header with Lock Zone, the student selector, and four tabs — Daily
/// Overview, Learning Progress, Learning Support and Controls — with every
/// action the website offers: WhatsApp share, the drilldown, assigning
/// tomorrow's focus mission, the AI coach, the M-Pesa link, the devotional and
/// songs toggles, the screen-time limit and the PIN.
///
/// The report comes from the same service the website renders from, so the
/// two never disagree about a child.
class ParentDashboardScreen extends ConsumerStatefulWidget {
  const ParentDashboardScreen({super.key, this.childId, this.initialTab});

  final int? childId;

  /// `overview`, `progress`, `support` or `controls`; Daily Overview when null.
  final String? initialTab;

  @override
  ConsumerState<ParentDashboardScreen> createState() => _ParentDashboardScreenState();
}

enum _Tab { overview, progress, support, controls }

class _ParentDashboardScreenState extends ConsumerState<ParentDashboardScreen> {
  late _Tab _tab = _Tab.values.firstWhere((tab) => tab.name == widget.initialTab, orElse: () => _Tab.overview);
  late int? _childId = widget.childId;
  String? _flash;
  Timer? _flashTimer;

  @override
  void dispose() {
    _flashTimer?.cancel();
    super.dispose();
  }

  /// The website's `session('success')` banner: shown, then gone after three
  /// seconds.
  void flash(String message) {
    _flashTimer?.cancel();
    setState(() => _flash = message);
    _flashTimer = Timer(const Duration(seconds: 3), () {
      if (mounted) setState(() => _flash = null);
    });
  }

  void error(String message) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message)));
  }

  /// Reload the report after an action, and the session so the map and the
  /// exit bar see a new time limit or focus mission straight away.
  Future<void> refresh() async {
    ref.invalidate(parentDashboardProvider(_childId));
    await ref.read(sessionProvider.notifier).bootstrap();
  }

  void _lock() {
    ref.read(authRepositoryProvider).lockParentZone();
    context.go('/profiles');
    ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Parent Zone locked.')));
  }

  @override
  Widget build(BuildContext context) {
    final dashboard = ref.watch(parentDashboardProvider(_childId));
    final sm = Tw.isSm(context);

    return SiteScaffold(
      safeTop: false,
      onBack: _lock,
      // .parent-bg: linear-gradient(180deg, #0F172A 0%, #1E1B4B 100%)
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topCenter,
          end: Alignment.bottomCenter,
          colors: [Tw.slate900, Tw.indigo950],
        ),
      ),
      child: Column(
        children: [
          _Header(onLock: _lock),
          Expanded(
            child: dashboard.when(
              loading: () => const Center(child: CircularProgressIndicator(color: Tw.indigo400)),
              error: (err, _) => _DarkMessage(
                text: err is ApiException && err.isOffline
                    ? 'The Parent Zone needs the internet the first time on this device.'
                    : 'The dashboard could not load. $err',
                onRetry: () => ref.invalidate(parentDashboardProvider(_childId)),
              ),
              data: (data) {
                final child = data.selectedChild;
                final report = data.report;

                return SingleChildScrollView(
                  padding: EdgeInsets.fromLTRB(sm ? 16 : 12, 16, sm ? 16 : 12, 80),
                  child: Center(
                    child: ConstrainedBox(
                      constraints: const BoxConstraints(maxWidth: 896),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          if (_flash != null) ...[
                            _FlashBanner(message: _flash!),
                            const SizedBox(height: 16),
                          ],
                          if (data.fromCache) ...[
                            const _OfflineBanner(),
                            const SizedBox(height: 16),
                          ],
                          _StudentSelector(
                            children: data.children,
                            selected: child,
                            onSelect: (id) => setState(() => _childId = id),
                          ),
                          const SizedBox(height: 20),
                          _TabBar(selected: _tab, onSelect: (tab) => setState(() => _tab = tab)),
                          const SizedBox(height: 24),
                          if (child == null || report == null)
                            _DarkMessage(
                              text: 'Add a child profile first and their report will appear here.',
                              onRetry: () => context.go('/add-child'),
                              retryLabel: 'Add Explorer',
                            )
                          else
                            switch (_tab) {
                              _Tab.overview => _OverviewTab(
                                  child: child,
                                  report: report,
                                  onViewProgress: () => setState(() => _tab = _Tab.progress),
                                ),
                              _Tab.progress => _ProgressTab(child: child, report: report),
                              _Tab.support => _SupportTab(
                                  child: child,
                                  report: report,
                                  missions: data.missions,
                                  host: this,
                                ),
                              _Tab.controls => _ControlsTab(
                                  child: child,
                                  guardian: data.guardian,
                                  host: this,
                                ),
                            },
                        ],
                      ),
                    ),
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}

// ── Shell ────────────────────────────────────────────────────────────────────

class _Header extends StatelessWidget {
  const _Header({required this.onLock});

  final VoidCallback onLock;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.fromLTRB(16, 12 + MediaQuery.paddingOf(context).top, 16, 12),
      decoration: BoxDecoration(
        color: Tw.slate900.withValues(alpha: 0.9),
        border: Border(bottom: BorderSide(color: Tw.indigo900.withValues(alpha: 0.5))),
      ),
      child: Center(
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 896),
          child: Row(
            children: [
              const Text('👨‍👩‍👧', style: TextStyle(fontSize: 24)),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Parent Companion Zone',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: Tw.text(Tw.base, color: Tw.white, weight: FontWeight.w900, height: 1.25),
                    ),
                    Text('KiddoQuest CBC Learning Control', style: Tw.text(11, color: Tw.indigo300, weight: FontWeight.w400)),
                  ],
                ),
              ),
              KidFocusable(
                onPressed: onLock,
                semanticLabel: 'Lock Zone',
                borderRadius: BorderRadius.circular(Tw.roundedXl),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: Tw.indigo600.withValues(alpha: 0.3),
                    borderRadius: BorderRadius.circular(Tw.roundedXl),
                    border: Border.all(color: Tw.indigo500.withValues(alpha: 0.4)),
                  ),
                  child: Text('🔒 Lock Zone', style: Tw.text(Tw.xs, color: Tw.indigo200)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _FlashBanner extends StatelessWidget {
  const _FlashBanner({required this.message});

  final String message;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Tw.emerald500.withValues(alpha: 0.2),
        borderRadius: BorderRadius.circular(Tw.rounded2xl),
        border: Border.all(color: Tw.emerald500),
        boxShadow: Tw.shadowMd,
      ),
      child: Text(message, textAlign: TextAlign.center, style: Tw.text(Tw.sm, color: Tw.emerald300)),
    );
  }
}

class _OfflineBanner extends StatelessWidget {
  const _OfflineBanner();

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Tw.amber500.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(Tw.rounded2xl),
        border: Border.all(color: Tw.amber400.withValues(alpha: 0.5)),
      ),
      child: Text(
        '📡 Offline — showing the last report this device saw. Changes need the internet.',
        textAlign: TextAlign.center,
        style: Tw.text(Tw.xs, color: Tw.amber200),
      ),
    );
  }
}

class _StudentSelector extends StatelessWidget {
  const _StudentSelector({required this.children, required this.selected, required this.onSelect});

  final List<Child> children;
  final Child? selected;
  final ValueChanged<int> onSelect;

  @override
  Widget build(BuildContext context) {
    final sm = Tw.isSm(context);

    final label = Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        const Text('👧👦', style: TextStyle(fontSize: 24)),
        const SizedBox(width: 10),
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('SELECTED STUDENT PROFILE:', style: Tw.label(10, color: Tw.indigo300)),
            Text(selected?.name ?? 'Select Student', style: Tw.text(Tw.sm, color: Tw.white, weight: FontWeight.w800)),
          ],
        ),
      ],
    );

    final dropdown = Container(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      decoration: BoxDecoration(
        color: Tw.slate900,
        borderRadius: BorderRadius.circular(Tw.roundedXl),
        border: Border.all(color: Tw.indigo400.withValues(alpha: 0.6)),
      ),
      child: DropdownButtonHideUnderline(
        child: DropdownButton<int>(
          value: selected?.id,
          isExpanded: true,
          dropdownColor: Tw.slate900,
          iconEnabledColor: Tw.white,
          style: Tw.text(Tw.xs, color: Tw.white, weight: FontWeight.w900),
          items: [
            for (final child in children)
              DropdownMenuItem(
                value: child.id,
                child: Text(
                  '${child.avatarEmoji} ${child.name} (${child.totalStars} Stars ⭐)',
                  overflow: TextOverflow.ellipsis,
                ),
              ),
          ],
          onChanged: (id) {
            if (id != null) onSelect(id);
          },
        ),
      ),
    );

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        gradient: const LinearGradient(colors: [Tw.slate900, Tw.indigo950, Tw.slate900]),
        borderRadius: BorderRadius.circular(Tw.rounded2xl),
        border: Border.all(color: Tw.indigo500.withValues(alpha: 0.4)),
        boxShadow: Tw.shadowLg,
      ),
      child: sm
          ? Row(
              children: [
                label,
                const Spacer(),
                SizedBox(width: 280, child: dropdown),
              ],
            )
          : Column(
              children: [
                label,
                const SizedBox(height: 12),
                dropdown,
              ],
            ),
    );
  }
}

class _TabBar extends StatelessWidget {
  const _TabBar({required this.selected, required this.onSelect});

  final _Tab selected;
  final ValueChanged<_Tab> onSelect;

  static const Map<_Tab, ({String emoji, String mobile, String wide})> labels = {
    _Tab.overview: (emoji: '🏠', mobile: 'Overview', wide: 'Daily Overview'),
    _Tab.progress: (emoji: '📚', mobile: 'Learning', wide: 'Learning Progress'),
    _Tab.support: (emoji: '🎯', mobile: 'Learning', wide: 'Learning Support'),
    _Tab.controls: (emoji: '⚙️', mobile: 'Controls', wide: 'Controls'),
  };

  @override
  Widget build(BuildContext context) {
    final sm = Tw.isSm(context);

    return Container(
      padding: const EdgeInsets.all(6),
      decoration: BoxDecoration(
        color: Tw.slate900.withValues(alpha: 0.7),
        borderRadius: BorderRadius.circular(Tw.rounded2xl),
        border: Border.all(color: Tw.slate800),
      ),
      child: Row(
        children: [
          for (final entry in labels.entries) ...[
            if (entry.key != _Tab.overview) const SizedBox(width: 6),
            Expanded(
              child: KidFocusable(
                onPressed: () => onSelect(entry.key),
                semanticLabel: entry.value.wide,
                borderRadius: BorderRadius.circular(Tw.roundedXl),
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 150),
                  padding: const EdgeInsets.symmetric(vertical: 10),
                  decoration: BoxDecoration(
                    color: selected == entry.key ? Tw.indigo500 : Tw.white.withValues(alpha: 0.05),
                    borderRadius: BorderRadius.circular(Tw.roundedXl),
                    boxShadow: selected == entry.key
                        ? [BoxShadow(color: Tw.indigo500.withValues(alpha: 0.4), blurRadius: 14, offset: const Offset(0, 4))]
                        : null,
                  ),
                  child: _tabContent(entry.value, selected == entry.key, sm),
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _tabContent(({String emoji, String mobile, String wide}) label, bool active, bool sm) {
    final style = Tw.text(
      sm ? Tw.sm : Tw.xs,
      color: active ? Tw.white : Tw.slate400,
      weight: active ? FontWeight.w800 : FontWeight.w700,
    );

    if (sm) {
      return Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Text(label.emoji, style: const TextStyle(fontSize: 14)),
          const SizedBox(width: 4),
          Flexible(child: Text(label.wide, overflow: TextOverflow.ellipsis, style: style)),
        ],
      );
    }

    return Column(
      children: [
        Text(label.emoji, style: const TextStyle(fontSize: 13)),
        const SizedBox(height: 4),
        Text(label.mobile, maxLines: 1, overflow: TextOverflow.fade, softWrap: false, style: style),
      ],
    );
  }
}

// ── Shared pieces ────────────────────────────────────────────────────────────

/// `.parent-card`.
class _ParentCard extends StatelessWidget {
  const _ParentCard({required this.child, this.borderColor, this.borderWidth = 1, this.gradient});

  final Widget child;
  final Color? borderColor;
  final double borderWidth;
  final Gradient? gradient;

  static const Color background = Color(0xF21E293B);
  static const Color border = Color(0x40818CF8);

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: gradient == null ? background : null,
        gradient: gradient,
        borderRadius: BorderRadius.circular(Tw.rounded3xl),
        border: Border.all(color: borderColor ?? border, width: borderWidth),
      ),
      child: child,
    );
  }
}

/// The "emoji, title, subtitle" header most cards open with.
class _CardHeading extends StatelessWidget {
  const _CardHeading({
    required this.emoji,
    required this.title,
    required this.subtitle,
    this.subtitleColor = Tw.indigo300,
    this.divider = true,
    this.trailing,
  });

  final String emoji;
  final String title;
  final String subtitle;
  final Color subtitleColor;
  final bool divider;
  final Widget? trailing;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.only(bottom: divider ? 12 : 0),
      decoration: divider
          ? BoxDecoration(border: Border(bottom: BorderSide(color: Tw.slate700.withValues(alpha: 0.5))))
          : null,
      child: Row(
        children: [
          Text(emoji, style: const TextStyle(fontSize: 30)),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: Tw.text(Tw.base, color: Tw.white, weight: FontWeight.w900)),
                Text(subtitle, style: Tw.text(Tw.xs, color: subtitleColor, weight: FontWeight.w400)),
              ],
            ),
          ),
          ?trailing,
        ],
      ),
    );
  }
}

/// A solid dark-zone button: the website's `bg-{colour}-600 … font-black`.
class _SolidButton extends StatelessWidget {
  const _SolidButton({
    required this.label,
    required this.color,
    required this.onPressed,
    this.textColor = Tw.white,
    this.busy = false,
    this.fontSize = Tw.xs,
    this.verticalPadding = 10,
    this.expand = true,
    this.border,
  });

  final String label;
  final Color color;
  final VoidCallback? onPressed;
  final Color textColor;
  final bool busy;
  final double fontSize;
  final double verticalPadding;
  final bool expand;
  final Color? border;

  @override
  Widget build(BuildContext context) {
    return KidFocusable(
      onPressed: busy ? null : onPressed,
      semanticLabel: label,
      borderRadius: BorderRadius.circular(Tw.roundedXl),
      child: Container(
        width: expand ? double.infinity : null,
        padding: EdgeInsets.symmetric(horizontal: 16, vertical: verticalPadding),
        alignment: expand ? Alignment.center : null,
        decoration: BoxDecoration(
          color: color,
          borderRadius: BorderRadius.circular(Tw.roundedXl),
          border: border == null ? null : Border.all(color: border!),
          boxShadow: Tw.shadowMd,
        ),
        child: busy
            ? SizedBox(width: fontSize + 4, height: fontSize + 4, child: CircularProgressIndicator(strokeWidth: 2, color: textColor))
            : Text(label, textAlign: TextAlign.center, style: Tw.text(fontSize, color: textColor, weight: FontWeight.w900)),
      ),
    );
  }
}

class _DarkMessage extends StatelessWidget {
  const _DarkMessage({required this.text, this.onRetry, this.retryLabel = 'Try again'});

  final String text;
  final VoidCallback? onRetry;
  final String retryLabel;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: _ParentCard(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(text, textAlign: TextAlign.center, style: Tw.text(Tw.sm, color: Tw.slate200)),
              if (onRetry != null) ...[
                const SizedBox(height: 16),
                _SolidButton(label: retryLabel, color: Tw.indigo600, onPressed: onRetry, expand: false),
              ],
            ],
          ),
        ),
      ),
    );
  }
}

/// Open a WhatsApp share link, as the website's `target="_blank"` links do.
Future<void> _openShare(BuildContext context, Uri uri) async {
  final messenger = ScaffoldMessenger.of(context);

  try {
    final opened = await launchUrl(uri, mode: LaunchMode.externalApplication);

    if (!opened) {
      messenger.showSnackBar(const SnackBar(content: Text('WhatsApp could not be opened on this device.')));
    }
  } catch (_) {
    messenger.showSnackBar(const SnackBar(content: Text('WhatsApp could not be opened on this device.')));
  }
}

/// The website's own address, for the link inside a shared report.
String _siteOrigin() {
  final uri = Uri.tryParse(kApiBaseUrl);

  if (uri == null || uri.host.isEmpty) return 'https://www.kiddoquest.co.ke';

  return uri.hasPort ? '${uri.scheme}://${uri.host}:${uri.port}' : '${uri.scheme}://${uri.host}';
}

String _thousands(int value) {
  final digits = value.toString();
  final buffer = StringBuffer();

  for (var i = 0; i < digits.length; i++) {
    if (i > 0 && (digits.length - i) % 3 == 0) buffer.write(',');
    buffer.write(digits[i]);
  }

  return buffer.toString();
}
