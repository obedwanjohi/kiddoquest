import 'package:flutter/widgets.dart';

/// The visual constitution, ported from `public/css/kid/tokens.css`.
///
/// Every colour, size and duration in the app comes from here. The website and
/// the app therefore look like the same product, and a brand change is a change
/// in one file rather than a search across a hundred widgets.
class KidColors {
  const KidColors._();

  // Brand
  static const Color primary = Color(0xFF7C3AED);
  static const Color primaryLight = Color(0xFFA78BFA);
  static const Color primaryDark = Color(0xFF6D28D9);
  static const Color primarySoft = Color(0xFFEFE7FD);

  static const Color success = Color(0xFF22C55E);
  static const Color successSoft = Color(0xFFDCFCE7);

  /// Amber is encouragement: streaks, stars, "your turn".
  static const Color amber = Color(0xFFF59E0B);
  static const Color amberSoft = Color(0xFFFEF3C7);

  /// Red is for leaving and deleting. It is never used for a wrong answer.
  static const Color danger = Color(0xFFEF4444);

  /// A wrong answer goes grey, everywhere, with a shake and a soft sound.
  static const Color wrong = Color(0xFF9CA3AF);

  // Surfaces
  static const Color background = Color(0xFFFFF9F0);
  static const Color surface = Color(0xFFFFFFFF);
  static const Color ink = Color(0xFF1F2937);
  static const Color muted = Color(0xFF6B7280);
  static const Color border = Color(0xFFE5E7EB);

  // The mission player keeps its own warmer stage, as on the web.
  static const Color stageCream = Color(0xFFFFF9E6);
  static const Color stageInk = Color(0xFF5A3E36);

  // Dark theme, for a television in a dim living room.
  static const Color darkBackground = Color(0xFF17131F);
  static const Color darkSurface = Color(0xFF241D31);
  static const Color darkInk = Color(0xFFF5F0FA);
  static const Color darkMuted = Color(0xFFA79FB5);
  static const Color darkBorder = Color(0xFF3A3049);
}

/// Each adventure world has a biome. The colour drives the map card, the
/// mission buttons inside it and the ambient particles.
class WorldPalette {
  const WorldPalette(this.name, this.base, this.light, this.accent);

  final String name;
  final Color base;
  final Color light;
  final Color accent;

  static const WorldPalette forest = WorldPalette('forest', Color(0xFF16A34A), Color(0xFF86EFAC), Color(0xFF15803D));
  static const WorldPalette safari = WorldPalette('safari', Color(0xFFD97706), Color(0xFFFCD34D), Color(0xFFB45309));
  static const WorldPalette ocean = WorldPalette('ocean', Color(0xFF0284C7), Color(0xFF7DD3FC), Color(0xFF0369A1));
  static const WorldPalette castle = WorldPalette('castle', Color(0xFF7C3AED), Color(0xFFC4B5FD), Color(0xFF5B21B6));
  static const WorldPalette space = WorldPalette('space', Color(0xFF4338CA), Color(0xFFA5B4FC), Color(0xFF3730A3));
  static const WorldPalette candy = WorldPalette('candy', Color(0xFFEC4899), Color(0xFFF9A8D4), Color(0xFFBE185D));
  static const WorldPalette arctic = WorldPalette('arctic', Color(0xFF0EA5E9), Color(0xFFBAE6FD), Color(0xFF0284C7));

  static const List<WorldPalette> all = [forest, safari, ocean, castle, space, candy, arctic];

  /// Pick a palette for a world. A world that carries its own theme colour wins;
  /// otherwise its name decides, and failing that its position on the map, so
  /// two neighbouring worlds never look identical.
  static WorldPalette resolve({String? themeColor, String? slug, int index = 0}) {
    if (themeColor != null && themeColor.trim().isNotEmpty) {
      final parsed = parseHex(themeColor);
      if (parsed != null) {
        for (final palette in all) {
          if (palette.base.toARGB32() == parsed.toARGB32()) return palette;
        }
        return WorldPalette('custom', parsed, _lighten(parsed, 0.35), _darken(parsed, 0.18));
      }
    }

    final haystack = (slug ?? '').toLowerCase();
    for (final palette in all) {
      if (haystack.contains(palette.name)) return palette;
    }
    if (haystack.contains('forest') || haystack.contains('meadow') || haystack.contains('garden')) return forest;
    if (haystack.contains('safari') || haystack.contains('plains') || haystack.contains('cookie')) return safari;
    if (haystack.contains('sea') || haystack.contains('cove') || haystack.contains('water')) return ocean;
    if (haystack.contains('mountain') || haystack.contains('rainbow')) return arctic;
    if (haystack.contains('village') || haystack.contains('kindness')) return candy;

    return all[index % all.length];
  }

