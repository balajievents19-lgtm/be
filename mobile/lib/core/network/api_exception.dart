class ApiException implements Exception {
  const ApiException({
    required this.message,
    this.statusCode,
    this.kind = ApiExceptionKind.unknown,
    this.code,
    this.fieldErrors = const {},
  });

  final String message;
  final int? statusCode;
  final ApiExceptionKind kind;
  final String? code;
  final Map<String, List<String>> fieldErrors;

  bool get isUnauthorized => statusCode == 401 || kind == ApiExceptionKind.unauthorized;
  bool get isOffline => kind == ApiExceptionKind.offline;
  bool get isValidation => kind == ApiExceptionKind.validation;
  bool get needsEmailVerification => code == 'email_verification_required';

  String? firstFieldError() {
    for (final errors in fieldErrors.values) {
      if (errors.isNotEmpty) return errors.first;
    }
    return null;
  }

  @override
  String toString() => message;
}

enum ApiExceptionKind {
  timeout,
  offline,
  unauthorized,
  notFound,
  validation,
  server,
  unknown,
}
