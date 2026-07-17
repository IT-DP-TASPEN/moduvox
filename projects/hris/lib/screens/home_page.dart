import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import 'beranda_page.dart';
import 'riwayat_page.dart';
import 'profil_page.dart';
import 'login_page.dart';
import 'settings_page.dart';
import 'about_page.dart';
import 'faq_page.dart';
import 'notifications_page.dart';

class HomePage extends StatefulWidget {
  const HomePage({super.key});

  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  int _selectedIndex = 0;
  int _unreadCount = 0;

  final List<Widget> _pages = const [
    BerandaPage(),
    RiwayatPage(),
    ProfilPage(),
  ];

  @override
  void initState() {
    super.initState();
    _fetchUnreadNotifications();
  }

  Future<void> _fetchUnreadNotifications() async {
    try {
      final response = await ApiService().getNotifications();
      if (response.statusCode == 200) {
        final decoded = jsonDecode(response.body);
        final List data = (decoded is Map && decoded['status'] == 'success') ? decoded['data'] : [];
        int unread = data.where((n) => n['read_at'] == null).length;
        if (mounted) {
          setState(() {
            _unreadCount = unread;
          });
        }
      }
    } catch (e) {
      debugPrint("Gagal mengambil notifikasi: $e");
    }
  }

  void _onNavTap(int index) {
    setState(() {
      _selectedIndex = index;
    });
  }

  void _navigateFromDrawer(_DrawerAction action) {
    Navigator.of(context).pop(); // close drawer

    switch (action) {
      case _DrawerAction.profileDetail:
        setState(() => _selectedIndex = 2);
        return;
      case _DrawerAction.settings:
        Navigator.push(context, MaterialPageRoute(builder: (_) => const SettingsPage()));
        return;
      case _DrawerAction.faq:
        Navigator.push(context, MaterialPageRoute(builder: (_) => const FaqPage()));
        return;
      case _DrawerAction.logout:
        Provider.of<AuthProvider>(context, listen: false).logout().then((_) {
          if (!mounted) return;
          Navigator.pushAndRemoveUntil(
            context,
            MaterialPageRoute(builder: (_) => const LoginPage()),
            (_) => false,
          );
        });
        return;
    }
  }

  PreferredSizeWidget _buildAppBar(BuildContext context) {
    return AppBar(
      backgroundColor: const Color(0xFF004A99),
      title: const Text(
        "BANK DP TASPEN",
        style: TextStyle(color: Colors.white),
      ),
      leading: Builder(
        builder: (context) => IconButton(
          icon: const Icon(Icons.menu, color: Colors.white),
          onPressed: () => Scaffold.of(context).openDrawer(),
        ),
      ),
      actions: [
        Stack(
          children: [
            IconButton(
              icon: const Icon(Icons.notifications_none, color: Colors.white),
              onPressed: () {
                Navigator.push(context, MaterialPageRoute(builder: (_) => const NotificationsPage()))
                  .then((_) => _fetchUnreadNotifications());
              },
            ),
            if (_unreadCount > 0)
              Positioned(
                right: 10,
                top: 10,
                child: Container(
                  padding: const EdgeInsets.all(4),
                  decoration: const BoxDecoration(
                    color: Colors.red,
                    shape: BoxShape.circle,
                  ),
                  child: Text(
                    "$_unreadCount",
                    style: const TextStyle(color: Colors.white, fontSize: 10),
                  ),
                ),
              )
          ],
        )
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      // AppBar hanya tampil di Beranda
      appBar: _selectedIndex == 0 ? _buildAppBar(context) : null,

      // Drawer hanya muncul di Beranda
      drawer: _selectedIndex == 0 ? _SideMenu(onAction: _navigateFromDrawer) : null,

      // SafeArea top aktif ketika tidak ada AppBar
      body: SafeArea(
        top: _selectedIndex != 0,
        child: IndexedStack(
          index: _selectedIndex,
          children: _pages,
        ),
      ),

      bottomNavigationBar: NavigationBar(
        selectedIndex: _selectedIndex,
        onDestinationSelected: _onNavTap,
        destinations: const [
          NavigationDestination(icon: Icon(Icons.home_outlined), selectedIcon: Icon(Icons.home), label: "Beranda"),
          NavigationDestination(icon: Icon(Icons.history_outlined), selectedIcon: Icon(Icons.history), label: "Riwayat"),
          NavigationDestination(icon: Icon(Icons.person_outline), selectedIcon: Icon(Icons.person), label: "Profil"),
        ],
      ),
    );
  }
}

/// --- SIDEBAR MENU ---
class _SideMenu extends StatelessWidget {
  final void Function(_DrawerAction action) onAction;

  const _SideMenu({required this.onAction});

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);
    final user = authProvider.user;

    return Drawer(
      child: Container(
        color: const Color(0xFF004A99),
        child: SafeArea(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SizedBox(height: 20),

              // Foto profil
              Center(
                child: CircleAvatar(
                  radius: 45,
                  backgroundImage: user != null && user['photo_profile'] != null
                      ? NetworkImage(user['photo_profile'])
                      : const AssetImage("assets/images/profile.jpg") as ImageProvider,
                ),
              ),
              const SizedBox(height: 12),

              Center(
                child: Text(
                  user != null ? user['name'] : "Nama User",
                  style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
                ),
              ),
              Center(
                child: Text(
                  user != null ? user['title'] ?? "-" : "Jabatan",
                  style: const TextStyle(color: Colors.white70, fontSize: 14),
                ),
              ),

              const SizedBox(height: 30),

              // Menu item
              _buildMenuItem(Icons.person, "Detail profil", () => onAction(_DrawerAction.profileDetail)),
              _buildMenuItem(Icons.settings, "Pengaturan", () => onAction(_DrawerAction.settings)),
              _buildMenuItem(Icons.help, "FAQ", () => onAction(_DrawerAction.faq)),
              _buildMenuItem(Icons.logout, "Log out", () => onAction(_DrawerAction.logout)),

              const Spacer(),
              const Center(
                child: Column(
                  children: [
                    Text(
                      "HRIS",
                      style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 18),
                    ),
                    Text(
                      "Bank DP Taspen",
                      style: TextStyle(color: Colors.white70, fontSize: 14),
                    ),
                    SizedBox(height: 20),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  static Widget _buildMenuItem(IconData icon, String title, VoidCallback onTap) {
    return ListTile(
      leading: Icon(icon, color: Colors.white),
      title: Text(title, style: const TextStyle(color: Colors.white)),
      onTap: onTap,
    );
  }
}

enum _DrawerAction {
  profileDetail,
  settings,
  faq,
  logout,
}
