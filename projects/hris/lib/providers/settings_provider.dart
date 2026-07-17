import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/notification_service.dart';

class SettingsProvider with ChangeNotifier {
  bool _saveToGallery = false;
  bool _smartWarning = true;
  bool _disableEmailNotif = false;
  String _language = "Bahasa Indonesia";
  bool _isReminderActive = false;
  TimeOfDay _reminderInTime = const TimeOfDay(hour: 7, minute: 45);
  TimeOfDay _reminderOutTime = const TimeOfDay(hour: 16, minute: 30);

  bool get saveToGallery => _saveToGallery;
  bool get smartWarning => _smartWarning;
  bool get disableEmailNotif => _disableEmailNotif;
  String get language => _language;
  bool get isReminderActive => _isReminderActive;
  TimeOfDay get reminderInTime => _reminderInTime;
  TimeOfDay get reminderOutTime => _reminderOutTime;

  SettingsProvider() {
    _loadSettings();
  }

  Future<void> _loadSettings() async {
    final prefs = await SharedPreferences.getInstance();
    _saveToGallery = prefs.getBool('save_to_gallery') ?? false;
    _smartWarning = prefs.getBool('smart_warning') ?? true;
    _disableEmailNotif = prefs.getBool('disable_email_notif') ?? false;
    _language = prefs.getString('language') ?? "Bahasa Indonesia";
    _isReminderActive = prefs.getBool('is_reminder_active') ?? false;
    
    int inH = prefs.getInt('reminder_in_h') ?? 7;
    int inM = prefs.getInt('reminder_in_m') ?? 45;
    _reminderInTime = TimeOfDay(hour: inH, minute: inM);

    int outH = prefs.getInt('reminder_out_h') ?? 16;
    int outM = prefs.getInt('reminder_out_m') ?? 30;
    _reminderOutTime = TimeOfDay(hour: outH, minute: outM);

    notifyListeners();
  }

  Future<void> setSaveToGallery(bool value) async {
    _saveToGallery = value;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('save_to_gallery', value);
    notifyListeners();
  }

  Future<void> setSmartWarning(bool value) async {
    _smartWarning = value;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('smart_warning', value);
    notifyListeners();
  }

  Future<void> setDisableEmailNotif(bool value) async {
    _disableEmailNotif = value;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('disable_email_notif', value);
    notifyListeners();
  }

  Future<void> setLanguage(String value) async {
    _language = value;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('language', value);
    notifyListeners();
  }

  Future<void> setReminderInTime(TimeOfDay time) async {
    _reminderInTime = time;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setInt('reminder_in_h', time.hour);
    await prefs.setInt('reminder_in_m', time.minute);
    if (_isReminderActive) await _scheduleReminders();
    notifyListeners();
  }

  Future<void> setReminderOutTime(TimeOfDay time) async {
    _reminderOutTime = time;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setInt('reminder_out_h', time.hour);
    await prefs.setInt('reminder_out_m', time.minute);
    if (_isReminderActive) await _scheduleReminders();
    notifyListeners();
  }

  Future<void> toggleReminder(bool value) async {
    _isReminderActive = value;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('is_reminder_active', value);
    
    if (value) {
      await _scheduleReminders();
    } else {
      await NotificationService().cancelAll();
    }
    
    notifyListeners();
  }

  Future<void> _scheduleReminders() async {
    await NotificationService().cancelAll();
    await NotificationService().scheduleDailyReminder(
      id: 1,
      title: "Waktunya Absen Masuk",
      body: "Jangan lupa untuk melakukan absen masuk hari ini.",
      hour: _reminderInTime.hour,
      minute: _reminderInTime.minute,
    );
    await NotificationService().scheduleDailyReminder(
      id: 2,
      title: "Waktunya Absen Keluar",
      body: "Sudah waktunya pulang! Jangan lupa absen keluar ya.",
      hour: _reminderOutTime.hour,
      minute: _reminderOutTime.minute,
    );
  }
}
