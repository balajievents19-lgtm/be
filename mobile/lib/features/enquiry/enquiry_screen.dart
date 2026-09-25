import 'package:flutter/material.dart';
import 'package:flutter_contacts/flutter_contacts.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:geolocator/geolocator.dart';
import 'package:go_router/go_router.dart';
import 'package:permission_handler/permission_handler.dart';

import '../../core/l10n/locale_controller.dart';
import '../../core/permissions/app_permissions.dart';
import '../../data/auth_controller.dart';
import '../../data/models/models.dart';
import '../../data/providers.dart';
import '../../widgets/common.dart';
import 'enquiry_submit.dart';

class EnquiryScreen extends ConsumerStatefulWidget {
  const EnquiryScreen({super.key});

  @override
  ConsumerState<EnquiryScreen> createState() => _EnquiryScreenState();
}

class _EnquiryScreenState extends ConsumerState<EnquiryScreen> {
  final _form = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _phone = TextEditingController();
  final _location = TextEditingController();
  final _budget = TextEditingController();
  final _notes = TextEditingController();
  DateTime? _date;
  EventTypeItem? _type;
  bool _submitting = false;

  @override
  void dispose() {
    _name.dispose();
    _phone.dispose();
    _location.dispose();
    _budget.dispose();
    _notes.dispose();
    super.dispose();
  }

  Future<void> _pickContact() async {
    final t = ref.read(stringsProvider);
    final allowed = await AppPermissions.requestContacts(context);
    if (!allowed) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(t.get('permissionDenied')),
          action: SnackBarAction(label: t.get('openSettings'), onPressed: openAppSettings),
        ),
      );
      return;
    }
    final contact = await FlutterContacts.openExternalPick();
    if (contact == null) return;
    final phones = contact.phones;
    if (phones.isEmpty) return;
    final digits = phones.first.number.replaceAll(RegExp(r'\D'), '');
    _phone.text = digits.length > 10 ? digits.substring(digits.length - 10) : digits;
  }

  Future<void> _useLocation() async {
    final t = ref.read(stringsProvider);
    final allowed = await AppPermissions.requestLocation(context);
    if (!allowed) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(t.get('permissionDenied')),
          action: SnackBarAction(label: t.get('openSettings'), onPressed: openAppSettings),
        ),
      );
      return;
    }
    try {
      final pos = await Geolocator.getCurrentPosition();
      final label = await ref.read(contentRepositoryProvider).reverseGeocode(lat: pos.latitude, lng: pos.longitude);
      if (label != null && label.isNotEmpty) {
        _location.text = label;
      }
    } catch (_) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(t.get('error'))));
    }
  }

  Future<void> _submit() async {
    if (_submitting) return;
    if (_form.currentState?.validate() != true || _type == null || _date == null) {
      if (_type == null || _date == null) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(ref.read(stringsProvider).get('required'))));
      }
      return;
    }
    setState(() => _submitting = true);
    await submitPlanEnquiry(
      context: context,
      ref: ref,
      name: _name.text,
      phone: _phone.text,
      type: _type!,
      location: _location.text,
      date: _date!,
      budget: _budget.text,
      notes: _notes.text,
    );
    if (mounted) setState(() => _submitting = false);
  }

  @override
  Widget build(BuildContext context) {
    final t = ref.watch(stringsProvider);
    final auth = ref.watch(authControllerProvider);
    final homeTypes = ref.watch(homeProvider).valueOrNull?.eventTypes ?? [];
    final apiTypes = ref.watch(eventTypesProvider).valueOrNull ?? [];
    final types = apiTypes.isNotEmpty ? apiTypes : homeTypes;

    return Scaffold(
      appBar: AppBar(title: Text(t.get('planYourEvent'))),
      body: Form(
        key: _form,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            if (!auth.signedIn) ...[
              Text(t.get('loginToEnquire')),
              const SizedBox(height: 8),
              OutlinedButton(onPressed: () => context.push('/login'), child: Text(t.get('login'))),
            ] else if (!auth.canSubmitEnquiry) ...[
              const SizedBox(height: 12),
              Text(t.get('verifyToEnquire')),
            ],
            const SizedBox(height: 16),
            AppTextField(label: t.get('yourName'), controller: _name, validator: (v) => (v == null || v.trim().isEmpty) ? t.get('required') : null),
            AppTextField(
              label: t.get('phoneNumber'),
              controller: _phone,
              keyboardType: TextInputType.phone,
              suffix: IconButton(icon: const Icon(Icons.contacts_outlined), onPressed: _pickContact, tooltip: t.get('pickContact')),
              validator: (v) {
                final digits = (v ?? '').replaceAll(RegExp(r'\D'), '');
                if (digits.length < 10) return t.get('invalidPhone');
                return null;
              },
            ),
            DropdownMenu<EventTypeItem>(
              label: Text(t.get('eventType')),
              expandedInsets: EdgeInsets.zero,
              dropdownMenuEntries: types.map((e) => DropdownMenuEntry(value: e, label: e.name)).toList(),
              onSelected: (v) => setState(() => _type = v),
            ),
            const SizedBox(height: 14),
            AppTextField(
              label: t.get('eventLocation'),
              controller: _location,
              suffix: IconButton(icon: const Icon(Icons.my_location), onPressed: _useLocation, tooltip: t.get('useLocation')),
              validator: (v) => (v == null || v.trim().isEmpty) ? t.get('required') : null,
            ),
            AppTextField(
              label: t.get('selectDate'),
              readOnly: true,
              controller: TextEditingController(
                text: _date == null ? '' : '${_date!.year}-${_date!.month.toString().padLeft(2, '0')}-${_date!.day.toString().padLeft(2, '0')}',
              ),
              validator: (_) => _date == null ? t.get('required') : null,
              onTap: () async {
                final picked = await showDatePicker(
                  context: context,
                  firstDate: DateTime.now(),
                  lastDate: DateTime.now().add(const Duration(days: 365 * 3)),
                  initialDate: _date ?? DateTime.now(),
                );
                if (picked != null) setState(() => _date = picked);
              },
            ),
            AppTextField(label: t.get('budget'), controller: _budget),
            AppTextField(label: t.get('additionalRequirements'), controller: _notes, maxLines: 4),
            PrimaryCta(label: _submitting ? t.get('submitting') : t.get('submitEnquiry'), loading: _submitting, onPressed: _submit),
          ],
        ),
      ),
    );
  }
}
