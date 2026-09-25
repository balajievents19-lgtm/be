import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'dart:typed_data';

import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';

import 'package:balaji_royal_events/core/network/api_client.dart';
import 'package:balaji_royal_events/core/network/api_exception.dart';
import 'package:balaji_royal_events/core/network/session_store.dart';
import 'package:balaji_royal_events/core/utils/deep_link_mapper.dart';
import 'package:balaji_royal_events/data/models/models.dart';
import 'package:balaji_royal_events/data/repositories/auth_repository.dart';
import 'package:balaji_royal_events/data/repositories/content_repository.dart';
import 'package:balaji_royal_events/features/enquiry/enquiry_submit.dart';
import 'package:balaji_royal_events/widgets/cards.dart';

class _Adapter implements HttpClientAdapter {
  _Adapter(this._handler);
  final Future<ResponseBody> Function(RequestOptions) _handler;

  @override
  void close({bool force = false}) {}

  @override
  Future<ResponseBody> fetch(RequestOptions options, Stream<Uint8List>? requestStream, Future<void>? cancelFuture) {
    return _handler(options);
  }
}

ResponseBody _json(int status, Map<String, dynamic> body) {
  return ResponseBody.fromString(
    jsonEncode(body),
    status,
    headers: {
      Headers.contentTypeHeader: [Headers.jsonContentType],
    },
  );
}

ApiClient _client(Future<ResponseBody> Function(RequestOptions) handler) {
  final dio = Dio(BaseOptions(baseUrl: 'https://www.balajiroyalevents.com/api'));
  dio.httpClientAdapter = _Adapter(handler);
  final site = Dio(BaseOptions(baseUrl: 'https://www.balajiroyalevents.com'));
  site.httpClientAdapter = _Adapter(handler);
  return ApiClient(dio: dio, siteDio: site, session: SessionStore(memory: {}));
}

