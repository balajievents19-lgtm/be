import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../core/l10n/locale_controller.dart';
import '../../core/theme/app_colors.dart';
import '../../data/models/models.dart';
import '../../data/providers.dart';
import '../../widgets/cards.dart';
import '../../widgets/common.dart';
import '../../widgets/youtube_embed.dart';
import '../enquiry/enquiry_submit.dart';

class HomeScreen extends ConsumerWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final t = ref.watch(stringsProvider);
    final home = ref.watch(homeProvider);

    return home.when(
      loading: () => Scaffold(
        backgroundColor: AppColors.cream,
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Image.asset('assets/brand/logo.png', width: 96, height: 96, fit: BoxFit.contain),
              const SizedBox(height: 20),
              const SizedBox(width: 28, height: 28, child: CircularProgressIndicator(strokeWidth: 2.4, color: AppColors.brand)),
            ],
          ),
        ),
      ),
      error: (e, _) => Scaffold(
        body: ErrorState(message: t.get('error'), onRetry: () => ref.refresh(homeProvider)),
      ),
      data: (payload) => Scaffold(
        appBar: AppHeader(
          title: payload.settings.companyName ?? t.get('appName'),
          showLogo: true,
          logoUrl: payload.settings.logo,
          onMenu: () => context.push('/more'),
          onSearch: () => context.push('/search'),
          onNotifications: () => context.push('/notifications'),
        ),
        body: RefreshIndicator(
          onRefresh: () async {
            ref.invalidate(homeProvider);
            await ref.read(homeProvider.future);
          },
          child: ListView(
          padding: const EdgeInsets.only(bottom: 32),
          children: [
            _HeroSlider(slides: payload.hero, cta: t.get('planYourEvent')),
            _QuickEnquiry(payload: payload),
            SectionHeader(title: t.get('services'), actionLabel: t.get('viewAll'), onAction: () => context.go('/services')),
            _ServicePreview(services: payload.services),
            SectionHeader(title: t.get('gallery'), actionLabel: t.get('viewAll'), onAction: () => context.go('/gallery')),
            if (payload.gallery.isEmpty)
              EmptyState(message: t.get('empty'))
            else
              SizedBox(
                height: 280,
                child: ListView.separated(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  scrollDirection: Axis.horizontal,
                  itemCount: payload.gallery.length,
                  separatorBuilder: (_, __) => const SizedBox(width: 12),
                  itemBuilder: (_, i) {
                    final item = payload.gallery[i];
                    return SizedBox(width: 200, child: GalleryCard(item: item, onTap: () => context.push('/gallery/${item.slug}')));
                  },
                ),
              ),
            _VideosAndMedia(payload: payload),
            Consumer(
              builder: (context, ref, _) {
                final packages = ref.watch(packagesProvider);
                return packages.when(
                  loading: () => const Padding(padding: EdgeInsets.all(16), child: ShimmerBox()),
                  error: (_, __) => const SizedBox.shrink(),
                  data: (items) {
                    if (items.isEmpty) return const SizedBox.shrink();
                    return Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        SectionHeader(title: t.get('packages'), actionLabel: t.get('viewAll'), onAction: () => context.push('/packages')),
                        LayoutBuilder(
                          builder: (context, constraints) {
                            final cardWidth = (constraints.maxWidth * 0.72).clamp(240.0, 300.0);
                            return SizedBox(
                              height: packageStripHeight(context, cardWidth: cardWidth, packages: items),
                              child: ListView.separated(
                                padding: const EdgeInsets.symmetric(horizontal: 16),
                                scrollDirection: Axis.horizontal,
                                itemCount: items.length,
                                separatorBuilder: (_, __) => const SizedBox(width: 12),
                                itemBuilder: (_, i) {
                                  final p = items[i];
                                  return SizedBox(
                                    width: cardWidth,
                                    child: PackageCard(package: p, onTap: () => context.push('/packages/${p.slug}')),
                                  );
                                },
                              ),
                            );
                          },
                        ),
                      ],
                    );
                  },
                );
              },
            ),
            SectionHeader(title: t.get('latestUpdates')),
            if (payload.blog.isEmpty)
              EmptyState(message: t.get('empty'))
            else
              ...payload.blog.take(3).map(
                    (b) => ListTile(
                      title: Text(b.title ?? ''),
                      subtitle: Text(b.excerpt ?? '', maxLines: 2, overflow: TextOverflow.ellipsis),
                      onTap: () => context.push('/updates'),
                    ),
                  ),
            SectionHeader(title: t.get('reviews'), actionLabel: t.get('viewAll'), onAction: () => context.push('/reviews')),
            if (payload.testimonials.isEmpty)
              EmptyState(message: t.get('empty'))
            else
              ...payload.testimonials.take(3).map(
                    (r) => Padding(
                      padding: const EdgeInsets.fromLTRB(16, 0, 16, 12),
                      child: ReviewCard(name: r.name ?? '', quote: r.quote ?? '', rating: r.rating, avatar: r.avatar),
                    ),
                  ),
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
              child: PrimaryCta(label: t.get('planYourEvent'), onPressed: () => context.go('/enquiry')),
            ),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: OutlinedButton(
                onPressed: () => context.push('/contact'),
                child: Text(t.get('contactUs')),
              ),
            ),
          ],
        ),
        ),
      ),
    );
  }
}

