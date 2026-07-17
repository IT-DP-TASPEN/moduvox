import 'package:flutter/material.dart';

class FaqPage extends StatelessWidget {
  const FaqPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8F9FA),
      appBar: AppBar(
        title: const Text('Pusat Bantuan & FAQ', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
        backgroundColor: const Color(0xFF004A99),
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              "Pertanyaan Umum",
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF004A99)),
            ),
            const SizedBox(height: 15),
            _buildFaqItem(
              context,
              'Bagaimana cara melakukan absen masuk/keluar?',
              'Buka menu Beranda, pastikan Anda sudah berada di lokasi kantor yang ditentukan (dalam radius), klik tombol "Absen Masuk" atau "Absen Keluar", ambil foto selfie, dan tekan kirim.',
            ),
            _buildFaqItem(
              context,
              'Kenapa lokasi saya tidak terdeteksi?',
              'Pastikan GPS di HP Anda sudah aktif dan aplikasi telah diberikan izin akses lokasi "Saat Aplikasi Digunakan". Jika masih bermasalah, coba buka Google Maps sejenak untuk memicu refresh lokasi.',
            ),
            _buildFaqItem(
              context,
              'Apa yang harus saya lakukan jika lupa absen?',
              'Jika Anda lupa absen, silakan lakukan koordinasi dengan atasan dan gunakan menu "Izin" atau "Tugas Luar" sesuai dengan kebijakan HR yang berlaku.',
            ),
            _buildFaqItem(
              context,
              'Berapa lama proses persetujuan cuti/izin?',
              'Proses persetujuan bergantung pada kecepatan Approver (Atasan/Direktur) masing-masing divisi. Anda akan menerima notifikasi otomatis setelah status pengajuan Anda diperbarui.',
            ),
            _buildFaqItem(
              context,
              'Apakah slip gaji saya aman?',
              'Ya, fitur Slip Gaji di aplikasi HRIS Bank DP Taspen dilindungi oleh sistem keamanan PIN dan enkripsi data untuk memastikan privasi penghasilan Anda tetap terjaga.',
            ),
            const SizedBox(height: 30),
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: Colors.blue.withOpacity(0.05),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: const Color(0xFF004A99).withOpacity(0.1)),
              ),
              child: Column(
                children: [
                  const Icon(Icons.support_agent, size: 40, color: Color(0xFF004A99)),
                  const SizedBox(height: 10),
                  const Text(
                    "Butuh Bantuan Lebih Lanjut?",
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                  ),
                  const SizedBox(height: 5),
                  const Text(
                    "Silakan hubungi tim IT atau HR untuk bantuan teknis terkait akun dan fitur aplikasi.",
                    textAlign: TextAlign.center,
                    style: TextStyle(fontSize: 12, color: Colors.grey),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFaqItem(BuildContext context, String q, String a) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(color: Colors.black.withOpacity(0.03), blurRadius: 10, offset: const Offset(0, 2)),
        ],
      ),
      child: ExpansionTile(
        title: Text(q, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: Colors.black87)),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        collapsedShape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        childrenPadding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
        children: [
          Text(a, style: const TextStyle(fontSize: 12, color: Colors.grey, height: 1.5)),
        ],
      ),
    );
  }
}
