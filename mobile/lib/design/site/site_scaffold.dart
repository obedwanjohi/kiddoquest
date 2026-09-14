import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../core/platform/form_factor.dart';

/// The frame for the screens that mirror the website.
///
/// Those pages paint their own full-bleed backgrounds — the lilac-to-cream
/// profile picker, the sky map, the navy parent zone — so this draws the
/// decoration edge to edge, behind the status bar, and keeps content inside the
/// safe area. It keeps the two things the kid scaffold guarantees on a
/// television: the overscan margin, and a remote's Back button going somewhere.
class SiteScaffold extends StatelessWidget {
  const SiteScaffold({
    super.key,
    required this.child,
    required this.decoration,
    this.onBack,
    this.safeTop = true,
  });

  final Widget child;
  final Decoration decoration;
  final VoidCallback? onBack;

  /// Off for screens whose own fixed top bar handles the status bar.
  final bool safeTop;

  @override
  Widget build(BuildContext context) {
    final formFactor = context.formFactor;

    Widget body = SafeArea(top: safeTop, child: child);

    if (formFactor.isTv) {
      body = Padding(padding: formFactor.safeInsets, child: body);
    }

    return Shortcuts(
      shortcuts: const <ShortcutActivator, Intent>{
        SingleActivator(LogicalKeyboardKey.goBack): _BackIntent(),
        SingleActivator(LogicalKeyboardKey.escape): _BackIntent(),
      },
      child: Actions(
        actions: <Type, Action<Intent>>{
          _BackIntent: CallbackAction<_BackIntent>(onInvoke: (_) {
            onBack?.call();
            return null;
          }),
        },
        child: FocusTraversalGroup(
          policy: ReadingOrderTraversalPolicy(),
          child: Scaffold(
            backgroundColor: Colors.transparent,
            body: DecoratedBox(
              decoration: decoration,
              child: SizedBox.expand(child: body),
            ),
          ),
        ),
      ),
    );
  }
}

class _BackIntent extends Intent {
  const _BackIntent();
}
