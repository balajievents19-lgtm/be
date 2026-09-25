import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:open_filex/open_filex.dart';
import 'package:path_provider/path_provider.dart';
import 'package:share_plus/share_plus.dart';

import '../../core/l10n/locale_controller.dart';
import '../../core/network/api_exception.dart';
import '../../core/utils/share_links.dart';
import '../../data/auth_controller.dart';
import '../../data/local_wishlist.dart';
import '../../data/providers.dart';
import '../../widgets/cards.dart';
import '../../widgets/common.dart';
import '../../widgets/youtube_embed.dart';

class GalleryScreen extends ConsumerStatefulWidget {
  const GalleryScreen({super.key});

  @override
  ConsumerState<GalleryScreen> createState() => _GalleryScreenState();
}

class _GalleryScreenState extends ConsumerState<GalleryScreen> {
  String? _category;

  @override
  Widget build(BuildContext context) {
    final t = ref.watch(stringsProvider);
    final categories = ref.watch(galleryCategoriesProvider);
    final items = ref.watch(galleryProvider(_category));

    return Scaffold(
      appBar: AppHeader(title: t.get('gallery'), onMenu: () => context.push('/more')),
      body: Column(
        children: [
          categories.when(
            loading: () => const SizedBox(height: 44),
            error: (_, __) => const SizedBox.shrink(),
            data: (list) => FilterChips(
              labels: list.map((c) => (c.slug, c.name)).toList(),
              selected: _category,
              onSelected: (v) => setState(() => _category = v),
            ),
          ),
          Expanded(
            child: items.when(
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (_, __) => ErrorState(message: t.get('error'), onRetry: () => ref.refresh(galleryProvider(_category))),
              data: (list) {
                if (list.isEmpty) return EmptyState(message: t.get('empty'));
                return GridView.builder(
                  padding: const EdgeInsets.fromLTRB(16, 12, 16, 24),
                  itemCount: list.length,
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 2,
                    mainAxisSpacing: 12,
                    crossAxisSpacing: 12,
                    childAspectRatio: 0.72,
                  ),
                  itemBuilder: (context, i) {
                    final item = list[i];
                    return GalleryCard(item: item, onTap: () => context.push('/gallery/${item.slug}'));
                  },
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}

class GalleryViewerScreen extends ConsumerStatefulWidget {
  const GalleryViewerScreen({super.key, required this.slug});
  final String slug;

  @override
  ConsumerState<GalleryViewerScreen> createState() => _GalleryViewerScreenState();
}

class _GalleryViewerScreenState extends ConsumerState<GalleryViewerScreen> {
  bool _fav = false;
  bool _downloading = false;

  @override
  void initState() {
    super.initState();
    LocalWishlist.load().then((set) {
      if (mounted) setState(() => _fav = set.contains(widget.slug));
    });
  }

  @override
  Widget build(BuildContext context) {
    final t = ref.watch(stringsProvider);
    final async = ref.watch(galleryDetailProvider(widget.slug));
    final auth = ref.watch(authControllerProvider);
    final signedIn = auth.signedIn;

    return async.when(
      loading: () => const Scaffold(body: Center(child: CircularProgressIndicator())),
      error: (_, __) => Scaffold(appBar: AppBar(), body: ErrorState(message: t.get('error'))),
      data: (item) {
        final embed = youtubeEmbedFor(item);
        final canDownload = signedIn && auth.verified && item.downloadAvailable && !item.isVideo;
        return Scaffold(
          appBar: AppBar(
            title: Text(item.title ?? t.get('gallery')),
            actions: [
              IconButton(
                icon: Icon(_fav ? Icons.favorite : Icons.favorite_border),
                onPressed: () async {
                  final next = await LocalWishlist.toggle(item.slug);
                  setState(() => _fav = next.contains(item.slug));
                },
              ),
              IconButton(
                icon: const Icon(Icons.share_outlined),
                onPressed: () => Share.share(ShareLinks.gallery(item.slug), subject: item.title),
              ),
            ],
          ),
          body: ListView(
            children: [
              if (item.isVideo && embed != null)
                AspectRatio(
                  aspectRatio: 16 / 9,
                  child: YoutubeEmbed(url: embed),
                )
              else
                InteractiveViewer(
                  child: AppNetworkImage(url: item.preview ?? item.image),
                ),
              Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    if (item.caption != null) Text(item.caption!),
                    if (item.categoryName != null) ...[
                      const SizedBox(height: 8),
                      Text(item.categoryName!, style: Theme.of(context).textTheme.bodySmall),
                    ],
                    if (canDownload) ...[
                      const SizedBox(height: 16),
                      PrimaryCta(
                        label: t.get('download'),
                        loading: _downloading,
                        icon: Icons.download_outlined,
                        onPressed: () async {
                          if (_downloading) return;
                          setState(() => _downloading = true);
                          try {
                            final bytes = await ref.read(contentRepositoryProvider).downloadGalleryItem(item.id);
                            final dir = await getApplicationDocumentsDirectory();
                            final file = File('${dir.path}/gallery-${item.id}.download');
                            await file.writeAsBytes(bytes, flush: true);
                            await OpenFilex.open(file.path);
                          } on ApiException catch (e) {
                            if (context.mounted) {
                              ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
                            }
                          } finally {
                            if (mounted) setState(() => _downloading = false);
                          }
                        },
                      ),
                    ] else if (item.downloadAvailable && (!signedIn || !auth.verified)) ...[
                      const SizedBox(height: 16),
                      Text(t.get('downloadDenied')),
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
