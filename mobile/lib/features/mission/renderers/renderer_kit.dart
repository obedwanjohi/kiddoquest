import 'package:flutter/material.dart';

import '../../../core/db/local_database.dart';
import '../../../core/models/pack.dart';
import '../../../core/platform/form_factor.dart';
import '../../../design/components/focus_ring.dart';
import '../../../design/components/pack_image.dart';
import '../../../design/tokens.dart';
import '../mission_session.dart';

/// What every question renderer is handed.
///
/// A renderer's only job is to let a child express an answer and then call
/// [submit] with it. It never decides whether the answer is right: the session
/// asks the shared scorer, so the app and the server always agree.
class RendererContext {
  const RendererContext({
    required this.question,
    required this.media,
    required this.onSubmit,
    this.isAnswering = true,
  });

  /// Build one from a live mission.
  factory RendererContext.of(MissionSessionController session, PackQuestion question) {
    return RendererContext(
      question: question,
      media: session.media,
      isAnswering: session.phase == QuestionPhase.asking,
      onSubmit: session.submitResponse,
    );
  }

  final PackQuestion question;
  final Map<String, ResolvedMedia> media;

  /// Where a finished answer goes. A renderer never judges it.
  final void Function(Map<String, dynamic> response) onSubmit;

  final bool isAnswering;

  void submit(Map<String, dynamic> response) => onSubmit(response);

  /// Options in the order the author put them.
  List<PackOption> get options {
    final sorted = [...question.options]..sort((a, b) => a.sortOrder.compareTo(b.sortOrder));

    return sorted;
  }
}

/// A tappable tile used by most of the build-an-answer renderers.
class KidTile extends StatelessWidget {
  const KidTile({
    super.key,
    this.label,
    this.mediaKey,
    this.media = const {},
    this.onPressed,
    this.selected = false,
    this.done = false,
    this.dimmed = false,
    this.accent = KidColors.primary,
    this.autofocus = false,
    this.height,
    this.trailing,
  });

  final String? label;
  final String? mediaKey;
  final Map<String, ResolvedMedia> media;
  final VoidCallback? onPressed;
  final bool selected;
  final bool done;
  final bool dimmed;
  final Color accent;
  final bool autofocus;
  final double? height;
  final Widget? trailing;

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);

    final (fill, edge, ink) = switch ((done, selected, dimmed)) {
      (true, _, _) => (KidColors.successSoft, KidColors.success, const Color(0xFF14532D)),
      (_, true, _) => (accent.withValues(alpha: 0.16), accent, theme.colorScheme.onSurface),
      (_, _, true) => (const Color(0xFFF3F4F6), KidColors.border, KidColors.muted),
      _ => (theme.colorScheme.surface, accent, theme.colorScheme.onSurface),
    };

    return KidFocusable(
      onPressed: (dimmed || done) ? null : onPressed,
      enabled: !dimmed && !done && onPressed != null,
      autofocus: autofocus,
      borderRadius: KidRadius.button,
      semanticLabel: label,
      child: AnimatedContainer(
        duration: KidMotion.fast,
        height: height,
        constraints: BoxConstraints(minHeight: KidTouch.min * formFactor.density),
        padding: EdgeInsets.symmetric(
          horizontal: KidSpacing.md * formFactor.density,
          vertical: KidSpacing.sm * formFactor.density,
        ),
        decoration: BoxDecoration(
          color: fill,
          borderRadius: KidRadius.button,
          border: Border.all(color: edge, width: selected || done ? 3 : 2),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            if (mediaKey != null)
              Padding(
                padding: EdgeInsets.only(right: label == null ? 0 : KidSpacing.sm),
                child: PackImage(mediaKey: mediaKey, media: media, height: 44 * formFactor.density),
              ),
            if (label != null && label!.isNotEmpty)
              Flexible(
                child: Text(
                  label!,
                  textAlign: TextAlign.center,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontFamily: KidFonts.display,
                    fontSize: KidTypeScale.answer * formFactor.density,
                    fontWeight: FontWeight.w800,
                    color: ink,
                  ),
                ),
              ),
            if (done) const Padding(padding: EdgeInsets.only(left: 6), child: Icon(Icons.check, size: 18, color: KidColors.success)),
            ?trailing,
          ],
        ),
      ),
    );
  }
}

/// The "I have finished building my answer" button.
class CheckButton extends StatelessWidget {
  const CheckButton({super.key, required this.onPressed, this.label = 'Check!', this.enabled = true});

  final VoidCallback onPressed;
  final String label;
  final bool enabled;

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;

    return KidFocusable(
      onPressed: enabled ? onPressed : null,
      enabled: enabled,
      borderRadius: KidRadius.button,
      semanticLabel: label,
      child: Container(
        height: KidTouch.recommended * formFactor.density,
        padding: EdgeInsets.symmetric(horizontal: KidSpacing.xl * formFactor.density),
        alignment: Alignment.center,
        decoration: BoxDecoration(
          color: enabled ? KidColors.success : KidColors.border,
          borderRadius: KidRadius.button,
          boxShadow: enabled ? KidShadows.edge(const Color(0xFF15803D)) : const [],
        ),
        child: Text(
          label,
          style: TextStyle(
            fontFamily: KidFonts.display,
            fontWeight: FontWeight.w800,
            fontSize: KidTypeScale.answer * formFactor.density,
            color: enabled ? Colors.white : KidColors.muted,
          ),
        ),
      ),
    );
  }
}

/// One short line telling the child what to do. Five words where possible.
class RendererPrompt extends StatelessWidget {
  const RendererPrompt({super.key, required this.text});

  final String text;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Padding(
      padding: const EdgeInsets.only(bottom: KidSpacing.sm),
      child: Text(
        text,
        textAlign: TextAlign.center,
        style: theme.textTheme.labelLarge?.copyWith(color: theme.colorScheme.onSurfaceVariant),
      ),
    );
  }
}

/// Splits a matching question's options into the two columns the child sees.
///
/// Authors mark sides with `content_type` where they can. Where they have not,
/// options that share a `match_key` are a pair, and the first of each pair goes
/// on the left. That is the same convention the website's engine follows.
({List<PackOption> left, List<PackOption> right}) splitMatchingSides(List<PackOption> options) {
  final left = options.where((o) => (o.contentType ?? '').toLowerCase() == 'left').toList();
  final right = options.where((o) => (o.contentType ?? '').toLowerCase() == 'right').toList();

  if (left.isNotEmpty && right.isNotEmpty) {
    return (left: left, right: right);
  }

  final byKey = <String, List<PackOption>>{};
  for (final option in options) {
    byKey.putIfAbsent((option.matchKey ?? '${option.id}').toLowerCase(), () => []).add(option);
  }

  final a = <PackOption>[];
  final b = <PackOption>[];

  for (final pair in byKey.values) {
    if (pair.isNotEmpty) a.add(pair.first);
    if (pair.length > 1) b.add(pair[1]);
  }

  return (left: a, right: b);
}
