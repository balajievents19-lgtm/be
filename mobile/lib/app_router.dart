import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import 'features/account/account_screen.dart';
import 'features/auth/auth_screens.dart';
import 'features/auth/oauth_web_screen.dart';
import 'features/contact/info_screens.dart';
import 'features/enquiry/enquiry_screen.dart';
import 'features/gallery/gallery_screen.dart';
import 'features/home/home_screen.dart';
import 'features/more/more_screen.dart';
import 'features/packages/packages_screen.dart';
import 'features/search/search_screen.dart';
import 'features/services/services_screen.dart';
import 'features/shell/main_shell.dart';
import 'features/shell/splash_screen.dart';

final rootNavigatorKey = GlobalKey<NavigatorState>();

final appRouter = GoRouter(
  navigatorKey: rootNavigatorKey,
  initialLocation: '/splash',
  routes: [
    GoRoute(parentNavigatorKey: rootNavigatorKey, path: '/splash', builder: (_, __) => const SplashScreen()),
    StatefulShellRoute.indexedStack(
      builder: (context, state, navigationShell) => MainShell(navigationShell: navigationShell),
      branches: [
        StatefulShellBranch(routes: [GoRoute(path: '/home', builder: (_, __) => const HomeScreen())]),
        StatefulShellBranch(routes: [GoRoute(path: '/services', builder: (_, __) => const ServicesScreen())]),
        StatefulShellBranch(routes: [GoRoute(path: '/gallery', builder: (_, __) => const GalleryScreen())]),
        StatefulShellBranch(routes: [GoRoute(path: '/enquiry', builder: (_, __) => const EnquiryScreen())]),
        StatefulShellBranch(routes: [GoRoute(path: '/account', builder: (_, __) => const AccountScreen())]),
      ],
    ),
    GoRoute(parentNavigatorKey: rootNavigatorKey, path: '/services/:slug', builder: (_, state) => ServiceDetailScreen(slug: state.pathParameters['slug']!)),
    GoRoute(parentNavigatorKey: rootNavigatorKey, path: '/gallery/:slug', builder: (_, state) => GalleryViewerScreen(slug: state.pathParameters['slug']!)),
    GoRoute(parentNavigatorKey: rootNavigatorKey, path: '/packages', builder: (_, __) => const PackagesScreen()),
    GoRoute(parentNavigatorKey: rootNavigatorKey, path: '/packages/:slug', builder: (_, state) => PackageDetailScreen(slug: state.pathParameters['slug']!)),
    GoRoute(parentNavigatorKey: rootNavigatorKey, path: '/login', builder: (_, __) => const LoginScreen()),
    GoRoute(parentNavigatorKey: rootNavigatorKey, path: '/register', builder: (_, __) => const RegisterScreen()),
    GoRoute(parentNavigatorKey: rootNavigatorKey, path: '/forgot-password', builder: (_, __) => const ForgotPasswordScreen()),
    GoRoute(parentNavigatorKey: rootNavigatorKey, path: '/verify-email', builder: (_, state) => VerifyEmailScreen(email: state.extra as String?)),
    GoRoute(parentNavigatorKey: rootNavigatorKey, path: '/oauth/:provider', builder: (_, state) => OAuthWebScreen(provider: state.pathParameters['provider'] ?? 'google')),
    GoRoute(parentNavigatorKey: rootNavigatorKey, path: '/location', builder: (_, __) => const LocationScreen()),
    GoRoute(parentNavigatorKey: rootNavigatorKey, path: '/contact', builder: (_, __) => const ContactScreen()),
    GoRoute(parentNavigatorKey: rootNavigatorKey, path: '/more', builder: (_, __) => const MoreScreen()),
    GoRoute(parentNavigatorKey: rootNavigatorKey, path: '/about', builder: (_, __) => const AboutScreen()),
    GoRoute(parentNavigatorKey: rootNavigatorKey, path: '/faq', builder: (_, __) => const FaqScreen()),
    GoRoute(parentNavigatorKey: rootNavigatorKey, path: '/reviews', builder: (_, __) => const ReviewsScreen()),
    GoRoute(parentNavigatorKey: rootNavigatorKey, path: '/updates', builder: (_, __) => const UpdatesScreen()),
    GoRoute(parentNavigatorKey: rootNavigatorKey, path: '/updates/:slug', builder: (_, state) => BlogDetailScreen(slug: state.pathParameters['slug']!)),
    GoRoute(parentNavigatorKey: rootNavigatorKey, path: '/search', builder: (_, __) => const SearchScreen()),
    GoRoute(parentNavigatorKey: rootNavigatorKey, path: '/profile', builder: (_, __) => const ProfileScreen()),
    GoRoute(parentNavigatorKey: rootNavigatorKey, path: '/my-enquiries', builder: (_, __) => const MyEnquiriesScreen()),
    GoRoute(parentNavigatorKey: rootNavigatorKey, path: '/my-bookings', builder: (_, __) => const MyBookingsScreen()),
    GoRoute(parentNavigatorKey: rootNavigatorKey, path: '/wishlist', builder: (_, __) => const WishlistScreen()),
    GoRoute(parentNavigatorKey: rootNavigatorKey, path: '/notifications', builder: (_, __) => const NotificationsScreen()),
    GoRoute(parentNavigatorKey: rootNavigatorKey, path: '/settings', builder: (_, __) => const SettingsScreen()),
    GoRoute(parentNavigatorKey: rootNavigatorKey, path: '/legal/privacy', builder: (_, __) => const LegalScreen(title: 'Privacy Policy', path: '/privacy-policy')),
    GoRoute(parentNavigatorKey: rootNavigatorKey, path: '/legal/terms', builder: (_, __) => const LegalScreen(title: 'Terms & Conditions', path: '/terms')),
  ],
);
