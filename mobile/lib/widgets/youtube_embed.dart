import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';

import '../core/config/app_config.dart';
import '../core/utils/media_url.dart';

/// In-app YouTube player: youtube-nocookie embed + live origin Referer/baseUrl.
class YoutubeEmbed extends StatefulWidget {
  const YoutubeEmbed({super.key, required this.url});

  final String url;

  @override
  State<YoutubeEmbed> createState() => _YoutubeEmbedState();
}

class _YoutubeEmbedState extends State<YoutubeEmbed> {
  late final WebViewController _controller;

  bool _allowed(Uri uri) {
    final host = uri.host.toLowerCase();
    if (uri.scheme == 'about' || uri.scheme == 'data') return true;
    if (uri.path.contains('/watch') || host == 'youtu.be' || host.endsWith('.youtu.be')) {
      return false;
    }
    if (host.endsWith('youtube-nocookie.com')) return true;
    if (host.endsWith('youtube.com')) return true;
    if (host.endsWith('googlevideo.com') ||
        host.endsWith('ytimg.com') ||
        host.endsWith('ggpht.com') ||
        host.endsWith('gstatic.com') ||
        host.endsWith('google.com')) {
      return true;
    }
    return false;
  }

  @override
  void initState() {
    super.initState();
    final uri = Uri.parse(widget.url);
    final html = MediaUrl.youtubePlayerHtml(widget.url);
    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setBackgroundColor(const Color(0xFF000000))
      ..setNavigationDelegate(
        NavigationDelegate(
          onNavigationRequest: (request) {
            final next = Uri.parse(request.url);
            return _allowed(next) ? NavigationDecision.navigate : NavigationDecision.prevent;
          },
        ),
      );
    if (html != null) {
      _controller.loadHtmlString(html, baseUrl: '${AppConfig.siteBaseUrl}/');
    } else {
      _controller.loadRequest(uri, headers: {'Referer': '${AppConfig.siteBaseUrl}/'});
    }
  }

  @override
  Widget build(BuildContext context) {
    return WebViewWidget(controller: _controller);
  }
}
