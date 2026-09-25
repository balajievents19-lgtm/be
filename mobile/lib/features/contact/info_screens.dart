import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:share_plus/share_plus.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:webview_flutter/webview_flutter.dart';

import '../../core/config/app_config.dart';
import '../../core/l10n/locale_controller.dart';
import '../../core/utils/share_links.dart';
import '../../data/models/models.dart';
import '../../data/providers.dart';
import '../../widgets/common.dart';

class ContactScreen extends ConsumerWidget {
  const ContactScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final t = ref.watch(stringsProvider);
    final home = ref.watch(homeProvider);
    return Scaffold(
      appBar: AppBar(title: Text(t.get('contactUs'))),
      body: home.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (_, __) => ErrorState(message: t.get('error'), onRetry: () => ref.refresh(homeProvider)),
        data: (payload) {
          final c = payload.settings.contact;
          final s = payload.settings.social;
          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              if (c.address != null) Text(c.address!, style: Theme.of(context).textTheme.titleMedium),
              const SizedBox(height: 16),
              if (c.phone != null)
                ListTile(leading: const Icon(Icons.call), title: Text(t.get('call')), subtitle: Text(c.phone!), onTap: () => launchUrl(Uri.parse('tel:${c.phone}'))),
              if (c.whatsapp != null)
                ListTile(
                  leading: const Icon(Icons.chat),
                  title: Text(t.get('whatsapp')),
                  subtitle: Text(c.whatsapp!),
                  onTap: () => launchUrl(Uri.parse('https://wa.me/${c.whatsapp!.replaceAll(RegExp(r'\D'), '')}')),
                ),
              if (c.email != null)
                ListTile(leading: const Icon(Icons.email_outlined), title: Text(t.get('emailUs')), subtitle: Text(c.email!), onTap: () => launchUrl(Uri.parse('mailto:${c.email}'))),
              if (payload.settings.workingHours != null)
                ListTile(leading: const Icon(Icons.schedule), title: Text(payload.settings.workingHours!)),
              const SizedBox(height: 12),
              ...s.entries.map((e) => ListTile(title: Text(e.$1), subtitle: Text(e.$2), onTap: () => launchUrl(Uri.parse(e.$2)))),
            ],
          );
        },
      ),
    );
  }
}

class LocationScreen extends ConsumerWidget {
  const LocationScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final t = ref.watch(stringsProvider);
    final home = ref.watch(homeProvider);
    return Scaffold(
      appBar: AppBar(title: Text(t.get('locationTitle'))),
      body: home.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (_, __) => ErrorState(message: t.get('error')),
        data: (payload) {
          final loc = payload.locations.where((l) => l.isPrimary).firstOrNull ?? payload.locations.firstOrNull;
          if (loc == null) return EmptyState(message: t.get('empty'));
          return ListView(
            children: [
              if (loc.latitude != null && loc.longitude != null)
                SizedBox(
                  height: 240,
                  child: _MapPreview(lat: loc.latitude!, lng: loc.longitude!, title: loc.name ?? AppConfig.brandName),
                ),
              ListTile(title: Text(loc.name ?? ''), subtitle: Text(loc.fullAddress)),
              if (loc.phone != null) ListTile(leading: const Icon(Icons.call), title: Text(loc.phone!), onTap: () => launchUrl(Uri.parse('tel:${loc.phone}'))),
              Padding(
                padding: const EdgeInsets.all(16),
                child: FilledButton.icon(
                  onPressed: () {
                    if (loc.latitude == null || loc.longitude == null) return;
                    launchUrl(Uri.parse('https://www.google.com/maps/dir/?api=1&destination=${loc.latitude},${loc.longitude}'));
                  },
                  icon: const Icon(Icons.directions),
                  label: Text(t.get('directions')),
                ),
              ),
            ],
          );
        },
      ),
    );
  }
}

class _MapPreview extends StatefulWidget {
  const _MapPreview({required this.lat, required this.lng, required this.title});
  final String lat;
  final String lng;
  final String title;

  @override
  State<_MapPreview> createState() => _MapPreviewState();
}

class _MapPreviewState extends State<_MapPreview> {
  late final WebViewController _controller;

  @override
  void initState() {
    super.initState();
    final q = Uri.encodeComponent('${widget.title} ${widget.lat},${widget.lng}');
    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setNavigationDelegate(
        NavigationDelegate(
          onNavigationRequest: (request) {
            final host = Uri.parse(request.url).host.toLowerCase();
            final allowed = host == 'maps.google.com' ||
                host == 'www.google.com' ||
                host.endsWith('.google.com') ||
                host.endsWith('.gstatic.com') ||
                host.endsWith('.googleapis.com');
            return allowed ? NavigationDecision.navigate : NavigationDecision.prevent;
          },
        ),
      )
      ..loadRequest(Uri.parse('https://maps.google.com/maps?q=$q&z=15&output=embed'));
  }

