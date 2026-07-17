import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';

class PersonalInfoPage extends StatelessWidget {
  const PersonalInfoPage({super.key});

  @override
  Widget build(BuildContext context) {
    final user = Provider.of<AuthProvider>(context).user;
    final cs = Theme.of(context).colorScheme;

    return Scaffold(
      backgroundColor: const Color(0xFFF0F4F3), // Light greenish-grey background
      appBar: AppBar(
        title: const Text(
          "Informasi personal",
          style: TextStyle(fontWeight: FontWeight.w600, fontSize: 18),
        ),
        elevation: 0,
        actions: [
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
            child: ElevatedButton.icon(
              onPressed: () {},
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.white,
                foregroundColor: Colors.black87,
                elevation: 0,
                padding: const EdgeInsets.symmetric(horizontal: 12),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
                minimumSize: const Size(0, 0),
              ),
              icon: const Icon(Icons.edit, size: 16, color: Colors.red),
              label: const Text(
                "Ubah",
                style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
              ),
            ),
          )
        ],
      ),
      body: SingleChildScrollView(
        physics: const BouncingScrollPhysics(),
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            // Foto Profil
            _buildSectionCard(
              context,
              title: "Foto profil",
              icon: Icons.face_retouching_natural,
              customBody: Padding(
                padding: const EdgeInsets.symmetric(vertical: 24),
                child: Center(
                  child: CircleAvatar(
                    radius: 60,
                    backgroundColor: Colors.grey.shade200,
                    backgroundImage: user != null && user['photo_profile'] != null
                        ? NetworkImage(user['photo_profile'])
                        : const AssetImage("assets/images/profile.jpg") as ImageProvider,
                  ),
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Informasi umum
            _buildSectionCard(
              context,
              title: "Informasi umum",
              icon: Icons.people_alt,
              items: [
                _InfoItem(label: "Nama lengkap", value: user?['name'] ?? "-"),
                _InfoItem(label: "Username", value: user?['username'] ?? "-"),
                _InfoItem(label: "Tempat lahir", value: user?['profile']?['birth_place'] ?? "-"),
                _InfoItem(label: "Tanggal lahir", value: user?['birth_date'] ?? "-"),
                _InfoItem(label: "Jenis kelamin", value: user?['gender'] == 'L' ? 'Laki-laki' : (user?['gender'] == 'P' ? 'Perempuan' : '-')),
                _InfoItem(label: "Status nikah", value: user?['profile']?['marital_status'] ?? "-"),
                _InfoItem(label: "Agama", value: user?['profile']?['religion'] ?? "-"),
                _InfoItem(label: "Nomor KTP", value: user?['profile']?['nik'] ?? user?['nik'] ?? "-"),
                _InfoItem(label: "Nomor Paspor", value: user?['profile']?['passport_no'] ?? user?['passport_no'] ?? "-"),
                _InfoItem(label: "Nomor handphone", value: user?['phone'] ?? "-"),
                _InfoItem(label: "Email", value: user?['email'] ?? "-"),
                _InfoItem(label: "Golongan darah", value: user?['profile']?['blood_type'] ?? "-"),
              ],
            ),
            const SizedBox(height: 16),

            // Pendidikan terakhir
            _buildSectionCard(
              context,
              title: "Pendidikan terakhir",
              icon: Icons.school,
              items: [
                _InfoItem(label: "Jenjang pendidikan", value: user?['profile']?['education_level'] ?? "-"),
                _InfoItem(label: "Lembaga pendidikan", value: user?['profile']?['education_institution'] ?? "-"),
                _InfoItem(label: "Tahun lulus", value: user?['profile']?['graduation_year'] ?? "-"),
                _InfoItem(label: "IPK / Nilai", value: user?['profile']?['gpa'] ?? "-"),
              ],
            ),
            const SizedBox(height: 16),

            // Informasi PTKP
            _buildSectionCard(
              context,
              title: "Informasi PTKP",
              icon: Icons.account_balance_wallet,
              items: [
                _InfoItem(label: "Status PTKP", value: user?['profile']?['ptkp_status'] ?? "-"),
                _InfoItem(label: "Tahun Berlaku", value: user?['profile']?['ptkp_year'] ?? "-"),
              ],
            ),
            const SizedBox(height: 16),

            // Alamat KTP
            _buildSectionCard(
              context,
              title: "Alamat KTP",
              icon: Icons.home_work,
              items: [
                _InfoItem(label: "Alamat", value: user?['profile']?['ktp_address'] ?? "-"),
                _InfoItem(
                  label: "Kel/Kota",
                  value: "${user?['profile']?['ktp_village'] ?? '-'}, ${user?['profile']?['ktp_city'] ?? '-'}",
                ),
              ],
            ),
            const SizedBox(height: 16),

            // Alamat Domisili
            _buildSectionCard(
              context,
              title: "Alamat domisili",
              icon: Icons.location_on,
              items: [
                _InfoItem(label: "Alamat", value: user?['profile']?['domicile_address'] ?? "-"),
                _InfoItem(
                  label: "Kel/Kota",
                  value: "${user?['profile']?['domicile_village'] ?? '-'}, ${user?['profile']?['domicile_city'] ?? '-'}",
                ),
              ],
            ),
            const SizedBox(height: 16),

            // Kontak Darurat
            _buildSectionCard(
              context,
              title: "Kontak darurat",
              icon: Icons.emergency,
              items: [
                _InfoItem(label: "Informasi", value: user?['profile']?['emergency_contact_info'] ?? "-"),
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
    List<_InfoItem>? items,
    Widget? customBody,
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
          if (customBody != null) customBody,
          if (items != null)
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
