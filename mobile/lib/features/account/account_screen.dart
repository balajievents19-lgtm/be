import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/l10n/locale_controller.dart';
import '../../core/network/api_exception.dart';
import '../../data/auth_controller.dart';
import '../../data/local_wishlist.dart';
import '../../data/providers.dart';
import '../../widgets/common.dart';

class AccountScreen extends ConsumerWidget {
  const AccountScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final t = ref.watch(stringsProvider);
    final auth = ref.watch(authControllerProvider);
    final customer = auth.customer;

    return Scaffold(
      appBar: AppBar(title: Text(t.get('navAccount'))),
      body: ListView(
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: customer == null
                ? Text(t.get('guestHint'))
                : Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(customer.name ?? '', style: Theme.of(context).textTheme.titleLarge),
                      if (customer.email != null) Text(customer.email!),
                      const SizedBox(height: 8),
                      Text(customer.emailVerified ? t.get('verified') : t.get('unverified')),
                    ],
                  ),
          ),
          if (customer == null) ...[
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: FilledButton(onPressed: () => context.push('/login'), child: Text(t.get('login'))),
            ),
            const SizedBox(height: 8),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: OutlinedButton(onPressed: () => context.push('/register'), child: Text(t.get('register'))),
            ),
          ] else if (customer.needsEmailVerification || !customer.emailVerified)
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: OutlinedButton(
                onPressed: () => context.push('/verify-email', extra: customer.email),
                child: Text(t.get('verifyEmail')),
              ),
            ),
          const SizedBox(height: 16),
          ListTile(
            leading: const Icon(Icons.person_outline),
            title: Text(t.get('profile')),
            trailing: const Icon(Icons.chevron_right),
            onTap: () => context.push(customer == null ? '/login' : '/profile'),
          ),
          ListTile(
            leading: const Icon(Icons.mail_outline),
            title: Text(t.get('myEnquiries')),
            trailing: const Icon(Icons.chevron_right),
            onTap: () => context.push('/my-enquiries'),
          ),
          ListTile(
            leading: const Icon(Icons.event_available_outlined),
            title: Text(t.get('myBookings')),
            trailing: const Icon(Icons.chevron_right),
            onTap: () => context.push('/my-bookings'),
          ),
          ListTile(
            leading: const Icon(Icons.favorite_border),
            title: Text(t.get('wishlist')),
            trailing: const Icon(Icons.chevron_right),
            onTap: () => context.push('/wishlist'),
          ),
          ListTile(
            leading: const Icon(Icons.notifications_none),
            title: Text(t.get('notifications')),
            trailing: const Icon(Icons.chevron_right),
            onTap: () => context.push('/notifications'),
          ),
          ListTile(
            leading: const Icon(Icons.star_outline),
            title: Text(t.get('myReviews')),
            trailing: const Icon(Icons.chevron_right),
            onTap: () => context.push('/reviews'),
          ),
          ListTile(
            leading: const Icon(Icons.settings_outlined),
            title: Text(t.get('settings')),
            trailing: const Icon(Icons.chevron_right),
            onTap: () => context.push('/settings'),
          ),
          ListTile(
            leading: const Icon(Icons.help_outline),
            title: Text(t.get('help')),
            trailing: const Icon(Icons.chevron_right),
            onTap: () => context.push('/contact'),
          ),
          if (customer != null)
            ListTile(
              leading: const Icon(Icons.logout),
              title: Text(t.get('logout')),
              onTap: () async {
                final ok = await showDialog<bool>(
                  context: context,
                  builder: (ctx) => AlertDialog(
                    title: Text(t.get('logout')),
                    actions: [
                      TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancel')),
                      FilledButton(onPressed: () => Navigator.pop(ctx, true), child: Text(t.get('logout'))),
                    ],
                  ),
                );
                if (ok == true) {
                  await ref.read(authControllerProvider.notifier).logout();
                }
              },
            ),
        ],
      ),
    );
  }
}

class SettingsScreen extends ConsumerWidget {
  const SettingsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final t = ref.watch(stringsProvider);
    final locale = ref.watch(localeCodeProvider);
    return Scaffold(
      appBar: AppBar(title: Text(t.get('settings'))),
      body: ListView(
        children: [
          ListTile(title: Text(t.get('language'))),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: SegmentedButton<String>(
              segments: [
                ButtonSegment(value: 'en', label: Text(t.get('english'))),
                ButtonSegment(value: 'hi', label: Text(t.get('hindi'))),
              ],
              selected: {locale},
              onSelectionChanged: (selection) {
                if (selection.isNotEmpty) {
                  ref.read(localeCodeProvider.notifier).setLocale(selection.first);
                }
              },
            ),
          ),
        ],
      ),
    );
  }
}

class ProfileScreen extends ConsumerStatefulWidget {
  const ProfileScreen({super.key});

