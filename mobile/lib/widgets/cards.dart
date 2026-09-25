import 'package:flutter/material.dart';

import '../core/theme/app_colors.dart';
import '../core/utils/media_url.dart';
import '../data/models/models.dart';
import 'common.dart';

class ServiceCard extends StatelessWidget {
  const ServiceCard({super.key, required this.service, required this.onTap, this.ctaLabel = 'Explore'});

  final ServiceItem service;
  final VoidCallback onTap;
  final String ctaLabel;

  @override
  Widget build(BuildContext context) {
    return Card(
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: onTap,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: AppNetworkImage(url: service.featuredImage),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(14, 10, 14, 10),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(service.name, maxLines: 2, overflow: TextOverflow.ellipsis, style: Theme.of(context).textTheme.titleMedium),
                  if (service.shortDescription != null) ...[
                    const SizedBox(height: 4),
                    Text(service.shortDescription!, maxLines: 2, overflow: TextOverflow.ellipsis, style: Theme.of(context).textTheme.bodySmall),
                  ],
                  const SizedBox(height: 8),
                  Text(ctaLabel, style: const TextStyle(color: AppColors.brand, fontWeight: FontWeight.w600)),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class PackageCard extends StatelessWidget {
  const PackageCard({super.key, required this.package, required this.onTap, this.priceFallback});

  final PackageItem package;
  final VoidCallback onTap;
  final String? priceFallback;

  @override
  Widget build(BuildContext context) {
    final price = package.displayPrice ?? priceFallback;
    final summary = package.summary?.trim();
    final theme = Theme.of(context);

    return Card(
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: onTap,
        child: LayoutBuilder(
          builder: (context, constraints) {
            final bounded = constraints.hasBoundedHeight;
            return Column(
              mainAxisSize: bounded ? MainAxisSize.max : MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                AspectRatio(aspectRatio: 16 / 9, child: AppNetworkImage(url: package.image)),
                if (bounded)
                  Expanded(
                    child: Padding(
                      padding: const EdgeInsets.fromLTRB(14, 12, 14, 14),
                      child: _PackageCardCopy(
                        name: package.name,
                        summary: summary,
                        price: price,
                        theme: theme,
                        fill: true,
                      ),
                    ),
                  )
                else
                  Padding(
                    padding: const EdgeInsets.fromLTRB(14, 12, 14, 14),
                    child: _PackageCardCopy(
                      name: package.name,
                      summary: summary,
                      price: price,
                      theme: theme,
                      fill: false,
                    ),
                  ),
              ],
            );
          },
        ),
      ),
    );
  }
}

class _PackageCardCopy extends StatelessWidget {
  const _PackageCardCopy({
    required this.name,
    required this.summary,
    required this.price,
    required this.theme,
    required this.fill,
  });

  final String name;
  final String? summary;
  final String? price;
  final ThemeData theme;
  final bool fill;

  @override
  Widget build(BuildContext context) {
    return Column(
      mainAxisSize: fill ? MainAxisSize.max : MainAxisSize.min,
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          name,
          style: theme.textTheme.titleMedium,
          maxLines: fill ? 3 : null,
        ),
        if (summary != null && summary!.isNotEmpty) ...[
          const SizedBox(height: 6),
          fill
              ? Expanded(
                  child: Text(
                    summary!,
                    style: theme.textTheme.bodyMedium,
                    maxLines: 4,
                  ),
                )
              : Text(summary!, style: theme.textTheme.bodyMedium),
        ],
        if (price != null) ...[
          const SizedBox(height: 8),
          Text(price!, style: const TextStyle(color: AppColors.maroon, fontWeight: FontWeight.w600)),
        ],
      ],
    );
  }
}

double packageStripHeight(BuildContext context, {required double cardWidth, required List<PackageItem> packages}) {
  final theme = Theme.of(context);
  final scaler = MediaQuery.textScalerOf(context);
  final copyWidth = (cardWidth - 32).clamp(1.0, cardWidth);
  var maxCopy = 0.0;

  for (final package in packages) {
    final title = TextPainter(
      text: TextSpan(text: package.name, style: theme.textTheme.titleMedium),
      textDirection: TextDirection.ltr,
      textScaler: scaler,
      maxLines: 3,
    )..layout(maxWidth: copyWidth);
    var copy = title.height;
    final summary = package.summary?.trim();
    if (summary != null && summary.isNotEmpty) {
      final body = TextPainter(
        text: TextSpan(text: summary, style: theme.textTheme.bodyMedium),
        textDirection: TextDirection.ltr,
        textScaler: scaler,
        maxLines: 4,
      )..layout(maxWidth: copyWidth);
      copy += 6 + body.height;
    }
    if ((package.displayPrice ?? '').isNotEmpty) {
      copy += 8 + scaler.scale(20);
    }
    if (copy > maxCopy) {
      maxCopy = copy;
    }
  }

  final measured = cardWidth * 9 / 16 + 12 + 14 + maxCopy + 12;
  final maxVisible = (MediaQuery.sizeOf(context).shortestSide * 0.38).clamp(320.0, 430.0);
  return measured < maxVisible ? measured : maxVisible;
}

class GalleryCard extends StatelessWidget {
  const GalleryCard({super.key, required this.item, required this.onTap});

  final GalleryItem item;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Card(
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: onTap,
        child: AspectRatio(
          aspectRatio: 4 / 5,
          child: Stack(
            fit: StackFit.expand,
            children: [
              AppNetworkImage(url: item.preview),
              if (item.isVideo)
                const Center(
                  child: CircleAvatar(
                    backgroundColor: Color(0xEBF15B22),
                    radius: 26,
                    child: Icon(Icons.play_arrow, color: Colors.white, size: 28),
                  ),
                ),
              Positioned(
                left: 0,
                right: 0,
                bottom: 0,
                child: Container(
                  padding: const EdgeInsets.all(10),
                  decoration: const BoxDecoration(
                    gradient: LinearGradient(
                      begin: Alignment.bottomCenter,
                      end: Alignment.topCenter,
                      colors: [Color(0xAA0E1123), Colors.transparent],
                    ),
                  ),
                  child: Text(
                    item.title ?? '',
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

String? youtubeEmbedFor(GalleryItem item) {
  if (!item.isYoutube && item.embedUrl == null) return null;
  final candidate = item.embedUrl ?? MediaUrl.youtubeNocookieEmbed(item.videoId);
  if (candidate == null) return null;
  final uri = Uri.tryParse(candidate);
  if (uri == null) return MediaUrl.youtubeNocookieEmbed(item.videoId);
  final host = uri.host.toLowerCase();
  if (host.endsWith('youtube-nocookie.com') && uri.path.contains('/embed/')) {
    return candidate;
  }
  return MediaUrl.youtubeNocookieEmbed(item.videoId);
}
