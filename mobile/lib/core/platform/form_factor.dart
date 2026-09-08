import 'package:device_info_plus/device_info_plus.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/widgets.dart';

/// The three shapes this app takes.
///
/// Not a screen-size breakpoint dressed up: each one is a different way of
/// touching the product. `compact` is a thumb, `expanded` is two hands on a
/// tablet, `television` is a remote control from three metres away.
enum FormFactor {
  compact,
  expanded,
  television;

  bool get isTv => this == FormFactor.television;
  bool get isCompact => this == FormFactor.compact;
  bool get isExpanded => this == FormFactor.expanded;

  /// Everything visual is multiplied by this: type, targets, radii, padding.
  double get density => switch (this) {
        FormFactor.compact => 1.0,
        FormFactor.expanded => 1.15,
        FormFactor.television => 1.4,
      };

  /// A television overscans; keep content off the edges of the panel.
  EdgeInsets get safeInsets => switch (this) {
        FormFactor.television => const EdgeInsets.symmetric(horizontal: 48, vertical: 27),
        _ => EdgeInsets.zero,
      };

  /// How many answer cards fit across.
  int get answerColumns => switch (this) {
        FormFactor.compact => 2,
        FormFactor.expanded => 2,
        FormFactor.television => 4,
      };

  int get worldColumns => switch (this) {
        FormFactor.compact => 1,
        FormFactor.expanded => 2,
        FormFactor.television => 3,
      };
}

/// Detects whether we are running on a television. Checked once at startup
/// because it cannot change while the app is open.
class TelevisionDetector {
  static bool? _cached;

  static bool get isTelevision => _cached ?? false;

  static Future<bool> detect() async {
    if (_cached != null) return _cached!;

    // defaultTargetPlatform rather than dart:io, so this file also compiles
    // for the web build used in testing.
    if (kIsWeb || defaultTargetPlatform != TargetPlatform.android) {
      return _cached = false;
    }

    try {
      final info = await DeviceInfoPlugin().androidInfo;
      final features = info.systemFeatures;
      _cached = features.contains('android.software.leanback') ||
          features.contains('android.software.leanback_only') ||
          !features.contains('android.hardware.touchscreen');
    } catch (_) {
      _cached = false;
    }

    return _cached!;
  }
}

/// Resolves the form factor for the current frame.
///
/// A television always wins, whatever its reported size; otherwise the shortest
/// side decides, so a phone in landscape is still a phone.
FormFactor resolveFormFactor(BuildContext context, {bool? isTelevision}) {
  if (isTelevision ?? TelevisionDetector.isTelevision) {
    return FormFactor.television;
  }

  final shortestSide = MediaQuery.sizeOf(context).shortestSide;

  return shortestSide >= 600 ? FormFactor.expanded : FormFactor.compact;
}

/// Makes the form factor available to the whole tree without every widget
/// having to measure the window itself.
class FormFactorScope extends InheritedWidget {
  const FormFactorScope({super.key, required this.formFactor, required super.child});

  final FormFactor formFactor;

  static FormFactor of(BuildContext context) {
    final scope = context.dependOnInheritedWidgetOfExactType<FormFactorScope>();
    return scope?.formFactor ?? resolveFormFactor(context);
  }

  @override
  bool updateShouldNotify(FormFactorScope oldWidget) => oldWidget.formFactor != formFactor;
}

/// Wraps a screen so it can be laid out for whatever it is running on.
class FormFactorBuilder extends StatelessWidget {
  const FormFactorBuilder({super.key, required this.builder});

  final Widget Function(BuildContext context, FormFactor formFactor) builder;

  @override
  Widget build(BuildContext context) => builder(context, FormFactorScope.of(context));
}

extension FormFactorContext on BuildContext {
  FormFactor get formFactor => FormFactorScope.of(this);

  /// Scale a token by the current density.
  double scaled(double value) => value * formFactor.density;
}
