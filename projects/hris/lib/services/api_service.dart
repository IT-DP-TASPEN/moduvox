import 'dart:convert';
import 'dart:io';
import 'dart:async';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../config/app_config.dart';

class ApiService {
  static String get baseUrl => AppConfig.apiBaseUrl;
  static final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();

  static const Duration _timeout = Duration(seconds: 30);

  Future<String?> _getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('auth_token');
  }

  Future<Map<String, String>> _headers() async {
    final token = await _getToken();
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  // Handle unauthorized (401) globally
  void _handleUnauthorized(http.Response response, String endpoint) async {
    if (response.statusCode == 401 && endpoint != '/verify-pin') {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove('auth_token');
      navigatorKey.currentState?.pushNamedAndRemoveUntil('/login', (route) => false);
    }
  }

  // Handle global network exceptions (timeout, offline)
  http.Response _handleNetworkError(dynamic e) {
    String message = 'Tidak dapat terhubung ke server. Silakan periksa koneksi internet Anda.';
    String title = 'Koneksi Gagal';
    
    if (e is TimeoutException) {
      message = 'Koneksi terputus karena server terlalu lama merespon. Silakan coba lagi.';
      title = 'Request Timeout';
    } else if (e is SocketException) {
      message = 'Gagal terhubung ke server. Pastikan Anda memiliki koneksi internet yang stabil.';
    }

    // Tampilkan halaman error custom
    // Gunakan Future.microtask agar tidak memblokir render tree yang sedang build
    Future.microtask(() {
      navigatorKey.currentState?.pushNamed('/error', arguments: {
        'title': title,
        'message': message,
      });
    });

    // Return dummy response agar app tidak crash karena null
    return http.Response(jsonEncode({'message': message, 'error': true}), 503);
  }

  Future<http.Response> login(String login, String password) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/login'),
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: jsonEncode({'login': login, 'password': password}),
      ).timeout(_timeout);
      return response;
    } catch (e) {
      return _handleNetworkError(e);
    }
  }

  Future<http.Response> loginPin(String login, String pin) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/login-pin'),
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: jsonEncode({'login': login, 'pin': pin}),
      ).timeout(_timeout);
      return response;
    } catch (e) {
      return _handleNetworkError(e);
    }
  }

  Future<http.Response> post(String endpoint, Map<String, dynamic> body) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl$endpoint'),
        headers: await _headers(),
        body: jsonEncode(body),
      ).timeout(_timeout);
      _handleUnauthorized(response, endpoint);
      return response;
    } catch (e) {
      return _handleNetworkError(e);
    }
  }

  Future<http.Response> get(String endpoint) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl$endpoint'),
        headers: await _headers(),
      ).timeout(_timeout);
      _handleUnauthorized(response, endpoint);
      return response;
    } catch (e) {
      return _handleNetworkError(e);
    }
  }

  Future<http.Response> postMultipart(
    String endpoint, {
    required Map<String, String> fields,
    File? photo,
    String photoField = 'photo',
  }) async {
    try {
      final token = await _getToken();
      final request = http.MultipartRequest('POST', Uri.parse('$baseUrl$endpoint'));

      request.headers.addAll({
        if (token != null) 'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      });

      request.fields.addAll(fields);

      if (photo != null) {
        request.files.add(await http.MultipartFile.fromPath(photoField, photo.path));
      }

      final streamedResponse = await request.send().timeout(_timeout);
      final response = await http.Response.fromStream(streamedResponse);
      _handleUnauthorized(response, endpoint);
      return response;
    } catch (e) {
      return _handleNetworkError(e);
    }
  }

  // Attendance
  Future<http.Response> submitAttendance({
    required String type,
    required double latitude,
    required double longitude,
    File? photo,
  }) async {
    return await postMultipart(
      '/attendance',
      fields: {
        'type': type,
        'latitude': latitude.toString(),
        'longitude': longitude.toString(),
      },
      photo: photo,
    );
  }

  Future<http.Response> getHistory() async => await get('/attendance/history');
  Future<http.Response> getNotifications() async => await get('/notifications');
  Future<http.Response> markNotificationAsRead(String id) async => await post('/notifications/$id/read', {});
  Future<http.Response> uploadPhotoProfile(File photo) async => await postMultipart('/user/update-photo', fields: {}, photo: photo);
  Future<http.Response> getUser() async => await get('/user');
  Future<http.Response> setPin(String pin) async => await post('/set-pin', {'pin': pin});
  Future<http.Response> verifyPin(String pin) async => await post('/verify-pin', {'pin': pin});

  // Leave / Permit / Overtime / Outside Duty
  Future<http.Response> getLeaveRequests() async => await get('/leave-requests');
  Future<http.Response> getPendingLeaveApprovals() async => await get('/leave-requests/pending');
  Future<http.Response> approveLeaveRequest(int id) async => await post('/leave-requests/$id/approve', {});
  Future<http.Response> rejectLeaveRequest(int id) async => await post('/leave-requests/$id/reject', {});

  Future<http.Response> getPermitRequests() async => await get('/permit-requests');
  Future<http.Response> getPendingPermitApprovals() async => await get('/permit-requests/pending');
  Future<http.Response> approvePermitRequest(int id) async => await post('/permit-requests/$id/approve', {});
  Future<http.Response> rejectPermitRequest(int id) async => await post('/permit-requests/$id/reject', {});

  Future<http.Response> getOvertimeRequests() async => await get('/overtime-requests');
  Future<http.Response> getPendingOvertimeApprovals() async => await get('/overtime-requests/pending');
  Future<http.Response> approveOvertimeRequest(int id) async => await post('/overtime-requests/$id/approve', {});
  Future<http.Response> rejectOvertimeRequest(int id) async => await post('/overtime-requests/$id/reject', {});

  Future<http.Response> getOutsideDutyRequests() async => await get('/outside-duty-requests');
  Future<http.Response> getPendingOutsideDutyApprovals() async => await get('/outside-duty-requests/pending');
  Future<http.Response> approveOutsideDutyRequest(int id) async => await post('/outside-duty-requests/$id/approve', {});
  Future<http.Response> rejectOutsideDutyRequest(int id) async => await post('/outside-duty-requests/$id/reject', {});

  Future<http.Response> getAttendanceRecap({
    required String startDate,
    required String endDate,
    int? userId,
  }) async {
    String url = '/attendance/recap?start_date=$startDate&end_date=$endDate';
    if (userId != null) url += '&user_id=$userId';
    return await get(url);
  }

  Future<http.Response> getUsers() async => await get('/users');

  // Salary & KPI & SSO
  Future<http.Response> getSalaries({int? month, int? year}) async {
    String url = '/salaries?';
    if (month != null) url += 'month=$month&';
    if (year != null) url += 'year=$year&';
    return await get(url);
  }
  Future<http.Response> getSalaryDetail(int id) async => await get('/salaries/$id');
  Future<http.Response> getKpis() async => await get('/kpis');
  Future<http.Response> saveKpi(Map<String, dynamic> data) async => await post('/kpis', data);
  Future<http.Response> getSsoLink(String path) async => await get('/auth/sso-link?path=' + Uri.encodeComponent(path));
  Future<http.Response> getBanners() async => await get('/banners');
}