  @override
  Widget build(BuildContext context) => WebViewWidget(controller: _controller);
}

class AboutScreen extends ConsumerWidget {
  const AboutScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final t = ref.watch(stringsProvider);
    final settings = ref.watch(homeProvider).valueOrNull?.settings;
    return Scaffold(
      appBar: AppBar(title: Text(t.get('aboutUs'))),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Text(settings?.companyName ?? t.get('appName'), style: Theme.of(context).textTheme.headlineSmall),
          const SizedBox(height: 8),
          if (settings?.tagline != null) Text(settings!.tagline!),
          const SizedBox(height: 12),
          Text(settings?.description ?? ''),
          if (settings?.aboutVision != null) ...[const SizedBox(height: 16), Text(settings!.aboutVision!)],
          if (settings?.aboutMission != null) ...[const SizedBox(height: 16), Text(settings!.aboutMission!)],
        ],
      ),
    );
  }
}

class UpdatesScreen extends ConsumerWidget {
  const UpdatesScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final t = ref.watch(stringsProvider);
    final async = ref.watch(blogProvider);
    return Scaffold(
      appBar: AppBar(title: Text(t.get('latestUpdates'))),
      body: async.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (_, __) => ErrorState(message: t.get('error')),
        data: (items) => items.isEmpty
            ? EmptyState(message: t.get('empty'))
            : ListView.builder(
                itemCount: items.length,
                itemBuilder: (_, i) => ListTile(
                  title: Text(items[i].title ?? ''),
                  subtitle: Text(items[i].excerpt ?? ''),
                  onTap: () => context.push('/updates/${items[i].slug}'),
                ),
              ),
      ),
    );
  }
}

class ReviewsScreen extends ConsumerWidget {
  const ReviewsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final t = ref.watch(stringsProvider);
    final async = ref.watch(testimonialsProvider);
    return Scaffold(
      appBar: AppBar(title: Text(t.get('reviews'))),
      body: async.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (_, __) => ErrorState(message: t.get('error')),
        data: (items) => items.isEmpty
            ? EmptyState(message: t.get('empty'))
            : ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: items.length,
                itemBuilder: (_, i) => Padding(
                  padding: const EdgeInsets.only(bottom: 12),
                  child: ReviewCard(
                    name: items[i].name ?? '',
                    quote: items[i].quote ?? '',
                    rating: items[i].rating,
                    avatar: items[i].avatar,
                  ),
                ),
              ),
      ),
    );
  }
}

class FaqScreen extends ConsumerWidget {
  const FaqScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final t = ref.watch(stringsProvider);
    final async = ref.watch(faqsProvider);
    return Scaffold(
      appBar: AppBar(title: Text(t.get('faq'))),
      body: async.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (_, __) => ErrorState(message: t.get('error')),
        data: (items) {
          if (items.isEmpty) return EmptyState(message: t.get('empty'));
          final groups = <String, List<FaqItem>>{};
          for (final f in items) {
            groups.putIfAbsent(f.categoryName ?? 'FAQ', () => []).add(f);
          }
          return ListView(
            children: groups.entries.expand((entry) {
              return [
                Padding(
                  padding: const EdgeInsets.fromLTRB(16, 16, 16, 4),
                  child: Text(entry.key, style: Theme.of(context).textTheme.titleMedium),
                ),
                ...entry.value.map(
                  (f) => ExpansionTile(
                    title: Text(f.question ?? ''),
                    children: [Padding(padding: const EdgeInsets.all(16), child: Text(f.answer ?? ''))],
                  ),
                ),
              ];
            }).toList(),
          );
        },
      ),
    );
  }
}

class BlogDetailScreen extends ConsumerWidget {
  const BlogDetailScreen({super.key, required this.slug});
  final String slug;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final t = ref.watch(stringsProvider);
    final async = ref.watch(blogDetailProvider(slug));
    return async.when(
      loading: () => const Scaffold(body: Center(child: CircularProgressIndicator())),
      error: (_, __) => Scaffold(appBar: AppBar(), body: ErrorState(message: t.get('error'))),
      data: (post) => Scaffold(
        appBar: AppBar(
          title: Text(post.title ?? t.get('latestUpdates')),
          actions: [
            IconButton(
              icon: const Icon(Icons.share_outlined),
              onPressed: () => Share.share(ShareLinks.blog(post.slug), subject: post.title),
            ),
          ],
        ),
        body: ListView(
          padding: const EdgeInsets.only(bottom: 32),
          children: [
            if (post.image != null) AspectRatio(aspectRatio: 16 / 9, child: AppNetworkImage(url: post.image)),
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(post.title ?? '', style: Theme.of(context).textTheme.headlineSmall),
                  if (post.publishedAt != null) ...[
                    const SizedBox(height: 8),
                    Text(post.publishedAt!, style: Theme.of(context).textTheme.bodySmall),
                  ],
                  const SizedBox(height: 12),
                  Text(post.content ?? post.excerpt ?? ''),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
