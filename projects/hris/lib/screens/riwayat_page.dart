import 'package:flutter/material.dart';

import 'attendance_history_page.dart';
import 'cuti_page.dart';
import 'izin_page.dart';
import 'lembur_page.dart';
import 'tugas_luar_page.dart';

class RiwayatPage extends StatelessWidget {
  const RiwayatPage({super.key});

  @override
  Widget build(BuildContext context) {
    final cs = Theme.of(context).colorScheme;
    return Column(
      children: [
        // Header Riwayat
        Container(
          width: double.infinity,
          color: cs.primary,
          padding: const EdgeInsets.all(16),
          child: const SafeArea(
            bottom: false,
            child: Text(
              "Riwayat",
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: Colors.white,
              ),
            ),
          ),
        ),

        // Body list riwayat
        Expanded(
          child: ListView(
            padding: const EdgeInsets.all(16),
            children: [
              _RiwayatCard(
                title: "Cuti",
                icon: Icons.access_time,
                onTap: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const CutiPage(initialTab: 1)),
                  );
                },
              ),
              _RiwayatCard(
                title: "Izin",
                icon: Icons.edit_calendar,
                onTap: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const IzinPage(initialTab: 1)),
                  );
                },
              ),
              _RiwayatCard(
                title: "Tugas Luar",
                icon: Icons.work,
                onTap: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const TugasLuarPage(initialTab: 1)),
                  );
                },
              ),
              _RiwayatCard(
                title: "Lembur",
                icon: Icons.hourglass_bottom,
                onTap: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const LemburPage(initialTab: 1)),
                  );
                },
              ),
              _RiwayatCard(
                title: "Absensi Online",
                icon: Icons.fingerprint,
                onTap: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const AttendanceHistoryPage()),
                  );
                },
              ),
              const _RiwayatCard(title: "Perubahan Profil", icon: Icons.people_alt),
              const _RiwayatCard(title: "Rekam Kegiatan", icon: Icons.event_note),
              const _RiwayatCard(title: "Koreksi Kehadiran", icon: Icons.edit),
            ],
          ),
        ),
      ],
    );
  }
}

class _RiwayatCard extends StatelessWidget {
  final String title;
  final IconData icon;
  final VoidCallback? onTap;

  const _RiwayatCard({required this.title, required this.icon, this.onTap});

  @override
  Widget build(BuildContext context) {
    final cs = Theme.of(context).colorScheme;
    return Card(
      margin: const EdgeInsets.symmetric(vertical: 8),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: ListTile(
        contentPadding:
            const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        leading: Icon(icon, color: cs.primary),
        title: Text(
          "Riwayat $title",
          style: const TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w600,
          ),
        ),
        subtitle: Text(title),
        onTap: onTap,
      ),
    );
  }
}
