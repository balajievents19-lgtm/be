import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/network/api_exception.dart';
import 'models/models.dart';
import 'providers.dart';
import 'repositories/auth_repository.dart';

class AuthState {
  const AuthState({this.customer, this.restoring = false});

  final CustomerProfile? customer;
  final bool restoring;

  bool get signedIn => customer != null;
  bool get verified => customer?.emailVerified == true && customer?.needsEmailVerification != true;
  bool get canSubmitEnquiry =>
      customer != null && (customer!.verifiedForEnquiry || verified);
}

class AuthController extends StateNotifier<AuthState> {
  AuthController(this._repo) : super(const AuthState(restoring: true));

  final AuthRepository _repo;

  Future<void> restore() async {
    state = const AuthState(restoring: true);
    final customer = await _repo.restoreSession();
    state = AuthState(customer: customer);
  }

  Future<CustomerProfile> login({required String login, required String password}) async {
    final customer = await _repo.login(login: login, password: password);
    state = AuthState(customer: customer);
    return customer;
  }

  Future<RegisterResult> register({
    required String name,
    required String email,
    required String phone,
    required String password,
    required String passwordConfirmation,
  }) {
    return _repo.register(
      name: name,
      email: email,
      phone: phone,
      password: password,
      passwordConfirmation: passwordConfirmation,
    );
  }

  Future<void> logout() async {
    await _repo.logout();
    state = const AuthState();
  }

  Future<void> refreshMe() async {
    try {
      final customer = await _repo.me();
      state = AuthState(customer: customer);
    } on ApiException catch (e) {
      if (e.isUnauthorized) {
        await _repo.clearSession();
        state = const AuthState();
      }
      rethrow;
    }
  }

  Future<CustomerProfile> verifyEmail({required String email, required String otp}) async {
    final customer = await _repo.verifyEmail(email: email, otp: otp);
    state = AuthState(customer: customer);
    return customer;
  }
}

final authControllerProvider = StateNotifierProvider<AuthController, AuthState>((ref) {
  return AuthController(ref.watch(authRepositoryProvider));
});
