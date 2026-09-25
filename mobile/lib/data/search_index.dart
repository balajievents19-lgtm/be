import 'dart:async';

import 'repositories/content_repository.dart';

class SearchHit {
  const SearchHit({required this.kind, required this.title, required this.route, this.subtitle});
  final String kind;
  final String title;
  final String route;
  final String? subtitle;
}

class SearchIndex {
  SearchIndex(this._content);

  final ContentRepository _content;

  Future<List<SearchHit>> query(String raw) async {
    final q = raw.trim().toLowerCase();
    if (q.length < 2) return const [];

    final servicesFuture = _content.fetchServices();
    final packagesFuture = _content.fetchPackages();
    final galleryFuture = _content.fetchGallery();
    final blogFuture = _content.fetchBlog();
    final services = await servicesFuture;
    final packages = await packagesFuture;
    final gallery = await galleryFuture;
    final blog = await blogFuture;

    final hits = <SearchHit>[];

    for (final item in services) {
      if (_matches(q, [item.name, item.shortDescription, item.fullDescription, item.slug])) {
        hits.add(SearchHit(kind: 'Services', title: item.name, route: '/services/${item.slug}', subtitle: item.shortDescription));
      }
    }
    for (final item in packages) {
      if (_matches(q, [item.name, item.summary, item.description, item.slug])) {
        hits.add(SearchHit(kind: 'Packages', title: item.name, route: '/packages/${item.slug}', subtitle: item.summary));
      }
    }
    for (final item in gallery) {
      if (_matches(q, [item.title, item.caption, item.categoryName, item.slug])) {
        hits.add(SearchHit(kind: 'Gallery', title: item.title ?? item.slug, route: '/gallery/${item.slug}', subtitle: item.categoryName));
      }
    }
    for (final item in blog) {
      if (_matches(q, [item.title, item.excerpt, item.slug])) {
        hits.add(SearchHit(kind: 'Latest updates', title: item.title ?? item.slug, route: '/updates/${item.slug}', subtitle: item.excerpt));
      }
    }
    return hits;
  }

  bool _matches(String q, List<String?> fields) {
    return fields.whereType<String>().any((f) => f.toLowerCase().contains(q));
  }
}

class Debouncer {
  Debouncer({this.duration = const Duration(milliseconds: 350)});
  final Duration duration;
  Timer? _timer;

  void run(VoidCallback action) {
    _timer?.cancel();
    _timer = Timer(duration, action);
  }

  void dispose() => _timer?.cancel();
}

typedef VoidCallback = void Function();
