import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/l10n/locale_controller.dart';
import '../../core/network/api_exception.dart';
import '../../data/auth_controller.dart';
import '../../data/providers.dart';
import '../../widgets/common.dart';

class LoginScreen extends ConsumerStatefulWidget {
  const LoginScreen({super.key});

  @override
  ConsumerState<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends ConsumerState<LoginScreen> {
  final _email = TextEditingController();
  final _password = TextEditingController();
  bool _busy = false;

  @override
  void dispose() {
    _email.dispose();
    _password.dispose();
    super.dispose();
  }

  Future<void> _login() async {
    if (_busy) return;
    setState(() => _busy = true);
    try {
      await ref.read(authControllerProvider.notifier).login(login: _email.text, password: _password.text);
      if (mounted) context.go('/account');
    } on ApiException catch (e) {
      if (!mounted) return;
      if (e.needsEmailVerification || e.code == 'email_verification_required') {
        context.push('/verify-email', extra: _email.text.trim());
      }
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final t = ref.watch(stringsProvider);
    final oauth = ref.watch(oauthProvidersProvider);
    return Scaffold(
      appBar: AppBar(title: Text(t.get('login'))),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Text(t.get('signInTitle'), style: Theme.of(context).textTheme.headlineSmall),
          const SizedBox(height: 16),
          AppTextField(label: t.get('email'), controller: _email, keyboardType: TextInputType.emailAddress),
          AppTextField(label: t.get('password'), controller: _password, obscureText: true),
          Align(
            alignment: Alignment.centerRight,
            child: TextButton(onPressed: () => context.push('/forgot-password'), child: Text(t.get('forgotPassword'))),
          ),
          PrimaryCta(label: t.get('login'), loading: _busy, onPressed: _login),
          const SizedBox(height: 12),
          oauth.when(
            loading: () => const SizedBox.shrink(),
            error: (_, __) => const SizedBox.shrink(),
            data: (status) => Column(
              children: [
                OutlinedButton.icon(
                  onPressed: () {
                    if (!status.google) {
                      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(t.get('oauthNotConfigured'))));
                      return;
                    }
                    context.push('/oauth/google');
                  },
                  icon: const Icon(Icons.g_mobiledata),
                  label: Text(t.get('continueGoogle')),
                ),
                const SizedBox(height: 8),
                OutlinedButton.icon(
                  onPressed: () {
                    if (!status.facebook) {
                      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(t.get('oauthNotConfigured'))));
                      return;
                    }
                    context.push('/oauth/facebook');
                  },
                  icon: const Icon(Icons.facebook),
                  label: Text(t.get('continueFacebook')),
                ),
              ],
            ),
          ),
          TextButton(onPressed: () => context.push('/register'), child: Text(t.get('register'))),
        ],
      ),
    );
  }
}

class RegisterScreen extends ConsumerStatefulWidget {
  const RegisterScreen({super.key});