class _HeroSlider extends StatefulWidget {
  const _HeroSlider({required this.slides, required this.cta});
  final List<HeroSlide> slides;
  final String cta;

  @override
  State<_HeroSlider> createState() => _HeroSliderState();
}

class _HeroSliderState extends State<_HeroSlider> {
  final _controller = PageController();
  int _index = 0;

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (widget.slides.isEmpty) return const SizedBox.shrink();
    return Column(
      children: [
        SizedBox(
          height: 340,
          child: PageView.builder(
            controller: _controller,
            onPageChanged: (i) => setState(() => _index = i),
            itemCount: widget.slides.length,
            itemBuilder: (context, i) {
              final slide = widget.slides[i];
              return Stack(
                fit: StackFit.expand,
                children: [
                  AppNetworkImage(url: slide.image),
                  const DecoratedBox(
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        begin: Alignment.bottomCenter,
                        end: Alignment.center,
                        colors: [Color(0xCC0E1123), Colors.transparent],
                      ),
                    ),
                  ),
                  Positioned(
                    left: 20,
                    right: 20,
                    bottom: 28,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          [slide.title, slide.titleHighlight].whereType<String>().join(' '),
                          style: Theme.of(context).textTheme.headlineSmall?.copyWith(color: Colors.white),
                        ),
                        if (slide.subtitle != null) ...[
                          const SizedBox(height: 8),
                          Text(slide.subtitle!, maxLines: 3, overflow: TextOverflow.ellipsis, style: const TextStyle(color: Colors.white70)),
                        ],
                        const SizedBox(height: 12),
                        FilledButton(
                          onPressed: () => context.go('/enquiry'),
                          child: Text(slide.buttonText ?? widget.cta),
                        ),
                      ],
                    ),
                  ),
                ],
              );
            },
          ),
        ),
        const SizedBox(height: 10),
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: List.generate(
            widget.slides.length,
            (i) => Container(
              width: i == _index ? 18 : 8,
              height: 8,
              margin: const EdgeInsets.symmetric(horizontal: 3),
              decoration: BoxDecoration(
                color: i == _index ? AppColors.brand : AppColors.gold.withValues(alpha: 0.4),
                borderRadius: BorderRadius.circular(8),
              ),
            ),
          ),
        ),
      ],
    );
  }
}

class _QuickEnquiry extends ConsumerStatefulWidget {
  const _QuickEnquiry({required this.payload});
  final HomePayload payload;

  @override
  ConsumerState<_QuickEnquiry> createState() => _QuickEnquiryState();
}

class _QuickEnquiryState extends ConsumerState<_QuickEnquiry> {
  final _form = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _phone = TextEditingController();
  final _location = TextEditingController();
  final _budget = TextEditingController();
  DateTime? _date;
  EventTypeItem? _type;
  bool _submitting = false;

  @override
  void dispose() {
    _name.dispose();
    _phone.dispose();
    _location.dispose();
    _budget.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final t = ref.watch(stringsProvider);
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 20, 16, 0),
      child: Card(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Form(
            key: _form,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(t.get('planYourEvent'), style: Theme.of(context).textTheme.titleLarge),
                const SizedBox(height: 12),
                AppTextField(label: t.get('yourName'), controller: _name, validator: (v) => (v == null || v.trim().isEmpty) ? t.get('required') : null),
                AppTextField(
                  label: t.get('phoneNumber'),
                  controller: _phone,
                  keyboardType: TextInputType.phone,
                  validator: (v) {
                    final digits = (v ?? '').replaceAll(RegExp(r'\D'), '');
                    if (digits.length < 10) return t.get('invalidPhone');
                    return null;
                  },
                ),
                DropdownMenu<EventTypeItem>(
                  expandedInsets: EdgeInsets.zero,
                  label: Text(t.get('eventType')),
                  dropdownMenuEntries: widget.payload.eventTypes
                      .map((e) => DropdownMenuEntry(value: e, label: e.name))
                      .toList(),
                  onSelected: (v) => setState(() => _type = v),
                ),
                const SizedBox(height: 14),
                AppTextField(
                  label: t.get('eventLocation'),
                  controller: _location,
                  validator: (v) => (v == null || v.trim().isEmpty) ? t.get('required') : null,
                ),
                AppTextField(
                  label: t.get('selectDate'),
                  readOnly: true,
                  controller: TextEditingController(
                    text: _date == null ? '' : '${_date!.year}-${_date!.month.toString().padLeft(2, '0')}-${_date!.day.toString().padLeft(2, '0')}',
                  ),
                  validator: (_) => _date == null ? t.get('required') : null,
                  onTap: () async {
                    final picked = await showDatePicker(
                      context: context,
                      firstDate: DateTime.now(),
                      lastDate: DateTime.now().add(const Duration(days: 365 * 3)),
                      initialDate: DateTime.now(),
                    );
                    if (picked != null) setState(() => _date = picked);
                  },
                ),
                AppTextField(label: t.get('budget'), controller: _budget),
                PrimaryCta(
                  label: _submitting ? t.get('submitting') : t.get('submitEnquiry'),
                  loading: _submitting,
                  onPressed: () async {
                    if (_submitting) return;
                    if (_form.currentState?.validate() != true) return;
                    if (_type == null || _date == null) {
                      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(t.get('required'))));
                      return;
                    }
                    setState(() => _submitting = true);
                    await submitPlanEnquiry(
                      context: context,
                      ref: ref,
                      name: _name.text,
                      phone: _phone.text,
                      type: _type!,
                      location: _location.text,
                      date: _date!,
                      budget: _budget.text,
                    );
                    if (mounted) setState(() => _submitting = false);
                  },
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _ServicePreview extends StatelessWidget {
  const _ServicePreview({required this.services});
  final List<ServiceItem> services;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: GridView.builder(
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        itemCount: services.length.clamp(0, 6),
        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: 2,
          mainAxisSpacing: 12,
          crossAxisSpacing: 12,
          childAspectRatio: 0.68,
        ),
        itemBuilder: (context, i) {
          final s = services[i];
          return ServiceCard(service: s, onTap: () => context.push('/services/${s.slug}'));
        },
      ),
    );
  }
}

