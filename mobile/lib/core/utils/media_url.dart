import '../config/app_config.dart';

/// Resolves public/display media paths from the Laravel API.
/// Never constructs original private gallery paths (e.g. gallery/images/*).
class MediaUrl {
  const MediaUrl._();

  static String? resolve(String? path) {
    if (path == null) return null;
    final trimmed = path.trim();
    if (trimmed.isEmpty) return null;
    if (trimmed.startsWith('data:')) return trimmed;
    if (trimmed.startsWith('http://') || trimmed.startsWith('https://')) {
      return trimmed;
    }
    final origin = AppConfig.siteBaseUrl.replaceAll(RegExp(r'/$'), '');
    if (trimmed.startsWith('/')) return '$origin$trimmed';
    return '$origin/$trimmed';
  }

  static bool isProtectedDisplayPath(String? path) {
    if (path == null) return false;
    return path.contains('/protected-media/');
  }

  /// YouTube nocookie embed URL from a video id. No watch/share URLs.
  static String? youtubeNocookieEmbed(String? videoId) {
    if (videoId == null || videoId.trim().isEmpty) return null;
    return 'https://www.youtube-nocookie.com/embed/${videoId.trim()}';
  }

  /// Player document so Android WebView sends a site Referer (avoids Error 153).
  /// Only accepts youtube-nocookie /embed URLs.
  static String? youtubePlayerHtml(String? embedUrl) {
    if (embedUrl == null || embedUrl.trim().isEmpty) return null;
    final uri = Uri.tryParse(embedUrl.trim());
    if (uri == null || uri.scheme != 'https') return null;
    final host = uri.host.toLowerCase();
    if (!host.endsWith('youtube-nocookie.com')) return null;
    if (!uri.path.contains('/embed/')) return null;
    if (uri.path.contains('/watch')) return null;

    final origin = AppConfig.siteBaseUrl.replaceAll(RegExp(r'/$'), '');
    final src = uri.replace(
      queryParameters: {
        ...uri.queryParameters,
        'origin': origin,
        'rel': '0',
      },
    ).toString();
    final safeSrc = src.replaceAll('"', '%22');

    return '''<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<style>html,body{margin:0;background:#000;height:100%}iframe{position:fixed;inset:0;width:100%;height:100%;border:0}</style>
</head>
<body>
<iframe src="$safeSrc" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
</body>
</html>''';
  }
}