  @override
  ConsumerState<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends ConsumerState<RegisterScreen> {
  final _form = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _email = TextEditingController();
  final _phone = TextEditingController();
  final _password = TextEditingController();
  final _confirm = TextEditingController();
  bool _terms = false;
  bool _busy = false;

  @override
  void dispose() {
    _name.dispose();
    _email.dispose();
    _phone.dispose();
    _password.dispose();
    _confirm.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (_busy) return;
    if (_form.currentState?.validate() != true || !_terms) {
      if (!_terms) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(ref.read(stringsProvider).get('acceptTerms'))));
      }
      return;
    }
    setState(() => _busy = true);
    try {
      final result = await ref.read(authControllerProvider.notifier).register(
            name: _name.text,
            email: _email.text,
            phone: _phone.text,
            password: _password.text,
            passwordConfirmation: _confirm.text,
          );
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(result.message)));
      context.push('/verify-email', extra: _email.text.trim());
    } on ApiException catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final t = ref.watch(stringsProvider);
    return Scaffold(
      appBar: AppBar(title: Text(t.get('register'))),
      body: Form(
        key: _form,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            Text(t.get('registerTitle'), style: Theme.of(context).textTheme.headlineSmall),
            const SizedBox(height: 16),
            AppTextField(label: t.get('yourName'), controller: _name, validator: (v) => (v == null || v.trim().isEmpty) ? t.get('required') : null),
            AppTextField(label: t.get('email'), controller: _email, keyboardType: TextInputType.emailAddress, validator: (v) => (v == null || !v.contains('@')) ? t.get('required') : null),
            AppTextField(
              label: t.get('phoneNumber'),
              controller: _phone,
              keyboardType: TextInputType.phone,
              validator: (v) => (v ?? '').replaceAll(RegExp(r'\D'), '').length == 10 ? null : t.get('invalidPhone'),
            ),
            AppTextField(label: t.get('password'), controller: _password, obscureText: true, validator: (v) => (v == null || v.length < 8) ? t.get('required') : null),
            AppTextField(
              label: t.get('passwordConfirm'),
              controller: _confirm,
              obscureText: true,
              validator: (v) => v != _password.text ? t.get('required') : null,
            ),
            CheckboxListTile(
              value: _terms,
              onChanged: (v) => setState(() => _terms = v ?? false),
              title: Text(t.get('acceptTerms')),
              controlAffinity: ListTileControlAffinity.leading,
            ),
            PrimaryCta(label: t.get('register'), loading: _busy, onPressed: _submit),
          ],
        ),
      ),
    );
  }
}

class ForgotPasswordScreen extends ConsumerStatefulWidget {
  const ForgotPasswordScreen({super.key});

  @override
  ConsumerState<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends ConsumerState<ForgotPasswordScreen> {
  final _email = TextEditingController();
  bool _busy = false;

  @override
  void dispose() {
    _email.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final t = ref.watch(stringsProvider);
    return Scaffold(
      appBar: AppBar(title: Text(t.get('forgotPassword'))),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          AppTextField(label: t.get('email'), controller: _email, keyboardType: TextInputType.emailAddress),
          PrimaryCta(
            label: t.get('forgotPassword'),
            loading: _busy,
            onPressed: () async {
              if (_busy) return;
              setState(() => _busy = true);
              try {
                final message = await ref.read(authRepositoryProvider).forgotPassword(_email.text);
                if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message)));
              } on ApiException catch (e) {
                if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
              } finally {
                if (mounted) setState(() => _busy = false);
              }
            },
          ),
        ],
      ),
    );
  }
}

class VerifyEmailScreen extends ConsumerStatefulWidget {
  const VerifyEmailScreen({super.key, this.email});
  final String? email;

  @override
  ConsumerState<VerifyEmailScreen> createState() => _VerifyEmailScreenState();
}

class _VerifyEmailScreenState extends ConsumerState<VerifyEmailScreen> {
  late final TextEditingController _email;
  final _otp = TextEditingController();
  bool _busy = false;

  @override
  void initState() {
    super.initState();
    _email = TextEditingController(text: widget.email ?? '');
  }

  @override
  void dispose() {
    _email.dispose();
    _otp.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final t = ref.watch(stringsProvider);
    return Scaffold(
      appBar: AppBar(title: Text(t.get('verifyEmail'))),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          AppTextField(label: t.get('email'), controller: _email, keyboardType: TextInputType.emailAddress),
          AppTextField(label: t.get('otp'), controller: _otp, keyboardType: TextInputType.number),
          PrimaryCta(
            label: t.get('verifyEmail'),
            loading: _busy,
            onPressed: () async {
              setState(() => _busy = true);
              try {
                await ref.read(authControllerProvider.notifier).verifyEmail(email: _email.text, otp: _otp.text);
                if (context.mounted) context.go('/account');
              } on ApiException catch (e) {
                if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
              } finally {
                if (mounted) setState(() => _busy = false);
              }
            },
          ),
          TextButton(
            onPressed: () async {
              try {
                final message = await ref.read(authRepositoryProvider).resendVerification(_email.text);
                if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message)));
              } on ApiException catch (e) {
                if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
              }
            },
            child: const Text('Resend code'),
          ),
        ],
      ),
    );
  }
}
