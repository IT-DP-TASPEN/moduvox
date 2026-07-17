import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';

class WarningHistoryPage extends StatelessWidget {
  const WarningHistoryPage({super.key});

  @override
  Widget build(BuildContext context) {
    final user = Provider.of<AuthProvider>(context).user;
    final List warnings = user?['warnings'] ?? [];
    final cs = Theme.of(context).colorScheme;

    return Scaffold(
      appBar: AppBar(
        title: const Text("Riwayat SP"),
        backgroundColor: cs.error,
      ),
      body: warnings.isEmpty
          ? const Center(child: Text("Bersih! Tidak ada riwayat SP"))
          : ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: warnings.length,
              itemBuilder: (context, index) {
                final item = warnings[index];
                return Card(
                  margin: const EdgeInsets.symmetric(vertical: 8),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  child: ListTile(
                    leading: Icon(Icons.warning_amber_rounded, color: cs.error),
                    title: Text("${item['level']} - ${item['date']}"),
                    subtitle: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text("Alasan: ${item['reason']}"),
                        Text("Berlaku s/d: ${item['expiry_date'] ?? '-'}", style: const TextStyle(fontWeight: FontWeight.bold)),
                      ],
                    ),
                  ),
                );
              },
            ),
    );
  }
}
