import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:share_plus/share_plus.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../core/config/app_config.dart';
import '../../core/l10n/locale_controller.dart';
import '../../core/utils/share_links.dart';

class MoreScreen extends ConsumerWidget {
  const MoreScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final t = ref.watch(stringsProvider);
    final site = AppConfig.siteBaseUrl;
    final entries = [
      (t.get('aboutUs'), '/about'),
      (t.get('services'), '/services'),
      (t.get('packages'), '/packages'),
      (t.get('gallery'), '/gallery'),
      (t.get('latestUpdates'), '/updates'),
      (t.get('reviews'), '/reviews'),
      (t.get('faq'), '/faq'),
      (t.get('locationTitle'), '/location'),
      (t.get('contactUs'), '/contact'),
      (t.get('settings'), '/settings'),
      (t.get('privacy'), '/legal/privacy'),
      (t.get('terms'), '/legal/terms'),
    ];

    return Scaffold(
      appBar: AppBar(title: Text(t.get('more'))),
      body: ListView(
        children: [
          ...entries.map(
            (e) => ListTile(
              title: Text(e.$1),
              trailing: const Icon(Icons.chevron_right),
              onTap: () {
                const shellRoutes = {'/services', '/gallery'};
                if (shellRoutes.contains(e.$2)) {
                  context.go(e.$2);
                } else {
                  context.push(e.$2);
                }
              },
            ),
          ),
          ListTile(
            title: Text(t.get('shareApp')),
            trailing: const Icon(Icons.share_outlined),
            onTap: () {
              Share.share(ShareLinks.app(), subject: t.get('appName'));
            },
          ),
          ListTile(
            title: const Text('Website'),
            subtitle: Text(site),
            onTap: () => launchUrl(Uri.parse(site)),
          ),
        ],
      ),
    );
  }
}

class LegalScreen extends StatelessWidget {
  const LegalScreen({super.key, required this.title, required this.path});
  final String title;
  final String path;

  @override
  Widget build(BuildContext context) {
    final url = '${AppConfig.siteBaseUrl}$path';
    return Scaffold(
      appBar: AppBar(title: Text(title)),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          const Text('This content is published on the Balaji Royal Events website.'),
          const SizedBox(height: 16),
          FilledButton(onPressed: () => launchUrl(Uri.parse(url)), child: const Text('Open on website')),
        ],
      ),
    );
  }
}