void main() {
  test('login success parses customer and does not invent a bearer token', () async {
    final api = _client((options) async {
      if (options.path.contains('csrf-cookie')) return ResponseBody.fromString('', 204);
      if (options.path.endsWith('/customer/login')) {
        expect(options.data['login'], 'user@example.com');
        return _json(200, {
          'data': {
            'id': 4,
            'name': 'Asha',
            'email': 'user@example.com',
            'email_verified': true,
            'needs_email_verification': false,
          },
          'message': 'Login successful.',
        });
      }
      fail('unexpected ${options.path}');
    });
    final repo = AuthRepository(api);
    final customer = await repo.login(login: 'user@example.com', password: 'secret');
    expect(customer.email, 'user@example.com');
    expect(customer.emailVerified, isTrue);
  });

  test('login validation errors map field messages', () async {
    final api = _client((options) async {
      if (options.path.contains('csrf-cookie')) return ResponseBody.fromString('', 204);
      return _json(422, {
        'message': 'The given data was invalid.',
        'errors': {
          'login': ['These credentials do not match our records.']
        },
      });
    });
    expect(
      () => AuthRepository(api).login(login: 'x', password: 'y'),
      throwsA(isA<ApiException>().having((e) => e.isValidation, 'validation', isTrue)),
    );
  });

  test('expired session on /me clears cookies', () async {
    final store = SessionStore(memory: {});
    await store.jar.saveFromResponse(
      Uri.parse('https://www.balajiroyalevents.com'),
      [Cookie('laravel_session', 'abc')],
    );
    await store.persist();
    final dio = Dio(BaseOptions(baseUrl: 'https://www.balajiroyalevents.com/api'));
    dio.httpClientAdapter = _Adapter((options) async {
      if (options.path.contains('csrf-cookie')) return ResponseBody.fromString('', 204);
      return _json(401, {'message': 'Unauthenticated.'});
    });
    final site = Dio(BaseOptions(baseUrl: 'https://www.balajiroyalevents.com'));
    site.httpClientAdapter = dio.httpClientAdapter;
    final api = ApiClient(dio: dio, siteDio: site, session: store);
    final customer = await AuthRepository(api).restoreSession();
    expect(customer, isNull);
    expect(await store.hasSessionCookie(), isFalse);
  });

  test('logout posts then clears session', () async {
    var loggedOut = false;
    final api = _client((options) async {
      if (options.path.contains('csrf-cookie')) return ResponseBody.fromString('', 204);
      if (options.path.endsWith('/customer/logout')) {
        loggedOut = true;
        return _json(200, {'message': 'Logged out.'});
      }
      fail(options.path);
    });
    await AuthRepository(api).logout();
    expect(loggedOut, isTrue);
  });

  test('enquiry success returns reference id', () async {
    final api = _client((options) async {
      if (options.path.contains('csrf-cookie')) return ResponseBody.fromString('', 204);
      expect(options.path.endsWith('/contact'), isTrue);
      expect(options.data['source'], 'slider');
      expect(options.data['event_type_id'], 2);
      return _json(201, {
        'data': {'id': 88, 'name': 'Asha', 'source': 'slider'},
      });
    });
    final result = await ContentRepository(api).submitEnquiry(
      name: 'Asha',
      mobile: '9876543210',
      eventType: const EventTypeItem(id: 2, name: 'Wedding', slug: 'wedding'),
      eventLocation: 'Jaipur',
      eventDate: '2026-12-01',
    );
    expect(result.id, 88);
  });

  test('enquiry API validation errors surface', () async {
    final api = _client((options) async {
      if (options.path.contains('csrf-cookie')) return ResponseBody.fromString('', 204);
      return _json(422, {
        'message': 'The given data was invalid.',
        'errors': {
          'event_date': ['Event date is required.']
        },
      });
    });
    expect(
      () => ContentRepository(api).submitEnquiry(
        name: 'Asha',
        mobile: '9876543210',
        eventType: const EventTypeItem(id: 2, name: 'Wedding', slug: 'wedding'),
        eventLocation: 'Jaipur',
        eventDate: '2026-12-01',
      ),
      throwsA(isA<ApiException>().having((e) => e.message, 'msg', contains('Event date'))),
    );
  });

  test('duplicate submission lock ignores the second call', () async {
    final lock = SubmissionLock();
    var runs = 0;
    final first = lock.run(() async {
      await Future<void>.delayed(const Duration(milliseconds: 30));
      runs++;
    });
    final second = lock.run(() async {
      runs++;
    });
    await Future.wait([first, second]);
    expect(runs, 1);
  });

  test('gallery download path is authorized-only endpoint', () {
    expect('/gallery/items/9/download', contains('/gallery/items/'));
    final guest = GalleryItem.fromJson({'id': 9, 'slug': 'hall', 'download_available': true});
    expect(guest.downloadAvailable, isTrue);
  });

  test('YouTube embed rejects watch URLs and stays on nocookie', () {
    final item = GalleryItem.fromJson({
      'id': 1,
      'slug': 'mehndi',
      'media_type': 'video',
      'video_source': 'youtube',
      'video_id': 'abc123',
      'embed': {'embed_url': 'https://www.youtube.com/watch?v=abc123'},
    });
    expect(youtubeEmbedFor(item), 'https://www.youtube-nocookie.com/embed/abc123');
  });

  test('deep links map website paths onto app screens', () {
    expect(deepLinkRoute(Uri.parse('https://www.balajiroyalevents.com/services/wedding')), '/services/wedding');
    expect(deepLinkRoute(Uri.parse('https://www.balajiroyalevents.com/gallery/mehndi')), '/gallery/mehndi');
    expect(deepLinkRoute(Uri.parse('https://www.balajiroyalevents.com/blog/news')), '/updates/news');
    expect(deepLinkRoute(Uri.parse('https://www.balajiroyalevents.com/contact')), '/enquiry');
    expect(deepLinkRoute(Uri.parse('https://evil.example/services/wedding')), isNull);
  });
}