  @override
  ConsumerState<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends ConsumerState<ProfileScreen> {
  final _phone = TextEditingController();
  final _email = TextEditingController();
  final _current = TextEditingController();
  final _password = TextEditingController();
  final _confirm = TextEditingController();
  bool _busy = false;

  @override
  void dispose() {
    _phone.dispose();
    _email.dispose();
    _current.dispose();
    _password.dispose();
    _confirm.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final t = ref.watch(stringsProvider);
    final customer = ref.watch(authControllerProvider).customer;
    if (customer == null) {
      return Scaffold(appBar: AppBar(), body: EmptyState(message: t.get('guestHint')));
    }
    return Scaffold(
      appBar: AppBar(title: Text(t.get('profile'))),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Text(customer.name ?? '', style: Theme.of(context).textTheme.titleLarge),
          Text(customer.email ?? ''),
          if (customer.phoneMasked != null || customer.phone != null) Text(customer.phoneMasked ?? customer.phone!),
          const SizedBox(height: 16),
          Text(customer.emailVerified ? t.get('verified') : t.get('unverified')),
          const Divider(height: 32),
          AppTextField(label: t.get('currentPassword'), controller: _current, obscureText: true),
          AppTextField(label: t.get('phoneNumber'), controller: _phone, keyboardType: TextInputType.phone),
          PrimaryCta(
            label: 'Update mobile',
            loading: _busy,
            onPressed: () async {
              setState(() => _busy = true);
              try {
                final message = await ref.read(authRepositoryProvider).changeMobile(
                      currentPassword: _current.text,
                      phone: _phone.text,
                    );
                await ref.read(authControllerProvider.notifier).refreshMe();
                if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message)));
              } on ApiException catch (e) {
                if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
              } finally {
                if (mounted) setState(() => _busy = false);
              }
            },
          ),
          const SizedBox(height: 16),
          AppTextField(label: t.get('email'), controller: _email, keyboardType: TextInputType.emailAddress),
          PrimaryCta(
            label: 'Update email',
            loading: _busy,
            onPressed: () async {
              setState(() => _busy = true);
              try {
                final message = await ref.read(authRepositoryProvider).changeEmail(
                      currentPassword: _current.text,
                      email: _email.text,
                    );
                await ref.read(authControllerProvider.notifier).refreshMe();
                if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message)));
              } on ApiException catch (e) {
                if (context.mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
              } finally {
                if (mounted) setState(() => _busy = false);
              }
            },
          ),
          const SizedBox(height: 16),
          AppTextField(label: t.get('password'), controller: _password, obscureText: true),
          AppTextField(label: t.get('passwordConfirm'), controller: _confirm, obscureText: true),
          PrimaryCta(
            label: 'Update password',
            loading: _busy,
            onPressed: () async {
              setState(() => _busy = true);
              try {
                final message = await ref.read(authRepositoryProvider).changePassword(
                      currentPassword: _current.text,
                      password: _password.text,
                      passwordConfirmation: _confirm.text,
                    );
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

class MyEnquiriesScreen extends ConsumerWidget {
  const MyEnquiriesScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final t = ref.watch(stringsProvider);
    return Scaffold(
      appBar: AppBar(title: Text(t.get('myEnquiries'))),
      body: EmptyState(message: t.get('noEnquiriesApi')),
    );
  }
}

class MyBookingsScreen extends ConsumerWidget {
  const MyBookingsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final t = ref.watch(stringsProvider);
    return Scaffold(
      appBar: AppBar(title: Text(t.get('myBookings'))),
      body: EmptyState(message: t.get('noBookings')),
    );
  }
}

class WishlistScreen extends ConsumerStatefulWidget {
  const WishlistScreen({super.key});

  @override
  ConsumerState<WishlistScreen> createState() => _WishlistScreenState();
}

class _WishlistScreenState extends ConsumerState<WishlistScreen> {
  Set<String> _slugs = {};

  @override
  void initState() {
    super.initState();
    LocalWishlist.load().then((value) {
      if (mounted) setState(() => _slugs = value);
    });
  }

  @override
  Widget build(BuildContext context) {
    final t = ref.watch(stringsProvider);
    final gallery = ref.watch(galleryProvider(null)).valueOrNull ?? [];
    final items = gallery.where((g) => _slugs.contains(g.slug)).toList();
    return Scaffold(
      appBar: AppBar(title: Text(t.get('wishlist'))),
      body: ListView(
        children: [
          Padding(padding: const EdgeInsets.all(16), child: Text(t.get('localWishlistHint'))),
          if (items.isEmpty) EmptyState(message: t.get('empty')),
          ...items.map(
            (item) => ListTile(
              title: Text(item.title ?? item.slug),
              onTap: () => context.push('/gallery/${item.slug}'),
            ),
          ),
        ],
      ),
    );
  }
}

class NotificationsScreen extends ConsumerWidget {
  const NotificationsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final t = ref.watch(stringsProvider);
    return Scaffold(
      appBar: AppBar(title: Text(t.get('notifications'))),
      body: EmptyState(message: t.get('noNotificationsApi')),
    );
  }
}
