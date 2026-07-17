import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import 'package:carousel_slider/carousel_slider.dart';

import 'absensi_page.dart';
import 'cuti_page.dart';
import 'izin_page.dart';
import 'lembur_page.dart';
import 'tugas_luar_page.dart';
import 'absen_masuk_page.dart';
import 'absen_keluar_page.dart';
import '../providers/settings_provider.dart';
import 'attendance/attendance_recap_screen.dart';
import 'kpi_page.dart';
import 'slip_gaji_page.dart';
import 'package:url_launcher/url_launcher.dart';

class BerandaPage extends StatefulWidget {
  const BerandaPage({super.key});

  @override
  State<BerandaPage> createState() => _BerandaPageState();
}

class _BerandaPageState extends State<BerandaPage> {
  int _currentIndex = 0;
  bool _hasCheckedIn = false;
  bool _hasCheckedOut = false;
  String? _checkInTime;
  String? _checkOutTime;
  bool _isLoadingAttendance = true;

  List<dynamic> bannerList = [
    "assets/images/banner.jpg",
    "assets/images/banner1.jpg",
    "assets/images/banner2.jpg",
    "assets/images/banner3.jpg",
    "assets/images/banner4.jpg",
  ];
  bool _isLoadingBanners = false;

  List<Map<String, dynamic>> _getMenu(bool isApprover) {
    return [
      {"title": "Cuti", "icon": Icons.calendar_month},
      {"title": "Izin", "icon": Icons.edit_note},
      {"title": "Tugas Luar", "icon": Icons.work},
      {"title": "Lembur", "icon": Icons.access_time},
      {"title": "Absensi", "icon": Icons.fingerprint},
      {"title": "Rekap Absensi", "icon": Icons.assignment},
      {"title": "Slip Gaji", "icon": Icons.receipt_long},
      {"title": "KPI", "icon": Icons.trending_up},
      {"title": "Lainnya", "icon": Icons.more_horiz},
    ];
  }

  @override
  void initState() {
    super.initState();
    _checkAttendance();
    _refreshUser();
    _fetchBanners();
  }

  Future<void> _fetchBanners() async {
    try {
      final resp = await ApiService().getBanners();
      if (resp.statusCode == 200) {
        final decoded = jsonDecode(resp.body);
        if (decoded['status'] == 'success' && decoded['data'] is List) {
          final data = decoded['data'] as List;
          if (data.isNotEmpty) {
            setState(() {
              bannerList = data;
            });
          }
        }
      }
    } catch (e) {
      debugPrint("Error fetching banners: $e");
    }
  }

  Future<void> _refreshUser() async {
    await context.read<AuthProvider>().loadUser();
  }

