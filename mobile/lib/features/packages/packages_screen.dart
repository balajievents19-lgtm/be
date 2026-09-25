import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:share_plus/share_plus.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../core/l10n/locale_controller.dart';
import '../../core/utils/html_plain_text.dart';
import '../../core/utils/share_links.dart';
import '../../data/providers.dart';
import '../../widgets/cards.dart';
import '../../widgets/common.dart';

class PackagesScreen extends ConsumerWidget {
  const PackagesScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final t = ref.watch(stringsProvider);
    final async = ref.watch(packagesProvider);
    return Scaffold(
      appBar: AppBar(title: Text(t.get('packages'))),
      body: async.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (_, __) => ErrorState(message: t.get('error'), onRetry: () => ref.refresh(packagesProvider)),
        data: (items) {
          if (items.isEmpty) return EmptyState(message: t.get('empty'));
          return ListView.separated(
            padding: const EdgeInsets.all(16),
            itemCount: items.length,
            separatorBuilder: (_, __) => const SizedBox(height: 12),
            itemBuilder: (context, i) {
              final p = items[i];
              return PackageCard(package: p, onTap: () => context.push('/packages/${p.slug}'));
            },
          );
        },
      ),
    );
  }
}

class PackageDetailScreen extends ConsumerWidget {
  const PackageDetailScreen({super.key, required this.slug});
  final String slug;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final t = ref.watch(stringsProvider);
    final async = ref.watch(packagesProvider);
    final contact = ref.watch(homeProvider).valueOrNull?.settings.contact;

    return async.when(
      loading: () => const Scaffold(body: Center(child: CircularProgressIndicator())),
      error: (_, __) => Scaffold(body: ErrorState(message: t.get('error'))),
      data: (items) {
        final package = items.where((p) => p.slug == slug).firstOrNull;
        if (package == null) {
          return Scaffold(appBar: AppBar(), body: EmptyState(message: t.get('empty')));
        }
        return Scaffold(
          appBar: AppBar(
            title: Text(package.name),
            actions: [
              IconButton(
                icon: const Icon(Icons.share_outlined),
                onPressed: () {
                  Share.share(ShareLinks.packages(), subject: package.name);
                },
              ),
            ],
          ),
          body: ListView(
            padding: const EdgeInsets.only(bottom: 32),
            children: [
              AspectRatio(aspectRatio: 16 / 9, child: AppNetworkImage(url: package.image)),
              Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(package.name, style: Theme.of(context).textTheme.headlineSmall),
                    if (package.displayPrice != null) ...[
                      const SizedBox(height: 8),
                      Text(package.displayPrice!, style: Theme.of(context).textTheme.titleMedium),
                    ],
                    const SizedBox(height: 12),
                    Text(htmlToPlainText(package.description ?? package.summary)),
                    if (package.features.isNotEmpty) ...[
                      const SizedBox(height: 16),
                      ...package.features.map(
                        (f) => ListTile(
                          dense: true,
                          leading: const Icon(Icons.check_circle_outline),
                          title: Text(f),
                        ),
                      ),
                    ],
                    const SizedBox(height: 16),
                    PrimaryCta(label: t.get('planYourEvent'), onPressed: () => context.go('/enquiry')),
                    if (contact?.whatsapp != null) ...[
                      const SizedBox(height: 10),
                      OutlinedButton.icon(
                        onPressed: () => launchUrl(Uri.parse('https://wa.me/${contact!.whatsapp!.replaceAll(RegExp(r'\D'), '')}')),
                        icon: const Icon(Icons.chat),
                        label: Text(t.get('whatsapp')),
                      ),
                    ],
                  ],
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}
