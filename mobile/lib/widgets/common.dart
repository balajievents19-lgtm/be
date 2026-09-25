import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:shimmer/shimmer.dart';

import '../core/theme/app_colors.dart';
import '../core/utils/media_url.dart';

class AppHeader extends StatelessWidget implements PreferredSizeWidget {
  const AppHeader({
    super.key,
    required this.title,
    this.showLogo = false,
    this.logoUrl,
    this.onMenu,
    this.onSearch,
    this.onNotifications,
  });

  final String title;
  final bool showLogo;
  final String? logoUrl;
  final VoidCallback? onMenu;
  final VoidCallback? onSearch;
  final VoidCallback? onNotifications;

  @override
  Size get preferredSize => const Size.fromHeight(64);

  @override
  Widget build(BuildContext context) {
    return AppBar(
      titleSpacing: 8,
      title: showLogo
          ? Row(
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(8),
                  child: SizedBox(
                    height: 36,
                    width: 36,
                    child: logoUrl != null
                        ? AppNetworkImage(url: logoUrl, fit: BoxFit.contain)
                        : Image.asset('assets/brand/logo.png', fit: BoxFit.contain),
                  ),
                ),
                const SizedBox(width: 10),
                Flexible(
                  child: Text(
                    title,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: Theme.of(context).textTheme.titleLarge,
                  ),
                ),
              ],
            )
          : Text(title),
      actions: [
        if (onSearch != null)
          IconButton(onPressed: onSearch, icon: const Icon(Icons.search)),
        if (onNotifications != null)
          IconButton(onPressed: onNotifications, icon: const Icon(Icons.notifications_none_outlined)),
        if (onMenu != null) IconButton(onPressed: onMenu, icon: const Icon(Icons.menu)),
      ],
    );
  }
}

class SectionHeader extends StatelessWidget {
  const SectionHeader({super.key, required this.title, this.actionLabel, this.onAction});

  final String title;
  final String? actionLabel;
  final VoidCallback? onAction;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 24, 8, 12),
      child: Row(
        children: [
          Expanded(
            child: Text(title, style: Theme.of(context).textTheme.headlineSmall),
          ),
          if (actionLabel != null && onAction != null)
            TextButton(onPressed: onAction, child: Text(actionLabel!)),
        ],
      ),
    );
  }
}

class AppNetworkImage extends StatelessWidget {
  const AppNetworkImage({
    super.key,
    required this.url,
    this.fit = BoxFit.cover,
    this.borderRadius,
  });

  final String? url;
  final BoxFit fit;
  final BorderRadius? borderRadius;

  @override
  Widget build(BuildContext context) {
    final resolved = MediaUrl.resolve(url);
    final child = resolved == null
        ? const _ImageFallback()
        : CachedNetworkImage(
            imageUrl: resolved,
            fit: fit,
            placeholder: (_, __) => const ShimmerBox(),
            errorWidget: (_, __, ___) => const _ImageFallback(),
          );
    if (borderRadius == null) return child;
    return ClipRRect(borderRadius: borderRadius!, child: child);
  }
}

class _ImageFallback extends StatelessWidget {
  const _ImageFallback();

  @override
  Widget build(BuildContext context) {
    return Container(
      color: AppColors.creamDeep,
      alignment: Alignment.center,
      child: Image.asset('assets/brand/logo.png', height: 40, fit: BoxFit.contain),
    );
  }
}

class ShimmerBox extends StatelessWidget {
  const ShimmerBox({super.key, this.height = 160, this.width});

  final double height;
  final double? width;

  @override
  Widget build(BuildContext context) {
    return Shimmer.fromColors(
      baseColor: AppColors.creamDeep,
      highlightColor: Colors.white,
      child: Container(
        height: height,
        width: width,
        color: AppColors.creamDeep,
      ),
    );
  }
}

class EmptyState extends StatelessWidget {
  const EmptyState({super.key, required this.message});

