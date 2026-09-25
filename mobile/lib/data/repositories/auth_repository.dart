import '../../core/config/app_config.dart';
import '../../core/network/api_client.dart';
import '../../core/network/api_exception.dart';
import '../models/models.dart';

class AuthRepository {
  AuthRepository(this._api);

  final ApiClient _api;

  Future<OAuthProviderStatus> oauthProviders() async {
    final raw = await _api.get('/customer/oauth/providers');
    final data = raw is Map ? (raw['data'] ?? raw) : raw;
    final map = JsonMap.asMap(data);
    return OAuthProviderStatus(
      google: JsonMap.flag(map['google']),
      facebook: JsonMap.flag(map['facebook']),
    );
  }

  String oauthRedirectUrl(String provider) {
    return '${AppConfig.apiBaseUrl}/customer/oauth/$provider/redirect';
  }

  Future<CustomerProfile> login({required String login, required String password}) async {
    final raw = await _api.post('/customer/login', body: {
      'login': login.trim(),
      'password': password,
      'remember': true,
    });
    return _customerFrom(raw);
  }

  Future<RegisterResult> register({
    required String name,
    required String email,
    required String phone,
    required String password,
    required String passwordConfirmation,
  }) async {
    final raw = await _api.post('/customer/register', body: {
      'name': name.trim(),
      'email': email.trim(),
      'phone': phone.replaceAll(RegExp(r'\D'), ''),
      'password': password,
      'password_confirmation': passwordConfirmation,
      'terms': true,
    });
    final map = JsonMap.asMap(raw);
    CustomerProfile? customer;
    try {
      customer = _customerFrom(raw);
    } catch (_) {}
    return RegisterResult(
      customer: customer,
      message: JsonMap.str(map['message']) ?? 'Check your email for a verification code.',
      needsVerification: true,
      email: JsonMap.str(JsonMap.asMap(map['verification'])['masked_email']) ?? email.trim(),
    );
  }

  Future<String> forgotPassword(String email) async {
    final raw = await _api.post('/customer/forgot-password', body: {
      'email': email.trim(),
      'login': email.trim(),
    });
    final map = JsonMap.asMap(raw);
    return JsonMap.str(map['message']) ?? 'If that account exists, we sent password reset instructions.';
  }

  Future<CustomerProfile> verifyEmail({required String email, required String otp}) async {
    final raw = await _api.post('/customer/email/verify', body: {
      'email': email.trim(),
      'otp': otp.trim(),
    });
    return _customerFrom(raw);
  }

  Future<String> resendVerification(String email) async {
    final raw = await _api.post('/customer/email/resend', body: {'email': email.trim()});
    return JsonMap.str(JsonMap.asMap(raw)['message']) ?? 'A verification email was sent.';
  }

  Future<CustomerProfile> me() async {
    final raw = await _api.get('/customer/me');
    return _customerFrom(raw);
  }

  Future<CustomerProfile?> restoreSession() async {
    if (!await _api.hasSession()) return null;
    try {
      return await me();
    } on ApiException catch (e) {
      if (e.isUnauthorized) {
        await clearSession();
      }
      return null;
    }
  }

  Future<void> logout() async {
    try {
      await _api.post('/customer/logout');
    } on ApiException {
      // Still drop local cookies.
    } finally {
      await clearSession();
    }
  }

  Future<void> clearSession() => _api.clearSession();

  Future<String> changePassword({
    required String currentPassword,
    required String password,
    required String passwordConfirmation,
  }) async {
    final raw = await _api.post('/customer/change-password', body: {
      'current_password': currentPassword,
      'password': password,
      'password_confirmation': passwordConfirmation,
    });
    return JsonMap.str(JsonMap.asMap(raw)['message']) ?? 'Password updated.';
  }

  Future<String> changeMobile({required String currentPassword, required String phone}) async {
    final raw = await _api.post('/customer/change-mobile', body: {
      'current_password': currentPassword,
      'phone': phone.replaceAll(RegExp(r'\D'), ''),
    });
    return JsonMap.str(JsonMap.asMap(raw)['message']) ?? 'Mobile number updated.';
  }

  Future<String> changeEmail({required String currentPassword, required String email}) async {
    final raw = await _api.post('/customer/change-email', body: {
      'current_password': currentPassword,
      'email': email.trim(),
    });
    return JsonMap.str(JsonMap.asMap(raw)['message']) ?? 'Check your email to confirm the new address.';
  }

  CustomerProfile _customerFrom(dynamic raw) {
    final map = JsonMap.asMap(raw);
    final data = map['data'] != null ? JsonMap.asMap(map['data']) : map;
    if (data.isEmpty) {
      throw const ApiException(message: 'Unexpected authentication response.');
    }
    return CustomerProfile.fromJson(data);
  }
}

class OAuthProviderStatus {
  const OAuthProviderStatus({this.google = false, this.facebook = false});
  final bool google;
  final bool facebook;
}

class RegisterResult {
  const RegisterResult({
    this.customer,
    required this.message,
    this.needsVerification = true,
    this.email,
  });

  final CustomerProfile? customer;
  final String message;
  final bool needsVerification;
  final String? email;
}
