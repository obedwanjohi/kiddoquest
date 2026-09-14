import 'package:flutter/material.dart';

/// The website's design vocabulary, in Flutter.
///
/// The website is built with Tailwind, and the screens that mirror it — the
/// profile picker, the adventure map, the add-explorer form, the parent zone —
/// are written against these values rather than approximations of them, so a
/// class name in the Blade template can be read straight across to a value
/// here. `Tw.slate900` is Tailwind's `slate-900`, and so on.
class Tw {
  const Tw._();

  // ── Faces ───────────────────────────────────────────────────────────────────

  /// `font-heading`: Baloo 2.
  static const String heading = 'Baloo2';

  /// The body face: Nunito.
  static const String body = 'Nunito';

  /// Text in the body face. [size] is in logical pixels, as Tailwind's are.
  static TextStyle text(
    double size, {
    Color color = slate900,
    FontWeight weight = FontWeight.w700,
    double? height,
    double? letterSpacing,
  }) =>
      TextStyle(
        fontFamily: body,
        fontSize: size,
        color: color,
        fontWeight: weight,
        height: height,
        letterSpacing: letterSpacing,
      );

  /// Text in the heading face. Baloo 2 stops at 800, which is what the
  /// website's `font-black` renders as too.
  static TextStyle display(
    double size, {
    Color color = slate900,
    FontWeight weight = FontWeight.w800,
    double? height,
  }) =>
      TextStyle(
        fontFamily: heading,
        fontSize: size,
        color: color,
        fontWeight: weight,
        height: height,
      );

  /// `uppercase tracking-wider`.
  static TextStyle label(double size, {Color color = slate900, FontWeight weight = FontWeight.w900}) =>
      text(size, color: color, weight: weight, letterSpacing: size * 0.05);

  // ── Sizes (text-xs … text-4xl) ─────────────────────────────────────────────

  static const double xs = 12;
  static const double sm = 14;
  static const double base = 16;
  static const double lg = 18;
  static const double xl = 20;
  static const double x2l = 24;
  static const double x3l = 30;
  static const double x4l = 36;
  static const double x5l = 48;

  // ── Palette ─────────────────────────────────────────────────────────────────

  static const Color white = Color(0xFFFFFFFF);
  static const Color black = Color(0xFF000000);

  static const Color slate50 = Color(0xFFF8FAFC);
  static const Color slate100 = Color(0xFFF1F5F9);
  static const Color slate200 = Color(0xFFE2E8F0);
  static const Color slate300 = Color(0xFFCBD5E1);
  static const Color slate400 = Color(0xFF94A3B8);
  static const Color slate500 = Color(0xFF64748B);
  static const Color slate600 = Color(0xFF475569);
  static const Color slate700 = Color(0xFF334155);
  static const Color slate800 = Color(0xFF1E293B);
  static const Color slate900 = Color(0xFF0F172A);
  static const Color slate950 = Color(0xFF020617);

  static const Color violet50 = Color(0xFFF5F3FF);
  static const Color violet200 = Color(0xFFDDD6FE);

  static const Color purple50 = Color(0xFFFAF5FF);
  static const Color purple100 = Color(0xFFF3E8FF);
  static const Color purple200 = Color(0xFFE9D5FF);
  static const Color purple300 = Color(0xFFD8B4FE);
  static const Color purple400 = Color(0xFFC084FC);
  static const Color purple500 = Color(0xFFA855F7);
  static const Color purple600 = Color(0xFF9333EA);
  static const Color purple700 = Color(0xFF7E22CE);
  static const Color purple800 = Color(0xFF6B21A8);
  static const Color purple900 = Color(0xFF581C87);
  static const Color purple950 = Color(0xFF3B0764);

  static const Color indigo100 = Color(0xFFE0E7FF);
  static const Color indigo200 = Color(0xFFC7D2FE);
  static const Color indigo300 = Color(0xFFA5B4FC);
  static const Color indigo400 = Color(0xFF818CF8);
  static const Color indigo500 = Color(0xFF6366F1);
  static const Color indigo600 = Color(0xFF4F46E5);
  static const Color indigo700 = Color(0xFF4338CA);
  static const Color indigo800 = Color(0xFF3730A3);
  static const Color indigo900 = Color(0xFF312E81);
  static const Color indigo950 = Color(0xFF1E1B4B);

  static const Color amber50 = Color(0xFFFFFBEB);
  static const Color amber100 = Color(0xFFFEF3C7);
  static const Color amber200 = Color(0xFFFDE68A);
  static const Color amber300 = Color(0xFFFCD34D);
  static const Color amber400 = Color(0xFFFBBF24);
  static const Color amber500 = Color(0xFFF59E0B);
  static const Color amber600 = Color(0xFFD97706);
  static const Color amber700 = Color(0xFFB45309);
  static const Color amber900 = Color(0xFF78350F);
  static const Color amber950 = Color(0xFF451A03);

