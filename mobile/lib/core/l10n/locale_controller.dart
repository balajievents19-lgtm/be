import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'app_strings.dart';

final localeCodeProvider = StateNotifierProvider<LocaleController, String>((ref) {
  return LocaleController();
});

final stringsProvider = Provider<AppStrings>((ref) {
  return AppStrings(ref.watch(localeCodeProvider));
});

class LocaleController extends StateNotifier<String> {
  LocaleController() : super('en') {
    _load();
  }

  static const _key = 'bre_locale';

  Future<void> _load() async {
    final prefs = await SharedPreferences.getInstance();
    final value = prefs.getString(_key);
    if (value != null && AppStrings.supported.contains(value)) {
      state = value;
    }
  }

  Future<void> setLocale(String code) async {
    if (!AppStrings.supported.contains(code)) return;
    state = code;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_key, code);
  }
}
