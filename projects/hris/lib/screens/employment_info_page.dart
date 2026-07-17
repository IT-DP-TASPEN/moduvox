import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';

class EmploymentInfoPage extends StatelessWidget {
  const EmploymentInfoPage({super.key});

  @override
  Widget build(BuildContext context) {
    final user = Provider.of<AuthProvider>(context).user;

    return Scaffold(
      backgroundColor: const Color(0xFFF0F4F3), // Light greenish-grey background
      appBar: AppBar(
        title: const Text(
          "Informasi kepegawaian",
          style: TextStyle(fontWeight: FontWeight.w600, fontSize: 18),
        ),
        elevation: 0,
      ),
      body: SingleChildScrollView(
        physics: const BouncingScrollPhysics(),
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            _buildSectionCard(
              context,
              title: "Informasi kepegawaian",
              icon: Icons.work,
              items: [
                _InfoItem(label: "NIP", value: user?['employee_id'] ?? "-"),
                _InfoItem(label: "Nama", value: user?['name'] ?? "-"),
                _InfoItem(label: "Jabatan", value: user?['title'] ?? user?['employment']?['position'] ?? "-"),
                _InfoItem(label: "Divisi", value: user?['division_name'] ?? user?['employment']?['department'] ?? "-"),
                _InfoItem(label: "Cabang", value: user?['branch_name'] ?? "-"),
                _InfoItem(label: "Lokasi Kerja", value: user?['office']?['name'] ?? "Belum Diatur"),
                _InfoItem(label: "Radius Absensi", value: user?['office']?['radius'] != null ? "${user?['office']?['radius']} meter" : "-"),
                _InfoItem(label: "Status Karyawan", value: user?['employment_status'] ?? user?['employment']?['employment_status'] ?? "-"),
                _InfoItem(label: "Tanggal Masuk", value: user?['join_date'] ?? user?['employment']?['join_date'] ?? "-"),
                _InfoItem(label: "Akhir Kontrak", value: user?['employment']?['contract_end_date'] ?? "-"),
                _InfoItem(label: "Golongan", value: user?['employment']?['grade'] ?? "-"),
                _InfoItem(label: "SKG", value: user?['employment']?['skg'] ?? "-"),
                _InfoItem(label: "Rekening Gaji", value: user?['employment']?['company_account_number'] ?? "-"),
                _InfoItem(label: "Rekening DPLK BNI", value: user?['employment']?['dplk_bni_account_number'] ?? "-"),
                _InfoItem(label: "Jatah Cuti Tahunan", value: user?['employment']?['leave_quota']?.toString() ?? "-"),
                _InfoItem(label: "Sisa Cuti Tahunan", value: user?['employment']?['remaining_leave']?.toString() ?? "-"),
              ],
            ),
            const SizedBox(height: 16),
            _buildSectionCard(
              context,
              title: "Informasi BPJS",
              icon: Icons.health_and_safety,
              items: [
                _InfoItem(label: "No. BPJS Ketenagakerjaan", value: user?['employment']?['bpjs_ketenagakerjaan_no'] ?? "-"),
                _InfoItem(label: "No. BPJS Kesehatan", value: user?['employment']?['bpjs_kesehatan_no'] ?? "-"),
              ],
            ),
            const SizedBox(height: 30),
          ],
        ),
      ),
    );
  }

  Widget _buildSectionCard(
    BuildContext context, {
    required String title,
    required IconData icon,
    required List<_InfoItem> items,
  }) {
    final cs = Theme.of(context).colorScheme;
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 4,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Header berwarna utama
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            decoration: BoxDecoration(
              color: cs.primary, // Mengikuti warna tema utama
              borderRadius: const BorderRadius.only(
                topLeft: Radius.circular(12),
                topRight: Radius.circular(12),
              ),
            ),
            child: Row(
              children: [
                Icon(icon, size: 20, color: Colors.white),
                const SizedBox(width: 12),
                Text(
                  title,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: Colors.white,
                  ),
                ),
              ],
            ),
          ),
          
          // Body putih
          Padding(
            padding: const EdgeInsets.symmetric(vertical: 12),
            child: Column(
              children: items.map((item) {
                return Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        flex: 5,
                        child: Text(
                          item.label,
                          style: TextStyle(
                            fontSize: 13,
                            color: Colors.grey.shade700,
                            fontWeight: FontWeight.w400,
                          ),
                        ),
                      ),
                      Expanded(
                        flex: 6,
                        child: Text(
                          item.value,
                          style: const TextStyle(
                            fontSize: 13,
                            color: Colors.black87,
                            fontWeight: FontWeight.w400,
                          ),
                          textAlign: TextAlign.left,
                        ),
                      ),
                    ],
                  ),
                );
              }).toList(),
            ),
          ),
        ],
      ),
    );
  }
}

class _InfoItem {
  final String label;
  final String value;

  const _InfoItem({
    required this.label,
    required this.value,
  });
}
