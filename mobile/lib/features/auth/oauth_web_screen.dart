import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:webview_cookie_manager_plus/webview_cookie_manager_plus.dart';
import 'package:webview_flutter/webview_flutter.dart';

import '../../core/config/app_config.dart';
import '../../core/l10n/locale_controller.dart';
import '../../data/auth_controller.dart';
import '../../data/providers.dart';

class OAuthWebScreen extends ConsumerStatefulWidget {
  const OAuthWebScreen({super.key, required this.provider});
  final String provider;

  @override
  ConsumerState<OAuthWebScreen> createState() => _OAuthWebScreenState();
}

class _OAuthWebScreenState extends ConsumerState<OAuthWebScreen> {
  late final WebViewController _controller;
  var _finishing = false;

  static const _trusted = {
    'www.balajiroyalevents.com',
    'balajiroyalevents.com',
    'accounts.google.com',
    'accounts.youtube.com',
    'google.com',
    'www.google.com',
    'apis.google.com',
    'facebook.com',
    'www.facebook.com',
    'm.facebook.com',
    'mbasic.facebook.com',
  };

  bool _allowed(Uri uri) {
    final host = uri.host.toLowerCase();
    if (_trusted.contains(host)) return true;
    if (host.endsWith('.google.com') || host.endsWith('.facebook.com') || host.endsWith('.fbcdn.net')) return true;
    return false;
  }

  Future<void> _maybeFinish(Uri uri) async {
    if (_finishing) return;
    final host = uri.host.toLowerCase();
    if (host != AppConfig.siteHost && host != 'balajiroyalevents.com') return;
    final oauth = uri.queryParameters['oauth'];
    if (oauth == 'not_configured' || oauth == 'unsupported') {
      _finishing = true;
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(ref.read(stringsProvider).get('oauthNotConfigured'))));
        context.pop();
      }
      return;
    }
    if (oauth != 'success' && uri.path != '/account') return;
    if (oauth != 'success' && !uri.queryParameters.containsKey('oauth')) return;
    if (oauth != 'success') return;

    _finishing = true;
    final manager = WebviewCookieManager();
    final cookies = await manager.getCookies(AppConfig.siteBaseUrl);
    final jar = ref.read(apiClientProvider).session.jar;
    await jar.saveFromResponse(Uri.parse(AppConfig.siteBaseUrl), cookies.cast<Cookie>());
    await jar.saveFromResponse(Uri.parse(AppConfig.apiBaseUrl), cookies.cast<Cookie>());
    await ref.read(apiClientProvider).session.persist();
    try {
      await ref.read(authControllerProvider.notifier).refreshMe();
    } catch (_) {}
    if (mounted) context.go('/account');
  }

  @override
  void initState() {
    super.initState();
    final url = ref.read(authRepositoryProvider).oauthRedirectUrl(widget.provider);
    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setNavigationDelegate(
        NavigationDelegate(
          onNavigationRequest: (request) {
            final uri = Uri.parse(request.url);
            if (!_allowed(uri)) return NavigationDecision.prevent;
            return NavigationDecision.navigate;
          },
          onPageFinished: (url) {
            _maybeFinish(Uri.parse(url));
          },
        ),
      )
      ..loadRequest(Uri.parse(url));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(widget.provider == 'google' ? 'Google' : 'Facebook')),
      body: WebViewWidget(controller: _controller),
    );
  }
}
