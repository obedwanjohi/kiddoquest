import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../features/auth/sign_in_screen.dart';
import '../features/downloads/downloads_screen.dart';
import '../features/map/adventure_map_screen.dart';
import '../features/mission/mission_briefing_screen.dart';
import '../features/mission/mission_player_screen.dart';
import '../features/parent/parent_dashboard_screen.dart';
import '../features/parent/parent_gate_screen.dart';
import '../features/parent/parent_settings_screen.dart';
import '../features/profiles/add_child_screen.dart';
import '../features/profiles/whos_playing_screen.dart';
import '../features/rewards/shop_screen.dart';
import '../features/screen_time/time_up_screen.dart';
import 'providers.dart';

/// Where everything lives.
///
/// The redirect enforces two rules and no more: a signed-out family only sees
/// sign-in, and a family with no children goes straight to adding one. Choosing
/// which child is playing is deliberately not gated, because handing a tablet
/// to a sibling should not need a password.
final routerProvider = Provider<GoRouter>((ref) {
  final refresh = ref.watch(routerRefreshProvider);

  return GoRouter(
    initialLocation: '/profiles',
    refreshListenable: refresh,
    redirect: (context, state) {
      final session = ref.read(sessionProvider);
      final location = state.matchedLocation;

      if (session.loading) return null;

      final onAuthScreen = location == '/sign-in' || location == '/register';

      if (!session.isSignedIn) {
        return onAuthScreen ? null : '/sign-in';
      }

      if (onAuthScreen) {
        return session.children.isEmpty ? '/add-child' : '/profiles';
      }

      if (session.children.isEmpty && location != '/add-child' && !location.startsWith('/parent')) {
        return '/add-child';
      }

      // A mission or the map needs a chosen child.
      if ((location == '/map' || location.startsWith('/mission')) && session.activeChild == null) {
        return '/profiles';
      }

      return null;
    },
    routes: [
      GoRoute(path: '/sign-in', builder: (context, state) => const SignInScreen()),
      GoRoute(path: '/register', builder: (context, state) => const SignInScreen(startOnRegister: true)),
      GoRoute(path: '/profiles', builder: (context, state) => const WhosPlayingScreen()),
      GoRoute(path: '/add-child', builder: (context, state) => const AddChildScreen()),
      GoRoute(path: '/map', builder: (context, state) => const AdventureMapScreen()),
      GoRoute(
        path: '/mission/:id',
        builder: (context, state) => MissionBriefingScreen(
          missionId: int.tryParse(state.pathParameters['id'] ?? '') ?? 0,
        ),
      ),
      GoRoute(
        path: '/mission/:id/play',
        builder: (context, state) => MissionPlayerScreen(
          missionId: int.tryParse(state.pathParameters['id'] ?? '') ?? 0,
        ),
      ),
      GoRoute(path: '/shop', builder: (context, state) => const ShopScreen()),
      GoRoute(path: '/time-up', builder: (context, state) => const TimeUpScreen()),
      GoRoute(path: '/downloads', builder: (context, state) => const DownloadsScreen()),
      GoRoute(path: '/parent', builder: (context, state) => const ParentGateScreen()),
      GoRoute(path: '/parent/home', builder: (context, state) => const ParentHomeScreen()),
      GoRoute(
        path: '/parent/report',
        builder: (context, state) => ParentDashboardScreen(
          childId: int.tryParse(state.uri.queryParameters['child'] ?? ''),
        ),
      ),
      GoRoute(path: '/parent/settings', builder: (context, state) => const ParentSettingsScreen()),
    ],
  );
});
