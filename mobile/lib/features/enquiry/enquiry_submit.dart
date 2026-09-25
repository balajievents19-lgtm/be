import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/l10n/locale_controller.dart';
import '../../core/network/api_exception.dart';
import '../../data/auth_controller.dart';
import '../../data/models/models.dart';
import '../../data/providers.dart';

class SubmissionLock {
  bool _busy = false;
  bool get isBusy => _busy;

  Future<T?> run<T>(Future<T> Function() action) async {
    if (_busy) return null;
    _busy = true;
    try {
      return await action();
    } finally {
      _busy = false;
    }
  }
}

Future<void> submitPlanEnquiry({
  required BuildContext context,
  required WidgetRef ref,
  required String name,
  required String phone,
  required EventTypeItem type,
  required String location,
  required DateTime date,
  String? budget,
  String? notes,
}) async {
  final t = ref.read(stringsProvider);
  final auth = ref.read(authControllerProvider);
  if (!auth.signedIn) {
    if (!context.mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(t.get('loginToEnquire'))));
    context.push('/login');
    return;
  }
  if (!auth.canSubmitEnquiry) {
    if (!context.mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(t.get('verifyToEnquire'))));
    final email = auth.customer?.email;
    if (email != null && email.isNotEmpty) {
      context.push('/verify-email', extra: email);
    }
    return;
  }
  final customer = auth.customer!;
  final dateStr =
      '${date.year}-${date.month.toString().padLeft(2, '0')}-${date.day.toString().padLeft(2, '0')}';
  try {
    final result = await ref.read(contentRepositoryProvider).submitEnquiry(
          name: customer.name ?? name,
          mobile: customer.phone ?? phone,
          email: customer.email,
          eventType: type,
          eventLocation: location,
          eventDate: dateStr,
          budget: budget,
          notes: notes,
        );
    if (!context.mounted) return;
    final extra = result.id != null ? ' Reference: ${result.id}.' : '';
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('${t.get('enquirySuccess')}$extra')),
    );
  } on ApiException catch (e) {
    if (!context.mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
  }
}
