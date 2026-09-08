import 'package:flutter/material.dart';

import '../core/platform/form_factor.dart';
import 'tokens.dart';

/// Builds the Material theme out of the KiddoQuest tokens.
///
/// The density argument is what makes one theme serve a phone and a television:
/// every size in here is multiplied by it, so a ten-foot layout is the same
/// design at the right scale rather than a separate set of widgets.
class KidTheme {
  const KidTheme._();

  static ThemeData light(FormFactor formFactor) => _build(formFactor, Brightness.light);

  static ThemeData dark(FormFactor formFactor) => _build(formFactor, Brightness.dark);

  static ThemeData _build(FormFactor formFactor, Brightness brightness) {
    final isDark = brightness == Brightness.dark;
    final density = formFactor.density;

    final background = isDark ? KidColors.darkBackground : KidColors.background;
    final surface = isDark ? KidColors.darkSurface : KidColors.surface;
    final ink = isDark ? KidColors.darkInk : KidColors.ink;
    final muted = isDark ? KidColors.darkMuted : KidColors.muted;
    final outline = isDark ? KidColors.darkBorder : KidColors.border;

    final scheme = ColorScheme(
      brightness: brightness,
      primary: isDark ? KidColors.primaryLight : KidColors.primary,
      onPrimary: isDark ? KidColors.darkBackground : Colors.white,
      primaryContainer: isDark ? KidColors.primaryDark : KidColors.primarySoft,
      onPrimaryContainer: isDark ? KidColors.darkInk : KidColors.primaryDark,
      secondary: KidColors.amber,
      onSecondary: KidColors.ink,
      secondaryContainer: isDark ? const Color(0xFF3A2E12) : KidColors.amberSoft,
      onSecondaryContainer: isDark ? KidColors.amberSoft : const Color(0xFF92400E),
      tertiary: KidColors.success,
      onTertiary: Colors.white,
      error: KidColors.danger,
      onError: Colors.white,
      surface: surface,
      onSurface: ink,
      onSurfaceVariant: muted,
      outline: outline,
    );

    TextStyle display(double size, FontWeight weight) => TextStyle(
          fontFamily: KidFonts.display,
          fontSize: size * density,
          fontWeight: weight,
          height: 1.15,
          color: ink,
        );

    TextStyle body(double size, FontWeight weight) => TextStyle(
          fontFamily: KidFonts.body,
          fontSize: size * density,
          fontWeight: weight,
          height: 1.4,
          color: ink,
        );

    return ThemeData(
      useMaterial3: true,
      colorScheme: scheme,
      scaffoldBackgroundColor: background,
      canvasColor: background,
      splashFactory: InkRipple.splashFactory,
      visualDensity: VisualDensity.standard,
      textTheme: TextTheme(
        displayLarge: display(KidTypeScale.hero, FontWeight.w800),
        displayMedium: display(KidTypeScale.title, FontWeight.w800),
        headlineMedium: display(KidTypeScale.question, FontWeight.w700),
        titleLarge: display(KidTypeScale.mission, FontWeight.w700),
        titleMedium: body(KidTypeScale.answer, FontWeight.w800),
        bodyLarge: body(KidTypeScale.body, FontWeight.w600),
        bodyMedium: body(KidTypeScale.body, FontWeight.w400),
        labelLarge: body(KidTypeScale.caption, FontWeight.w800),
        labelSmall: body(KidTypeScale.caption - 2, FontWeight.w700),
      ),
      cardTheme: CardThemeData(
        color: surface,
        elevation: 0,
        margin: EdgeInsets.zero,
        shape: const RoundedRectangleBorder(borderRadius: KidRadius.card),
      ),
      dividerTheme: DividerThemeData(color: outline, thickness: 1, space: 1),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: surface,
        contentPadding: EdgeInsets.symmetric(
          horizontal: KidSpacing.md * density,
          vertical: KidSpacing.md * density,
        ),
        border: OutlineInputBorder(
          borderRadius: KidRadius.button,
          borderSide: BorderSide(color: outline, width: 2),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: KidRadius.button,
          borderSide: BorderSide(color: outline, width: 2),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: KidRadius.button,
          borderSide: BorderSide(color: scheme.primary, width: 3),
        ),
        errorBorder: const OutlineInputBorder(
          borderRadius: KidRadius.button,
          borderSide: BorderSide(color: KidColors.danger, width: 2),
        ),
        labelStyle: body(KidTypeScale.caption, FontWeight.w700).copyWith(color: muted),
      ),
      snackBarTheme: SnackBarThemeData(
        backgroundColor: isDark ? KidColors.darkSurface : KidColors.ink,
        contentTextStyle: body(KidTypeScale.body, FontWeight.w700).copyWith(color: Colors.white),
        behavior: SnackBarBehavior.floating,
        shape: const RoundedRectangleBorder(borderRadius: KidRadius.button),
      ),
      pageTransitionsTheme: const PageTransitionsTheme(
        builders: {
          TargetPlatform.android: FadeUpwardsPageTransitionsBuilder(),
        },
      ),
    );
  }
}