  Future<void> _checkAttendance() async {
    if (!mounted) return;
    setState(() => _isLoadingAttendance = true);
    try {
      final resp = await ApiService().getHistory();
      if (resp.statusCode == 200) {
        final decoded = jsonDecode(resp.body);
        final items = (decoded is Map && decoded['data'] is List) ? (decoded['data'] as List) : const [];
        
        final now = DateTime.now();
        String? inTime;
        String? outTime;
        bool checkedIn = false;
        bool checkedOut = false;

        for (final it in items) {
          if (it is! Map) continue;
          final createdAt = it['created_at'];
          if (createdAt == null) continue;
          
          final date = DateTime.tryParse(createdAt.toString())?.toLocal();
          if (date != null && date.year == now.year && date.month == now.month && date.day == now.day) {
            if (it['type'] == 'masuk') {
              checkedIn = true;
              inTime = DateFormat('HH:mm').format(date);
            }
            if (it['type'] == 'keluar') {
              checkedOut = true;
              outTime = DateFormat('HH:mm').format(date);
            }
          }
        }
        
        if (mounted) {
          setState(() {
            _hasCheckedIn = checkedIn;
            _hasCheckedOut = checkedOut;
            _checkInTime = inTime;
            _checkOutTime = outTime;
            _isLoadingAttendance = false;
          });

          // Smart Warning Logic
          final settings = Provider.of<SettingsProvider>(context, listen: false);
          if (settings.smartWarning && !checkedIn) {
            final now = DateTime.now();
            final warningTime = DateTime(now.year, now.month, now.day, 8, 0);
            if (now.isAfter(warningTime)) {
              Future.delayed(const Duration(seconds: 1), () {
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text("⚠️ Anda belum absen masuk! Segera lakukan absen."),
                      backgroundColor: Colors.orange,
                      duration: Duration(seconds: 5),
                    ),
                  );
                }
              });
            }
          }
        }
      } else {
        if (mounted) setState(() => _isLoadingAttendance = false);
      }
    } catch (e) {
      if (mounted) setState(() => _isLoadingAttendance = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);
    final user = authProvider.user;
    final bool isApprover = user?['is_approver'] == true;
    final menuList = _getMenu(isApprover);
    final cs = Theme.of(context).colorScheme;

    return SingleChildScrollView(
      physics: const BouncingScrollPhysics(),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // 🔹 Profile Card (Enterprise Look)
          Padding(
            padding: const EdgeInsets.all(16.0),
            child: Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.04),
                    blurRadius: 10,
                    offset: const Offset(0, 4),
                  ),
                ],
                border: Border.all(color: Colors.grey.shade100),
              ),
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 28,
                    backgroundColor: cs.primary.withValues(alpha: 0.1),
                    backgroundImage: user != null && user['photo_profile'] != null
                        ? NetworkImage(user['photo_profile'])
                        : const AssetImage("assets/images/profile.jpg") as ImageProvider,
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          user != null ? user['name'] : "Nama Karyawan",
                          style: const TextStyle(
                            fontWeight: FontWeight.w800,
                            fontSize: 16,
                            letterSpacing: -0.5,
                          ),
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 2),
                        Text(
                          user != null ? (user['title'] ?? "-").toString() : "Jabatan",
                          style: TextStyle(
                            fontSize: 13,
                            color: Colors.grey.shade600,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: cs.primary.withValues(alpha: 0.08),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.calendar_today, size: 14, color: cs.primary),
                        const SizedBox(width: 4),
                        Text(
                          user?['employment'] != null 
                            ? "Cuti: ${user?['employment']?['remaining_leave']}" 
                            : "Cuti: ...",
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                            color: cs.primary,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),

          // 🔹 Aksi Cepat (Absen Masuk & Keluar)
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16.0),
            child: Row(
              children: [
                Expanded(
                  child: _QuickActionCard(
                    icon: Icons.login_rounded,
                    title: "Absen Masuk",
                    subtitle: _hasCheckedIn ? "Pukul $_checkInTime" : null,
                    color: cs.primary,
                    isDisabled: _isLoadingAttendance || _hasCheckedIn,
                    onTap: () async {
                      await Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const AbsenMasukPage()),
                      );
                      _checkAttendance();
                    },
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: _QuickActionCard(
                    icon: Icons.logout_rounded,
                    title: "Absen Keluar",
                    subtitle: _hasCheckedOut ? "Pukul $_checkOutTime" : null,
                    color: Colors.red.shade600,
                    isDisabled: _isLoadingAttendance || !_hasCheckedIn || _hasCheckedOut,
                    onTap: () async {
                      await Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const AbsenKeluarPage()),
                      );
                      _checkAttendance();
                    },
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),

          // 🔹 Slider
          CarouselSlider(
            items: bannerList.map((banner) {
              final String imageUrl = banner is Map ? banner['image_url'] : banner;
              final bool isNetwork = imageUrl.startsWith('http');

              return ClipRRect(
                borderRadius: BorderRadius.circular(16),
                child: isNetwork 
                  ? Image.network(
                      imageUrl,
                      fit: BoxFit.cover,
                      width: double.infinity,
                      errorBuilder: (context, error, stackTrace) => Image.asset(
                        "assets/images/banner.jpg",
                        fit: BoxFit.cover,
                        width: double.infinity,
                      ),
                    )
                  : Image.asset(
                      imageUrl,
                      fit: BoxFit.cover,
                      width: double.infinity,
                    ),
              );
            }).toList(),
            options: CarouselOptions(
              height: 140,
              autoPlay: true,
              enlargeCenterPage: true,
              viewportFraction: 0.9,
              onPageChanged: (index, reason) {
                setState(() {
                  _currentIndex = index;
                });
              },
            ),
          ),

          // 🔹 Indicator
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: bannerList.asMap().entries.map((entry) {
              return Container(
                width: _currentIndex == entry.key ? 16 : 6,
                height: 6,
                margin: const EdgeInsets.symmetric(vertical: 12, horizontal: 3),
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(4),
                  color: _currentIndex == entry.key
                      ? cs.primary
                      : Colors.grey.shade300,
                ),
              );
            }).toList(),
          ),

          // 🔹 Menu Utama Header
          const Padding(
            padding: EdgeInsets.fromLTRB(20, 10, 20, 10),
            child: Text(
              "Menu Utama",
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w800,
                letterSpacing: -0.5,
                color: Colors.black87,
              ),
            ),
          ),

          // 🔹 Menu Grid
          GridView.builder(
            physics: const NeverScrollableScrollPhysics(),
            shrinkWrap: true,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 4,
              mainAxisExtent: 110,
              crossAxisSpacing: 12,
              mainAxisSpacing: 16,
            ),
            itemCount: menuList.length,
            itemBuilder: (context, index) {
              return InkWell(
                borderRadius: BorderRadius.circular(16),
                onTap: () {
                  final title = menuList[index]["title"];
                  if (title == "Absensi") {
                    Navigator.push(context, MaterialPageRoute(builder: (_) => const AbsensiPage()));
                  } else if (title == "Cuti") {
                    Navigator.push(context, MaterialPageRoute(builder: (_) => const CutiPage()));
                  } else if (title == "Izin") {
                    Navigator.push(context, MaterialPageRoute(builder: (_) => const IzinPage()));
                  } else if (title == "Lembur") {
                    Navigator.push(context, MaterialPageRoute(builder: (_) => const LemburPage()));
                  } else if (title == "Tugas Luar") {
                    Navigator.push(context, MaterialPageRoute(builder: (_) => const TugasLuarPage()));
                  } else if (title == "Rekap Absensi") {
                    Navigator.push(context, MaterialPageRoute(builder: (_) => const AttendanceRecapScreen()));
                  } else if (title == "Slip Gaji") {
                    Navigator.push(context, MaterialPageRoute(builder: (_) => const SlipGajiPage()));
                  } else if (title == "KPI") {
                    Navigator.push(context, MaterialPageRoute(builder: (_) => const KpiPage()));
                  } else {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text("Menu $title belum tersedia.")),
                    );
                  }
                },
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(16),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.03),
                            blurRadius: 8,
                            offset: const Offset(0, 2),
                          ),
                        ],
                        border: Border.all(color: Colors.grey.shade100),
                      ),
                      child: Icon(
                        menuList[index]["icon"],
                        size: 26,
                        color: cs.primary,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      menuList[index]["title"],
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                        color: Colors.black87,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              );
            },
          ),
          const SizedBox(height: 20),
        ],
      ),
    );
  }
}

