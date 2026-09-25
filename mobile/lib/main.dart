import 'package:app_links/app_links.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'app_router.dart';
import 'core/notifications/push_notifications.dart';
import 'core/theme/app_theme.dart';
import 'core/utils/deep_link_mapper.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await PushNotifications.bootstrap();
  runApp(const ProviderScope(child: BalajiRoyalEventsApp()));
}

class BalajiRoyalEventsApp extends StatefulWidget {
  const BalajiRoyalEventsApp({super.key});

  @override
  State<BalajiRoyalEventsApp> createState() => _BalajiRoyalEventsAppState();
}

class _BalajiRoyalEventsAppState extends State<BalajiRoyalEventsApp> {
  @override
  void initState() {
    super.initState();
    _listenDeepLinks();
  }

  Future<void> _listenDeepLinks() async {
    try {
      final links = AppLinks();
      final initial = await links.getInitialLink();
      if (initial != null) {
        final route = deepLinkRoute(initial);
        if (route != null) appRouter.go(route);
      }
      links.uriLinkStream.listen((uri) {
        final route = deepLinkRoute(uri);
        if (route != null) appRouter.go(route);
      });
    } catch (_) {
      // Plugin is unavailable in unit tests.
    }
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp.router(
      title: 'Balaji Royal Events',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.light(),
      routerConfig: appRouter,
    );
  }
}
