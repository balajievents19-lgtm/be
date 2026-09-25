import 'package:dio/dio.dart';
import 'package:dio_cookie_manager/dio_cookie_manager.dart';

import '../config/app_config.dart';
import 'api_exception.dart';
import 'session_store.dart';

class ApiClient {
  ApiClient({
    Dio? dio,
    Dio? siteDio,
    SessionStore? session,
  })  : _session = session ?? SessionStore(),
        _dio = dio ??
            Dio(
              BaseOptions(
                baseUrl: AppConfig.apiBaseUrl,
                connectTimeout: AppConfig.connectTimeout,
                receiveTimeout: AppConfig.receiveTimeout,
                headers: const {
                  'Accept': 'application/json',
                  'Content-Type': 'application/json',
                },
              ),
            ) {
    _siteDio = siteDio ??
        Dio(
          BaseOptions(
            baseUrl: AppConfig.siteBaseUrl,
            connectTimeout: AppConfig.connectTimeout,
            receiveTimeout: AppConfig.receiveTimeout,
            headers: const {'Accept': 'application/json'},
          ),
        );
    final cookieManager = CookieManager(_session.jar);
    if (!_dio.interceptors.any((i) => i is CookieManager)) {
      _dio.interceptors.add(cookieManager);
    }
    if (!_siteDio.interceptors.any((i) => i is CookieManager)) {
      _siteDio.interceptors.add(CookieManager(_session.jar));
    }
    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          await _ready;
          options.headers['Origin'] = AppConfig.siteBaseUrl;
          options.headers['Referer'] = '${AppConfig.siteBaseUrl}/';
          options.headers['X-Requested-With'] = 'XMLHttpRequest';
          final xsrf = await _session.xsrfToken();
          if (xsrf != null && xsrf.isNotEmpty) {
            options.headers['X-XSRF-TOKEN'] = xsrf;
          }
          handler.next(options);
        },
        onResponse: (response, handler) async {
          await _session.persist();
          handler.next(response);
        },
        onError: (error, handler) async {
          await _session.persist();
          handler.next(error);
        },
      ),
    );
    _ready = _session.load();
  }

  final Dio _dio;
  late final Dio _siteDio;
  final SessionStore _session;
  late final Future<void> _ready;

  SessionStore get session => _session;

  Future<void> clearSession() => _session.clear();

  Future<bool> hasSession() => _session.hasSessionCookie();

  Future<void> ensureCsrf() async {
    await _ready;
    await _siteDio.get<dynamic>('/sanctum/csrf-cookie');
    await _session.persist();
  }

  Future<dynamic> get(String path, {Map<String, dynamic>? query}) async {
    try {
      await _ready;
      final response = await _dio.get<dynamic>(path, queryParameters: query);
      return response.data;
    } on DioException catch (e) {
      throw mapDio(e);
    }
  }

  Future<dynamic> post(String path, {Map<String, dynamic>? body}) async {
    try {
      await _ready;
      if (path.contains('customer') || path == '/contact' || path.endsWith('/contact')) {
        await ensureCsrf();
      }
      final response = await _dio.post<dynamic>(path, data: body);
      return response.data;
    } on DioException catch (e) {
      throw mapDio(e);
    }
  }

  Future<List<int>> getBytes(String path) async {
    try {
      await _ready;
      final response = await _dio.get<List<int>>(
        path,
        options: Options(responseType: ResponseType.bytes),
      );
      return response.data ?? const [];
    } on DioException catch (e) {
      throw mapDio(e);
    }
  }

  static ApiException mapDio(DioException e) {
    switch (e.type) {
      case DioExceptionType.connectionTimeout:
      case DioExceptionType.sendTimeout:
      case DioExceptionType.receiveTimeout:
        return const ApiException(
          message: 'The request timed out. Please try again.',
          kind: ApiExceptionKind.timeout,
        );
      case DioExceptionType.connectionError:
        return const ApiException(
          message: 'You appear to be offline.',
          kind: ApiExceptionKind.offline,
        );
      default:
        break;
    }

    final status = e.response?.statusCode;
    final data = e.response?.data;
    String message = 'Something went wrong. Please try again.';
    String? code;
    final fieldErrors = <String, List<String>>{};

    if (data is Map) {
      if (data['message'] is String) message = data['message'] as String;
      if (data['code'] is String) code = data['code'] as String;
      final errors = data['errors'];
      if (errors is Map) {
        errors.forEach((key, value) {
          if (value is List) {
            fieldErrors[key.toString()] = value.map((e) => e.toString()).toList();
          } else if (value != null) {
            fieldErrors[key.toString()] = [value.toString()];
          }
        });
        final first = fieldErrors.values.expand((e) => e).cast<String>().where((e) => e.isNotEmpty);
        if (first.isNotEmpty) message = first.first;
      }
    }

    var kind = ApiExceptionKind.unknown;
    if (status == 401) kind = ApiExceptionKind.unauthorized;
    if (status == 404) kind = ApiExceptionKind.notFound;
    if (status == 422) kind = ApiExceptionKind.validation;
    if (status != null && status >= 500) kind = ApiExceptionKind.server;

    return ApiException(
      message: message,
      statusCode: status,
      kind: kind,
      code: code,
      fieldErrors: fieldErrors,
    );
  }
}
