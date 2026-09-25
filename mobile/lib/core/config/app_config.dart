/// Runtime configuration. Secrets never live here.
///
/// Override at build time:
/// `flutter run --dart-define=API_BASE_URL=https://www.balajiroyalevents.com/api`
/// `flutter run --dart-define=SITE_BASE_URL=https://www.balajiroyalevents.com`
class AppConfig {
  const AppConfig._();

  static const String packageId = 'com.balajiroyalevents.app';
  static const String brandName = 'Balaji Royal Events';
  static const String siteHost = 'www.balajiroyalevents.com';

  static const String apiBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'https://www.balajiroyalevents.com/api',
  );

  static const String siteBaseUrl = String.fromEnvironment(
    'SITE_BASE_URL',
    defaultValue: 'https://www.balajiroyalevents.com',
  );

  static const Duration connectTimeout = Duration(seconds: 20);
  static const Duration receiveTimeout = Duration(seconds: 30);
}