class _QuickActionCard extends StatelessWidget {
  final IconData icon;
  final String title;
  final String? subtitle;
  final Color color;
  final VoidCallback onTap;
  final bool isDisabled;

  const _QuickActionCard({
    required this.icon,
    required this.title,
    this.subtitle,
    required this.color,
    required this.onTap,
    this.isDisabled = false,
  });

  @override
  Widget build(BuildContext context) {
    final displayColor = isDisabled ? Colors.grey : color;
    
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: isDisabled ? null : onTap,
        borderRadius: BorderRadius.circular(16),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 12),
          decoration: BoxDecoration(
            color: isDisabled ? Colors.grey.shade50 : Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: displayColor.withValues(alpha: 0.3)),
            boxShadow: [
              if (!isDisabled)
                BoxShadow(
                  color: color.withValues(alpha: 0.1),
                  blurRadius: 10,
                  offset: const Offset(0, 4),
                ),
            ],
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: displayColor.withValues(alpha: 0.1),
                  shape: BoxShape.circle,
                ),
                child: Icon(icon, color: displayColor, size: 20),
              ),
              const SizedBox(width: 10),
              Flexible(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: TextStyle(
                        fontWeight: FontWeight.w700,
                        color: displayColor,
                        fontSize: 14,
                        letterSpacing: -0.5,
                      ),
                    ),
                    if (subtitle != null)
                      Text(
                        subtitle!,
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                          color: displayColor.withValues(alpha: 0.7),
                        ),
                      ),
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
