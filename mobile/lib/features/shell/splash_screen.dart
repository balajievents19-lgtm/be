import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/config/app_config.dart';
import '../../core/theme/app_colors.dart';
import '../../data/auth_controller.dart';

class SplashScreen extends ConsumerStatefulWidget {
  const SplashScreen({super.key});

  @override
  ConsumerState<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends ConsumerState<SplashScreen> {
  @override
  void initState() {
    super.initState();
    _boot();
  }

  Future<void> _boot() async {
    final started = DateTime.now();
    await ref.read(authControllerProvider.notifier).restore();
    final elapsed = DateTime.now().difference(started);
    const minHold = Duration(milliseconds: 400);
    if (elapsed < minHold) {
      await Future<void>.delayed(minHold - elapsed);
    }
    if (!mounted) return;
    final signedIn = ref.read(authControllerProvider).signedIn;
    context.go(signedIn ? '/account' : '/home');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.cream,
      body: SafeArea(
        child: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Semantics(
                label: 'Balaji Royal Events logo',
                image: true,
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(20),
                  child: Image.asset('assets/brand/logo.png', width: 120, height: 120, fit: BoxFit.contain),
                ),
              ),
              const SizedBox(height: 24),
              Text(AppConfig.brandName, style: Theme.of(context).textTheme.headlineMedium, textAlign: TextAlign.center),
              const SizedBox(height: 8),
              const Text('Weddings & celebrations in Rajasthan', style: TextStyle(color: AppColors.muted)),
              const SizedBox(height: 32),
              const SizedBox(width: 28, height: 28, child: CircularProgressIndicator(strokeWidth: 2.4, color: AppColors.brand)),
            ],
          ),
        ),
      ),
    );
  }
}
