import 'package:permission_handler/permission_handler.dart';

class PermissionService {
  static Future<void> requestAllPermissions() async {
    // Request basic permissions
    Map<Permission, PermissionStatus> statuses = await [
      Permission.location,
      Permission.camera,
      Permission.notification,
    ].request();

    // Check if location is granted, then request background location if needed
    if (statuses[Permission.location]!.isGranted) {
      await Permission.locationAlways.request();
    }

    // For Android 13+ exact alarms (needed for reminders)
    if (await Permission.scheduleExactAlarm.isDenied) {
      await Permission.scheduleExactAlarm.request();
    }

    // Ignore battery optimizations (for background execution stability)
    if (await Permission.ignoreBatteryOptimizations.isDenied) {
      await Permission.ignoreBatteryOptimizations.request();
    }
  }

  static Future<bool> checkMandatoryPermissions() async {
    bool location = await Permission.location.isGranted;
    bool camera = await Permission.camera.isGranted;
    return location && camera;
  }
}
