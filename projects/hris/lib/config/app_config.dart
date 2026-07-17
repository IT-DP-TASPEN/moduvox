class AppConfig {
  /// Base URL can be set via --dart-define=API_BASE_URL=...
  /// If not provided, it will fallback to the default production URL.
  static const String _baseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'https://hris.bankdptaspen.co.id/api',
  );

  /// Toggle this to true if you want to use the Local Development URL
  /// regardless of the dart-define. This is useful for quick local testing.
  static const bool useLocalDev = false;

  static String get apiBaseUrl {
    if (useLocalDev) {
      // Update this IP to your local testing machine IP
      return 'http://192.168.1.10:8000/api'; 
    }
    return _baseUrl;
  }
}
