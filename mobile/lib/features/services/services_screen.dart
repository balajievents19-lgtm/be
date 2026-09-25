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

class ServicesScreen extends ConsumerWidget {
  const ServicesScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final t = ref.watch(stringsProvider);
    final async = ref.watch(servicesProvider);
    return Scaffold(
      appBar: AppHeader(title: t.get('services'), onMenu: () => context.push('/more')),
      body: async.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (_, __) => ErrorState(message: t.get('error'), onRetry: () => ref.refresh(servicesProvider)),
        data: (items) {
          if (items.isEmpty) return EmptyState(message: t.get('empty'));
          return GridView.builder(
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
            itemCount: items.length,
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              mainAxisSpacing: 12,
              crossAxisSpacing: 12,
              childAspectRatio: 0.62,
            ),
            itemBuilder: (context, i) {
              final s = items[i];
              return ServiceCard(service: s, onTap: () => context.push('/services/${s.slug}'));
            },
          );
        },
      ),
    );
  }
}

class ServiceDetailScreen extends ConsumerWidget {
  const ServiceDetailScreen({super.key, required this.slug});
  final String slug;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final t = ref.watch(stringsProvider);
    final async = ref.watch(serviceDetailProvider(slug));
    final home = ref.watch(homeProvider).valueOrNull;
    final contact = home?.settings.contact;

    return async.when(
      loading: () => const Scaffold(body: Center(child: CircularProgressIndicator())),
      error: (_, __) => Scaffold(appBar: AppBar(), body: ErrorState(message: t.get('error'), onRetry: () => ref.refresh(serviceDetailProvider(slug)))),
      data: (service) => Scaffold(
        appBar: AppBar(
          title: Text(service.name),
          actions: [
            IconButton(
              icon: const Icon(Icons.share_outlined),
              onPressed: () => Share.share(ShareLinks.service(service.slug), subject: service.name),
            ),
          ],
        ),
        body: ListView(
          padding: const EdgeInsets.only(bottom: 32),
          children: [
            AspectRatio(aspectRatio: 16 / 9, child: AppNetworkImage(url: service.bannerImage ?? service.featuredImage)),
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(service.name, style: Theme.of(context).textTheme.headlineSmall),
                  const SizedBox(height: 8),
                  Text(htmlToPlainText(service.fullDescription ?? service.shortDescription)),
                  const SizedBox(height: 16),
                  PrimaryCta(label: t.get('planYourEvent'), onPressed: () => context.go('/enquiry')),
                  const SizedBox(height: 10),
                  if (contact?.whatsapp != null)
                    OutlinedButton.icon(
                      onPressed: () => launchUrl(Uri.parse('https://wa.me/${contact!.whatsapp!.replaceAll(RegExp(r'\D'), '')}')),
                      icon: const Icon(Icons.chat),
                      label: Text(t.get('whatsapp')),
                    ),
                  if (contact?.phone != null) ...[
                    const SizedBox(height: 8),
                    OutlinedButton.icon(
                      onPressed: () => launchUrl(Uri.parse('tel:${contact!.phone}')),
                      icon: const Icon(Icons.call),
                      label: Text(t.get('call')),
                    ),
                  ],
                ],
              ),
            ),
            if (service.galleryImages.isNotEmpty) ...[
              SectionHeader(title: t.get('gallery')),
              SizedBox(
                height: 140,
                child: ListView.separated(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  scrollDirection: Axis.horizontal,
                  itemCount: service.galleryImages.length,
                  separatorBuilder: (_, __) => const SizedBox(width: 10),
                  itemBuilder: (_, i) => ClipRRect(
                    borderRadius: BorderRadius.circular(12),
                    child: SizedBox(width: 180, child: AppNetworkImage(url: service.galleryImages[i])),
                  ),
                ),
              ),
            ],
            Consumer(
              builder: (context, ref, _) {
                final packages = ref.watch(packagesProvider).valueOrNull ?? [];
                final related = packages.where((p) => p.serviceId == service.id).toList();
                if (related.isEmpty) return const SizedBox.shrink();
                return Column(
                  children: [
                    SectionHeader(title: t.get('packages')),
                    ...related.map((p) => Padding(
                          padding: const EdgeInsets.fromLTRB(16, 0, 16, 12),
                          child: PackageCard(package: p, onTap: () => context.push('/packages/${p.slug}')),
                        )),
                  ],
                );
              },
            ),
          ],
        ),
      ),
    );
  }
}
