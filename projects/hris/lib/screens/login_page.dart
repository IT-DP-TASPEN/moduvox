import 'package:absensi/providers/auth_provider.dart';
import 'package:absensi/screens/setup_pin_page.dart';
import 'package:provider/provider.dart';
import 'package:flutter/material.dart';
import 'home_page.dart';
import 'pin_page.dart';

class LoginPage extends StatefulWidget {
  const LoginPage({super.key});

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final TextEditingController _loginController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  bool _forcePasswordLogin = false;

  void _handleLogin() async {
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    final result = await authProvider.login(
      _loginController.text,
      _passwordController.text,
    );

    if (result['success']) {
      if (mounted) {
        if (result['has_pin'] == true) {
          Navigator.pushReplacement(
            context,
            MaterialPageRoute(builder: (context) => const HomePage()),
          );
        } else {
          Navigator.pushReplacement(
            context,
            MaterialPageRoute(builder: (context) => const SetupPinPage()),
          );
        }
      }
    } else {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(result['message'] ?? "Login gagal. Cek koneksi & Data Anda.")),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);
    final user = authProvider.user;
    final cs = Theme.of(context).colorScheme;

    return Scaffold(
      body: SafeArea(
        child: SingleChildScrollView(
          child: Column(
            children: [
              // Header (banner + overlay)
              SizedBox(
                height: 280,
                width: double.infinity,
                child: Stack(
                  fit: StackFit.expand,
                  children: [
                    Image.asset("assets/images/banner.jpg", fit: BoxFit.cover), // Using single banner.jpg as requested
                    DecoratedBox(
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          begin: Alignment.topCenter,
                          end: Alignment.bottomCenter,
                          colors: [
                            Colors.white.withValues(alpha: 0.1),
                            Colors.white.withValues(alpha: 0.8),
                          ],
                        ),
                      ),
                    ),
                    SafeArea(
                      child: Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Image.asset("assets/images/logo.png", height: 30), // Assuming logo.png is the e+ logo
                            const Text("by Bank DP Taspen", style: TextStyle(fontWeight: FontWeight.bold, color: Colors.blueGrey, fontSize: 12)),
                          ],
                        ),
                      ),
                    ),
                    Padding(
                      padding: const EdgeInsets.all(20),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.center,
                        mainAxisAlignment: MainAxisAlignment.end,
                        children: [
                          if (user != null && !_forcePasswordLogin) ...[
                            Container(
                              padding: const EdgeInsets.all(4),
                              decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                color: Colors.white.withValues(alpha: 0.5),
                              ),
                              child: CircleAvatar(
                                radius: 60,
                                backgroundImage: user['photo_profile'] != null
                                    ? NetworkImage(user['photo_profile'])
                                    : const AssetImage("assets/images/profile.jpg") as ImageProvider,
                              ),
                            ),
                            const SizedBox(height: 16),
                            Text(
                              "Halo, ${user['name']}",
                              textAlign: TextAlign.center,
                              style: const TextStyle(
                                fontSize: 20,
                                fontWeight: FontWeight.w800,
                                color: Color(0xFF37474F),
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              (user['title'] ?? "-").toString(),
                              textAlign: TextAlign.center,
                              style: const TextStyle(
                                fontSize: 13,
                                color: Colors.blueGrey,
                              ),
                            ),
                          ] else ...[
                            Align(
                              alignment: Alignment.centerLeft,
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text(
                                    "Selamat Datang",
                                    style: TextStyle(
                                      fontSize: 24,
                                      fontWeight: FontWeight.w900,
                                      color: Color(0xFF37474F),
                                    ),
                                  ),
                                  const SizedBox(height: 6),
                                  Text(
                                    "Silakan login untuk melanjutkan",
                                    style: TextStyle(
                                      fontSize: 14,
                                      color: Colors.blueGrey.withValues(alpha: 0.8),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),
                  ],
                ),
              ),

              Padding(
                padding: const EdgeInsets.fromLTRB(20, 16, 20, 20),
                child: Column(
                  children: [
                    if (user == null || _forcePasswordLogin) ...[
                      Align(
                        alignment: Alignment.centerLeft,
                        child: Row(
                          children: [
                            const CircleAvatar(
                              radius: 20,
                              backgroundImage: AssetImage("assets/images/profile.jpg"),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Text(
                                "Absensi Bank DP Taspen",
                                style: TextStyle(
                                  fontWeight: FontWeight.w800,
                                  color: cs.onSurface,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 14),
                      Card(
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.stretch,
                            children: [
                              TextField(
                                controller: _loginController,
                                textInputAction: TextInputAction.next,
                                decoration: const InputDecoration(
                                  labelText: "Email atau NIP",
                                  prefixIcon: Icon(Icons.person_outline),
                                ),
                              ),
                              const SizedBox(height: 12),
                              TextField(
                                controller: _passwordController,
                                obscureText: true,
                                onSubmitted: (_) => _handleLogin(),
                                decoration: const InputDecoration(
                                  labelText: "Password",
                                  prefixIcon: Icon(Icons.lock_outline),
                                ),
                              ),
                              const SizedBox(height: 16),
                              SizedBox(
                                width: double.infinity,
                                child: FilledButton(
                                  onPressed: authProvider.isLoading ? null : _handleLogin,
                                  child: authProvider.isLoading
                                      ? const SizedBox(
                                          width: 18,
                                          height: 18,
                                          child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                                        )
                                      : const Text("Login"),
                                ),
                              ),
                              if (_forcePasswordLogin)
                                TextButton(
                                  onPressed: () => setState(() => _forcePasswordLogin = false),
                                  child: const Text("Kembali ke Login Cepat"),
                                ),
                            ],
                          ),
                        ),
                      ),
                    ] else ...[
                      const SizedBox(height: 20),
                      SizedBox(
                        width: double.infinity,
                        height: 56,
                        child: FilledButton(
                          onPressed: () {
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (context) => const PinPage(nextPage: "home"),
                              ),
                            );
                          },
                          style: FilledButton.styleFrom(
                            backgroundColor: const Color(0xFF2E7D32), // Green color from image
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(30)),
                          ),
                          child: const Text("Login", style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                        ),
                      ),
                    ],
                    const SizedBox(height: 30),
                    // Quick actions
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceAround,
                      children: [
                        _QuickActionIcon(
                          icon: Icons.login,
                          color: const Color(0xFF2E7D32),
                          title: "Absen Masuk",
                          onTap: () {
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (context) => const PinPage(nextPage: "masuk"),
                              ),
                            );
                          },
                        ),
                        _QuickActionIcon(
                          icon: Icons.logout,
                          color: const Color(0xFFC62828),
                          title: "Absen Keluar",
                          onTap: () {
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (context) => const PinPage(nextPage: "keluar"),
                              ),
                            );
                          },
                        ),
                        _QuickActionIcon(
                          icon: Icons.swap_horiz,
                          color: const Color(0xFF455A64),
                          title: "Ganti user",
                          onTap: () async {
                            await authProvider.logout();
                            setState(() {
                              _forcePasswordLogin = true;
                            });
                          },
                        ),
                      ],
                    ),

                    const SizedBox(height: 20),
                    Center(
                      child: Column(
                        children: [
                          const Text("Kendala login? Lihat FAQ", style: TextStyle(fontSize: 12, color: Colors.blueGrey)),
                          const SizedBox(height: 4),
                          Text("v1.0.0", style: TextStyle(fontSize: 10, color: cs.onSurface.withValues(alpha: 0.4))),
                        ],
                      ),
                    ),
                    const SizedBox(height: 10),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _QuickActionIcon extends StatelessWidget {
  final IconData icon;
  final Color color;
  final String title;
  final VoidCallback onTap;

  const _QuickActionIcon({
    required this.icon,
    required this.color,
    required this.title,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(16),
          child: Container(
            width: 64,
            height: 64,
            decoration: BoxDecoration(
              color: color,
              borderRadius: BorderRadius.circular(16),
              boxShadow: [
                BoxShadow(
                  color: color.withValues(alpha: 0.3),
                  blurRadius: 8,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: Icon(icon, color: Colors.white, size: 30),
          ),
        ),
        const SizedBox(height: 8),
        Text(
          title,
          style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600),
        ),
      ],
    );
  }
}

class _MenuIcon extends StatelessWidget {
  final IconData icon;
  final Color color;
  final String title;
  final VoidCallback? onTap;

  const _MenuIcon({
    required this.icon,
    required this.color,
    required this.title,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(14),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 12),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.08),
          borderRadius: BorderRadius.circular(14),
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, size: 26, color: color),
            const SizedBox(height: 8),
            Text(
              title,
              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}
