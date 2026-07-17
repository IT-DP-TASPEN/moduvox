import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/api_service.dart';

class AuthProvider with ChangeNotifier {
  bool _isLoading = false;
  Map<String, dynamic>? _user;
  String? _token;

  bool get isLoading => _isLoading;
  Map<String, dynamic>? get user => _user;
  String? get token => _token;

  final ApiService _apiService = ApiService();

  Future<Map<String, dynamic>> login(String login, String password) async {
    _isLoading = true;
    notifyListeners();

    try {
      final response = await _apiService.login(login, password);
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        _token = data['access_token'];
        _user = data['user'];
        bool hasPin = data['has_pin'] ?? false;
        
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('auth_token', _token!);
        await prefs.setString('user_data', jsonEncode(_user));
        
        _isLoading = false;
        notifyListeners();
        return {'success': true, 'has_pin': hasPin};
      } else {
        final data = jsonDecode(response.body);
        _isLoading = false;
        notifyListeners();
        return {'success': false, 'message': data['message'] ?? 'Login ditolak server'};
      }
    } catch (e) {
      debugPrint("Login Error: $e");
      _isLoading = false;
      notifyListeners();
      return {'success': false, 'message': e.toString()};
    }
  }

  Future<Map<String, dynamic>> loginWithPin(String pin) async {
    if (_user == null) return {'success': false, 'message': 'User data missing'};
    
    _isLoading = true;
    notifyListeners();

    try {
      final loginIdentifier = _user!['email'] ?? _user!['employee_id'];
      final response = await _apiService.loginPin(loginIdentifier, pin);
      
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        _token = data['access_token'];
        _user = data['user'];
        
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('auth_token', _token!);
        await prefs.setString('user_data', jsonEncode(_user));
        
        _isLoading = false;
        notifyListeners();
        return {'success': true};
      } else {
        final data = jsonDecode(response.body);
        _isLoading = false;
        notifyListeners();
        return {'success': false, 'message': data['message'] ?? 'PIN salah'};
      }
    } catch (e) {
      debugPrint("Login PIN Error: $e");
      _isLoading = false;
      notifyListeners();
      return {'success': false, 'message': e.toString()};
    }
  }

  Future<bool> setPin(String pin) async {
    _isLoading = true;
    notifyListeners();

    try {
      final response = await _apiService.setPin(pin);
      if (response.statusCode == 200) {
        _isLoading = false;
        notifyListeners();
        return true;
      }
    } catch (e) {
      debugPrint("Set PIN Error: $e");
    }

    _isLoading = false;
    notifyListeners();
    return false;
  }

  Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    await prefs.remove('user_data');
    _token = null;
    _user = null;
    notifyListeners();
  }

  Future<void> tryAutoLogin() async {
    final prefs = await SharedPreferences.getInstance();
    
    // Always try to load user data for Quick Login UI
    final userData = prefs.getString('user_data');
    if (userData != null) {
      _user = jsonDecode(userData);
    }

    // Load token if exists
    if (prefs.containsKey('auth_token')) {
      _token = prefs.getString('auth_token');
    }
    
    notifyListeners();
  }

  Future<void> loadUser() async {
    try {
      final response = await _apiService.getUser();
      if (response.statusCode == 200) {
        _user = jsonDecode(response.body);
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('user_data', jsonEncode(_user));
        notifyListeners();
      }
    } catch (e) {
      debugPrint("Load User Error: $e");
    }
  }
}
