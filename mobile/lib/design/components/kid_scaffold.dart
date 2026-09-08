import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../core/platform/form_factor.dart';
import '../tokens.dart';
import 'focus_ring.dart';
import 'mascot_stage.dart';

/// The page frame every screen sits in.
///
/// On a television it adds the overscan margin and traps focus inside the page
/// so a stray remote press cannot send a child somewhere they cannot get back
/// from. Everywhere else it is a plain scaffold with a warm background.
class KidScaffold extends StatelessWidget {
  const KidScaffold({
    super.key,
    required this.child,
    this.appBar,
    this.background,
    this.onBack,
    this.maxContentWidth = 1100,
    this.padContent = true,
  });

  final Widget child;
  final PreferredSizeWidget? appBar;
  final Color? background;
  final VoidCallback? onBack;
  final double maxContentWidth;
  final bool padContent;

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;

    Widget body = child;

    if (padContent) {
      body = Padding(
        padding: EdgeInsets.symmetric(horizontal: KidSpacing.md * formFactor.density),
        child: body,
      );
    }

    body = Center(
      child: ConstrainedBox(
        constraints: BoxConstraints(maxWidth: maxContentWidth * (formFactor.isTv ? 1.6 : 1.0)),
        child: body,
      ),
    );

    if (formFactor.isTv) {
      body = Padding(padding: formFactor.safeInsets, child: body);
    }

    return Shortcuts(
      shortcuts: <ShortcutActivator, Intent>{
        // A remote's Back button should step back, never drop the child out of
        // the app from the middle of a mission.
        const SingleActivator(LogicalKeyboardKey.goBack): const _BackIntent(),
        const SingleActivator(LogicalKeyboardKey.escape): const _BackIntent(),
      },
      child: Actions(
        actions: <Type, Action<Intent>>{
          _BackIntent: CallbackAction<_BackIntent>(onInvoke: (_) {
            onBack?.call();
            return null;
          }),
        },
        child: NearestFocusTraversal(
          child: Scaffold(
            backgroundColor: background,
            appBar: appBar,
            body: SafeArea(child: body),
          ),
        ),
      ),
    );
  }
}

class _BackIntent extends Intent {
  const _BackIntent();
}

/// What the child looks at while a pack downloads or the app talks to the
/// server. Leo is doing the waiting, not a spinner.
class LeoLoading extends StatelessWidget {
  const LeoLoading({super.key, this.message = 'Getting your adventure ready…', this.progress});

  final String message;
  final double? progress;

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);

    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const MascotStage(mood: MascotMood.talking),
          SizedBox(height: KidSpacing.md * formFactor.density),
          Text(message, textAlign: TextAlign.center, style: theme.textTheme.titleLarge),
          SizedBox(height: KidSpacing.md * formFactor.density),
          SizedBox(
            width: 240 * formFactor.density,
            child: ClipRRect(
              borderRadius: KidRadius.pill,
              child: LinearProgressIndicator(
                value: progress,
                minHeight: 10,
                backgroundColor: KidColors.primarySoft,
                valueColor: const AlwaysStoppedAnimation(KidColors.primary),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

/// A friendly dead end: something went wrong, here is what to do about it.
class LeoMessage extends StatelessWidget {
  const LeoMessage({
    super.key,
    required this.title,
    this.body,
    this.action,
    this.mood = MascotMood.thinking,
  });

  final String title;
  final String? body;
  final Widget? action;
  final MascotMood mood;

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;
    final theme = Theme.of(context);

    return Center(
      child: Padding(
        padding: EdgeInsets.all(KidSpacing.lg * formFactor.density),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            MascotStage(mood: mood),
            SizedBox(height: KidSpacing.md * formFactor.density),
            Text(title, textAlign: TextAlign.center, style: theme.textTheme.displayMedium),
            if (body != null) ...[
              SizedBox(height: KidSpacing.sm * formFactor.density),
              Text(
                body!,
                textAlign: TextAlign.center,
                style: theme.textTheme.bodyLarge?.copyWith(color: theme.colorScheme.onSurfaceVariant),
              ),
            ],
            if (action != null) ...[
              SizedBox(height: KidSpacing.lg * formFactor.density),
              action!,
            ],
          ],
        ),
      ),
    );
  }
}
