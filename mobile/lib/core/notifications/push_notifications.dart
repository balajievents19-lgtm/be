/// Flutter-side push foundation. No Laravel device-token endpoint exists.
/// Do not request notification permission until a backend token API exists.
class PushNotifications {
  const PushNotifications._();

  static Future<void> bootstrap() async {}
}