class _VideosAndMedia extends ConsumerWidget {
  const _VideosAndMedia({required this.payload});

  final HomePayload payload;

  List<Object> get _items {
    if (payload.externalMedia.isNotEmpty) return payload.externalMedia;
    return payload.gallery.where((item) => item.isVideo).toList();
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final t = ref.watch(stringsProvider);
    final items = _items;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SectionHeader(
          title: t.get('videosMedia'),
          actionLabel: t.get('viewAllMedia'),
          onAction: () => context.go('/gallery'),
        ),
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 0, 16, 12),
          child: Text(
            t.get('videosMediaSubtitle'),
            style: const TextStyle(color: AppColors.muted),
          ),
        ),
        if (items.isEmpty)
          EmptyState(message: t.get('empty'))
        else
          SizedBox(
            height: 228,
            child: ListView.separated(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              scrollDirection: Axis.horizontal,
              itemCount: items.length,
              separatorBuilder: (_, __) => const SizedBox(width: 12),
              itemBuilder: (_, i) {
                final item = items[i];
                if (item is ExternalMediaItem) {
                  return SizedBox(
                    width: 220,
                    child: _HomeMediaCard(
                      title: item.title ?? '',
                      typeLabel: item.typeLabel,
                      thumbnail: item.thumbnail,
                      onPlay: () => _openExternal(context, item),
                    ),
                  );
                }
                final gallery = item as GalleryItem;
                return SizedBox(
                  width: 220,
                  child: _HomeMediaCard(
                    title: gallery.title ?? '',
                    typeLabel: gallery.mediaType ?? 'Video',
                    thumbnail: gallery.preview,
                    onPlay: () => context.push('/gallery/${gallery.slug}'),
                  ),
                );
              },
            ),
          ),
      ],
    );
  }

  Future<void> _openExternal(BuildContext context, ExternalMediaItem item) async {
    final embed = item.embedUrl;
    if (item.isYoutube && embed != null && embed.contains('youtube-nocookie.com')) {
      await showDialog<void>(
        context: context,
        builder: (context) => Dialog(
          insetPadding: const EdgeInsets.all(12),
          backgroundColor: Colors.black,
          child: AspectRatio(
            aspectRatio: 16 / 9,
            child: YoutubeEmbed(url: embed),
          ),
        ),
      );
      return;
    }
    final link = item.url;
    if (link == null) return;
    final uri = Uri.tryParse(link);
    if (uri != null) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }
}

class _HomeMediaCard extends StatelessWidget {
  const _HomeMediaCard({
    required this.title,
    required this.typeLabel,
    required this.onPlay,
    this.thumbnail,
  });

  final String title;
  final String typeLabel;
  final String? thumbnail;
  final VoidCallback onPlay;

  @override
  Widget build(BuildContext context) {
    return Card(
      clipBehavior: Clip.antiAlias,
      color: AppColors.navy,
      child: InkWell(
        onTap: onPlay,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: Stack(
                fit: StackFit.expand,
                children: [
                  if (thumbnail != null && thumbnail!.isNotEmpty)
                    AppNetworkImage(url: thumbnail)
                  else
                    const ColoredBox(color: Color(0xFF1A1F38)),
                  const Center(
                    child: CircleAvatar(
                      backgroundColor: Color(0xEBF15B22),
                      radius: 24,
                      child: Icon(Icons.play_arrow, color: Colors.white, size: 28),
                    ),
                  ),
                  Positioned(
                    top: 8,
                    left: 8,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: AppColors.gold.withValues(alpha: 0.92),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        typeLabel,
                        style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: Color(0xFF0E1123)),
                      ),
                    ),
                  ),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(10, 8, 10, 10),
              child: Text(
                title,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
