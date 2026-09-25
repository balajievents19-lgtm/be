/// Maps website / app-link URIs onto existing Flutter routes.
String? deepLinkRoute(Uri uri) {
  final host = uri.host.toLowerCase();
  if (host.isNotEmpty && host != 'www.balajiroyalevents.com' && host != 'balajiroyalevents.com') {
    return null;
  }
  var path = uri.path;
  if (path.length > 1 && path.endsWith('/')) {
    path = path.substring(0, path.length - 1);
  }
  if (path.isEmpty || path == '/') return '/home';
  const exact = {
    '/services': '/services',
    '/gallery': '/gallery',
    '/packages': '/packages',
    '/contact': '/enquiry',
    '/account': '/account',
    '/login': '/login',
    '/register': '/register',
    '/faq': '/faq',
    '/about': '/about',
    '/blog': '/updates',
  };
  if (exact.containsKey(path)) return exact[path];

  final parts = path.split('/').where((p) => p.isNotEmpty).toList();
  if (parts.length == 2 && parts[0] == 'services') return '/services/${parts[1]}';
  if (parts.length == 2 && parts[0] == 'gallery') return '/gallery/${parts[1]}';
  if (parts.length == 2 && parts[0] == 'blog') return '/updates/${parts[1]}';
  if (parts.length == 2 && parts[0] == 'packages') return '/packages/${parts[1]}';
  if (parts.length == 2 && parts[0] == 'faq') return '/faq';
  return null;
}
