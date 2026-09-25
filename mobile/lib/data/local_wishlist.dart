import 'package:shared_preferences/shared_preferences.dart';

/// Local-only favourites. No customer wishlist API exists on Laravel.
class LocalWishlist {
  LocalWishlist._();
  static const key = 'bre_local_wishlist_slugs';

  static Future<Set<String>> load() async {
    final prefs = await SharedPreferences.getInstance();
    return (prefs.getStringList(key) ?? const []).toSet();
  }

  static Future<Set<String>> toggle(String slug) async {
    final prefs = await SharedPreferences.getInstance();
    final next = (prefs.getStringList(key) ?? const []).toSet();
    if (next.contains(slug)) {
      next.remove(slug);
    } else {
      next.add(slug);
    }
    await prefs.setStringList(key, next.toList());
    return next;
  }
}
