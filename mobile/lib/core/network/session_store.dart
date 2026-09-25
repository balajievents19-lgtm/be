import 'dart:convert';

import 'package:cookie_jar/cookie_jar.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import '../config/app_config.dart';

/// Persists Laravel session cookies in secure storage (never SharedPreferences).
class SessionStore {
  SessionStore({
    FlutterSecureStorage? storage,
    Map<String, String>? memory,
  })  : _storage = storage ?? const FlutterSecureStorage(),
        _memory = memory;

  static const cookiesKey = 'bre_session_cookies';

  final FlutterSecureStorage _storage;
  final Map<String, String>? _memory;
  final DefaultCookieJar jar = DefaultCookieJar(ignoreExpires: true);

  Future<String?> _read() async {
    try {
      if (_memory != null) return _memory[cookiesKey];
      return await _storage.read(key: cookiesKey);
    } catch (_) {
      return null;
    }
  }

  Future<void> _write(String value) async {
    try {
      if (_memory != null) {
        _memory[cookiesKey] = value;
        return;
      }
      await _storage.write(key: cookiesKey, value: value);
    } catch (_) {}
  }

  Future<void> _delete() async {
    try {
      if (_memory != null) {
        _memory.remove(cookiesKey);
        return;
      }
      await _storage.delete(key: cookiesKey);
    } catch (_) {}
  }

  Future<void> load() async {
    final raw = await _read();
    if (raw == null || raw.isEmpty) return;
    try {
      final list = jsonDecode(raw);
      if (list is! List) return;
      final cookies = <Cookie>[];
      for (final item in list) {
        if (item is! Map) continue;
        final name = item['name']?.toString();
        final value = item['value']?.toString();
        if (name == null || value == null || name.isEmpty) continue;
        final cookie = Cookie(name, value);
        final domain = item['domain']?.toString();
        if (domain != null && domain.isNotEmpty) cookie.domain = domain;
        cookie.path = item['path']?.toString() ?? '/';
        cookie.secure = item['secure'] == true;
        cookie.httpOnly = item['httpOnly'] == true;
        cookies.add(cookie);
      }
      final uris = [
        Uri.parse(AppConfig.siteBaseUrl),
        Uri.parse(AppConfig.apiBaseUrl),
      ];
      for (final uri in uris) {
        await jar.saveFromResponse(uri, cookies);
      }
    } catch (_) {
      await clear();
    }
  }

  Future<void> persist() async {
    final uris = [
      Uri.parse(AppConfig.siteBaseUrl),
      Uri.parse(AppConfig.apiBaseUrl),
    ];
    final merged = <String, Cookie>{};
    for (final uri in uris) {
      for (final cookie in await jar.loadForRequest(uri)) {
        merged['${cookie.domain}|${cookie.path}|${cookie.name}'] = cookie;
      }
    }
    final encoded = jsonEncode(
      merged.values
          .map(
            (cookie) => {
              'name': cookie.name,
              'value': cookie.value,
              'domain': cookie.domain,
              'path': cookie.path ?? '/',
              'secure': cookie.secure,
              'httpOnly': cookie.httpOnly,
            },
          )
          .toList(),
    );
    await _write(encoded);
  }

  Future<void> clear() async {
    await jar.deleteAll();
    await _delete();
  }

  Future<bool> hasSessionCookie() async {
    final cookies = await jar.loadForRequest(Uri.parse(AppConfig.siteBaseUrl));
    return cookies.any((c) => c.name.toLowerCase().contains('session') || c.name == 'laravel_session' || c.name.startsWith('remember_customer_'));
  }

  Future<String?> xsrfToken() async {
    final cookies = [
      ...await jar.loadForRequest(Uri.parse(AppConfig.siteBaseUrl)),
      ...await jar.loadForRequest(Uri.parse(AppConfig.apiBaseUrl)),
    ];
    for (final cookie in cookies) {
      if (cookie.name == 'XSRF-TOKEN') {
        return Uri.decodeComponent(cookie.value);
      }
    }
    return null;
  }
}
