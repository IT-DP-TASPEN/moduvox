import 'package:absensi/screens/login_page.dart';
import 'package:absensi/screens/home_page.dart';
import 'package:absensi/providers/auth_provider.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'theme/app_theme.dart';
import 'services/api_service.dart';
import 'services/notification_service.dart';
import 'providers/settings_provider.dart';
import 'screens/error_page.dart';
import 'services/permission_service.dart';
import 'package:intl/date_symbol_data_local.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  await initializeDateFormatting('id', null);
  
  // Request all necessary permissions
  await PermissionService.requestAllPermissions();
  
  // Initialize notification service
  await NotificationService().init();

  final authProvider = AuthProvider();
  await authProvider.tryAutoLogin();

  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider.value(value: authProvider),
        ChangeNotifierProvider(create: (_) => SettingsProvider()),
      ],
      child: const MyApp(),
    ),
  );
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer<AuthProvider>(
      builder: (context, auth, _) {
        return MaterialApp(
          debugShowCheckedModeBanner: false,
          title: 'Absensi Bank DP Taspen',
          theme: AppTheme.lightTheme,
          navigatorKey: ApiService.navigatorKey,
          home: const LoginPage(),
          routes: {
            '/login': (context) => const LoginPage(),
            '/error': (context) => const ErrorPage(),
          },
        );
      },
    );
  }
}