  static Color? parseHex(String value) {
    final cleaned = value.replaceAll('#', '').trim();
    if (cleaned.length != 6 && cleaned.length != 8) return null;
    final parsed = int.tryParse(cleaned.length == 6 ? 'FF$cleaned' : cleaned, radix: 16);
    return parsed == null ? null : Color(parsed);
  }

  static Color _lighten(Color color, double amount) =>
      Color.lerp(color, const Color(0xFFFFFFFF), amount) ?? color;

  static Color _darken(Color color, double amount) =>
      Color.lerp(color, const Color(0xFF000000), amount) ?? color;
}

/// Font families. The TTFs are not in the repository yet, so these names fall
/// back to the platform font until `assets/fonts/` is populated and declared in
/// pubspec.yaml. Nothing breaks in the meantime; the type scale below is what
/// carries the hierarchy.
class KidFonts {
  const KidFonts._();

  static const String display = 'Baloo2';
  static const String body = 'Nunito';
}

/// The type scale from tokens.css. Sizes are multiplied by the form factor's
/// density, so one definition covers a phone, a tablet and a ten-foot screen.
class KidTypeScale {
  const KidTypeScale._();

  static const double hero = 40;
  static const double title = 28;
  static const double mission = 20;
  static const double question = 24;
  static const double answer = 18;
  static const double body = 16;
  static const double caption = 14;
}

class KidRadius {
  const KidRadius._();

  static const double sm = 8;
  static const double md = 12;
  static const double lg = 16;
  static const double xl = 24;
  static const double xxl = 32;

  static const BorderRadius card = BorderRadius.all(Radius.circular(xl));
  static const BorderRadius button = BorderRadius.all(Radius.circular(lg));
  static const BorderRadius pill = BorderRadius.all(Radius.circular(999));
}

class KidSpacing {
  const KidSpacing._();

  static const double xs = 4;
  static const double sm = 8;
  static const double md = 16;
  static const double lg = 24;
  static const double xl = 32;
  static const double xxl = 48;
}

/// Touch and focus targets. A three-year-old's aim is not precise and a remote
/// control is worse, so nothing interactive is ever smaller than this.
class KidTouch {
  const KidTouch._();

  static const double min = 48;
  static const double recommended = 64;
  static const double large = 72;
}

class KidMotion {
  const KidMotion._();

  static const Duration fast = Duration(milliseconds: 100);
  static const Duration normal = Duration(milliseconds: 300);
  static const Duration slow = Duration(milliseconds: 500);
  static const Duration celebrate = Duration(milliseconds: 1000);
  static const Duration confetti = Duration(milliseconds: 2000);

  /// A wrong answer shakes for exactly this long and never moves more than 8px.
  static const Duration shake = Duration(milliseconds: 400);

  /// cubic-bezier(.34, 1.56, .64, 1) — the overshoot that makes buttons feel soft.
  static const Curve spring = Cubic(0.34, 1.56, 0.64, 1);
  static const Curve ease = Curves.easeOutCubic;
}

/// The 3-D bottom edge that makes every button look pressable, and its pressed
/// state. From `--kid-shadow-3d` in tokens.css.
class KidShadows {
  const KidShadows._();

  static List<BoxShadow> edge(Color color, {bool pressed = false}) => [
        BoxShadow(
          color: color,
          offset: Offset(0, pressed ? 1 : 4),
          blurRadius: 0,
        ),
      ];

  static const List<BoxShadow> soft = [
    BoxShadow(color: Color(0x14000000), offset: Offset(0, 2), blurRadius: 8),
  ];

  static const List<BoxShadow> popup = [
    BoxShadow(color: Color(0x26000000), offset: Offset(0, 8), blurRadius: 24),
  ];
}