  final String message;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(32),
      child: Column(
        children: [
          const Icon(Icons.auto_awesome_outlined, color: AppColors.gold, size: 40),
          const SizedBox(height: 12),
          Text(message, textAlign: TextAlign.center, style: Theme.of(context).textTheme.bodyMedium),
        ],
      ),
    );
  }
}

class ErrorState extends StatelessWidget {
  const ErrorState({super.key, required this.message, this.onRetry});

  final String message;
  final VoidCallback? onRetry;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(32),
      child: Column(
        children: [
          const Icon(Icons.wifi_off_outlined, color: AppColors.maroon, size: 40),
          const SizedBox(height: 12),
          Text(message, textAlign: TextAlign.center),
          if (onRetry != null) ...[
            const SizedBox(height: 12),
            OutlinedButton(onPressed: onRetry, child: const Text('Retry')),
          ],
        ],
      ),
    );
  }
}

class PrimaryCta extends StatelessWidget {
  const PrimaryCta({super.key, required this.label, required this.onPressed, this.icon, this.loading = false});

  final String label;
  final VoidCallback? onPressed;
  final IconData? icon;
  final bool loading;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: double.infinity,
      child: FilledButton.icon(
        onPressed: loading ? null : onPressed,
        icon: loading
            ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
            : Icon(icon ?? Icons.arrow_forward),
        label: Text(label),
      ),
    );
  }
}

class FilterChips extends StatelessWidget {
  const FilterChips({
    super.key,
    required this.labels,
    required this.selected,
    required this.onSelected,
  });

  final List<(String, String)> labels;
  final String? selected;
  final ValueChanged<String?> onSelected;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 44,
      child: ListView(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16),
        children: [
          Padding(
            padding: const EdgeInsets.only(right: 8),
            child: ChoiceChip(
              label: const Text('All'),
              selected: selected == null,
              onSelected: (_) => onSelected(null),
            ),
          ),
          ...labels.map(
            (item) => Padding(
              padding: const EdgeInsets.only(right: 8),
              child: ChoiceChip(
                label: Text(item.$2),
                selected: selected == item.$1,
                onSelected: (_) => onSelected(item.$1),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class AppTextField extends StatelessWidget {
  const AppTextField({
    super.key,
    required this.label,
    this.controller,
    this.keyboardType,
    this.maxLines = 1,
    this.obscureText = false,
    this.validator,
    this.onTap,
    this.readOnly = false,
    this.suffix,
  });

  final String label;
  final TextEditingController? controller;
  final TextInputType? keyboardType;
  final int maxLines;
  final bool obscureText;
  final String? Function(String?)? validator;
  final VoidCallback? onTap;
  final bool readOnly;
  final Widget? suffix;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 14),
      child: TextFormField(
        controller: controller,
        keyboardType: keyboardType,
        maxLines: maxLines,
        obscureText: obscureText,
        validator: validator,
        onTap: onTap,
        readOnly: readOnly,
        decoration: InputDecoration(labelText: label, suffixIcon: suffix),
      ),
    );
  }
}

class ReviewCard extends StatelessWidget {
  const ReviewCard({super.key, required this.name, required this.quote, this.rating, this.avatar});

  final String name;
  final String quote;
  final double? rating;
  final String? avatar;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                CircleAvatar(
                  backgroundColor: AppColors.creamDeep,
                  backgroundImage: MediaUrl.resolve(avatar) != null
                      ? CachedNetworkImageProvider(MediaUrl.resolve(avatar)!)
                      : null,
                  child: MediaUrl.resolve(avatar) == null ? Text(name.isNotEmpty ? name[0] : 'B') : null,
                ),
                const SizedBox(width: 12),
                Expanded(child: Text(name, style: Theme.of(context).textTheme.titleMedium)),
                if (rating != null)
                  Row(
                    children: [
                      const Icon(Icons.star, color: AppColors.gold, size: 16),
                      Text(rating!.toStringAsFixed(1)),
                    ],
                  ),
              ],
            ),
            const SizedBox(height: 12),
            Text(quote, maxLines: 5, overflow: TextOverflow.ellipsis),
          ],
        ),
      ),
    );
  }
}
