import 'package:flutter/widgets.dart';
import 'package:permission_handler/permission_handler.dart';

class AppPermissions {
  const AppPermissions._();

  static Future<bool> requestLocation(BuildContext context) async {
    final status = await Permission.locationWhenInUse.request();
    return status.isGranted || status.isLimited;
  }

  static Future<bool> requestContacts(BuildContext context) async {
    final status = await Permission.contacts.request();
    return status.isGranted || status.isLimited;
  }
}
