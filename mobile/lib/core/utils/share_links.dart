import '../config/app_config.dart';

class ShareLinks {
  const ShareLinks._();

  static String service(String slug) => '${AppConfig.siteBaseUrl}/services/$slug';
  static String gallery(String slug) => '${AppConfig.siteBaseUrl}/gallery/$slug';
  static String packages() => '${AppConfig.siteBaseUrl}/packages';
  static String blog(String slug) => '${AppConfig.siteBaseUrl}/blog/$slug';
  static String enquiry() => '${AppConfig.siteBaseUrl}/contact';
  static String app() => AppConfig.siteBaseUrl;
}