  static const Color yellow300 = Color(0xFFFDE047);

  static const Color emerald50 = Color(0xFFECFDF5);
  static const Color emerald100 = Color(0xFFD1FAE5);
  static const Color emerald200 = Color(0xFFA7F3D0);
  static const Color emerald300 = Color(0xFF6EE7B7);
  static const Color emerald400 = Color(0xFF34D399);
  static const Color emerald500 = Color(0xFF10B981);
  static const Color emerald600 = Color(0xFF059669);
  static const Color emerald700 = Color(0xFF047857);
  static const Color emerald800 = Color(0xFF065F46);
  static const Color emerald900 = Color(0xFF064E3B);
  static const Color emerald950 = Color(0xFF022C22);

  static const Color teal600 = Color(0xFF0D9488);
  static const Color teal700 = Color(0xFF0F766E);

  static const Color green500 = Color(0xFF22C55E);

  static const Color sky100 = Color(0xFFE0F2FE);
  static const Color sky500 = Color(0xFF0EA5E9);
  static const Color sky700 = Color(0xFF0369A1);

  static const Color blue100 = Color(0xFFDBEAFE);
  static const Color blue500 = Color(0xFF3B82F6);
  static const Color blue900 = Color(0xFF1E3A8A);

  static const Color rose50 = Color(0xFFFFF1F2);
  static const Color rose200 = Color(0xFFFECDD3);
  static const Color rose300 = Color(0xFFFDA4AF);
  static const Color rose400 = Color(0xFFFB7185);
  static const Color rose500 = Color(0xFFF43F5E);
  static const Color rose600 = Color(0xFFE11D48);
  static const Color rose700 = Color(0xFFBE123C);

  static const Color red100 = Color(0xFFFEE2E2);

  static const Color pink100 = Color(0xFFFCE7F3);
  static const Color pink500 = Color(0xFFEC4899);
  static const Color pink600 = Color(0xFFDB2777);
  static const Color pink800 = Color(0xFF9D174D);
  static const Color pink900 = Color(0xFF831843);

  static const Color orange500 = Color(0xFFF97316);
  static const Color orange600 = Color(0xFFEA580C);

  // ── Radii (rounded-xl … rounded-3xl) ───────────────────────────────────────

  static const double roundedLg = 8;
  static const double roundedXl = 12;
  static const double rounded2xl = 16;
  static const double rounded3xl = 24;

  // ── Shadows ────────────────────────────────────────────────────────────────

  /// `shadow-xs` / `shadow-sm`.
  static const List<BoxShadow> shadowSm = [
    BoxShadow(color: Color(0x0D000000), blurRadius: 2, offset: Offset(0, 1)),
  ];

  /// `shadow-md`.
  static const List<BoxShadow> shadowMd = [
    BoxShadow(color: Color(0x1A000000), blurRadius: 6, spreadRadius: -1, offset: Offset(0, 4)),
    BoxShadow(color: Color(0x1A000000), blurRadius: 4, spreadRadius: -2, offset: Offset(0, 2)),
  ];

  /// `shadow-lg`.
  static const List<BoxShadow> shadowLg = [
    BoxShadow(color: Color(0x1A000000), blurRadius: 15, spreadRadius: -3, offset: Offset(0, 10)),
    BoxShadow(color: Color(0x1A000000), blurRadius: 6, spreadRadius: -4, offset: Offset(0, 4)),
  ];

  /// `shadow-xl`.
  static const List<BoxShadow> shadowXl = [
    BoxShadow(color: Color(0x1A000000), blurRadius: 25, spreadRadius: -5, offset: Offset(0, 20)),
    BoxShadow(color: Color(0x1A000000), blurRadius: 10, spreadRadius: -6, offset: Offset(0, 8)),
  ];

  /// `shadow-2xl`.
  static const List<BoxShadow> shadow2xl = [
    BoxShadow(color: Color(0x40000000), blurRadius: 50, spreadRadius: -12, offset: Offset(0, 25)),
  ];

  /// A solid "3-D" bottom edge, the website's `shadow-[0_4px_0_#…]`.
  static List<BoxShadow> edge(Color color, {double depth = 4}) => [
        BoxShadow(color: color, offset: Offset(0, depth)),
      ];

  /// Is the window at least Tailwind's `sm` breakpoint (640px)?
  static bool isSm(BuildContext context) => MediaQuery.sizeOf(context).width >= 640;
}
