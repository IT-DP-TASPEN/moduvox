import 'package:flutter/material.dart';

class AboutPage extends StatelessWidget {
  const AboutPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Tentang Kami'),
        backgroundColor: const Color(0xFF004A99),
        foregroundColor: Colors.white,
      ),
      body: const Padding(
        padding: EdgeInsets.all(16),
        child: Text(
          'Bank DP Taspen\n\n'
          'Aplikasi Absensi Mandiri Bank DP Taspen dirancang untuk memudahkan seluruh karyawan dalam melakukan pencatatan kehadiran, pengajuan izin, cuti, serta monitoring kinerja secara efisien dan transparan.',
        ),
      ),
    );
  }
}

