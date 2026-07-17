import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';

class UserFilesPage extends StatelessWidget {
  const UserFilesPage({super.key});

  @override
  Widget build(BuildContext context) {
    final cs = Theme.of(context).colorScheme;
    final user = Provider.of<AuthProvider>(context).user;
    final List files = user?['files'] ?? [];

    return Scaffold(
      appBar: AppBar(
        title: const Text("File Saya"),
      ),
      body: files.isEmpty
          ? const Center(child: Text("Belum ada lampiran file"))
          : ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: files.length,
              itemBuilder: (context, index) {
                final item = files[index];
                return Card(
                  margin: const EdgeInsets.symmetric(vertical: 8),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  child: ListTile(
                    leading: Icon(Icons.insert_drive_file, color: cs.primary),
                    title: Text(item['name']),
                    subtitle: Text(item['file_type']?.toUpperCase() ?? "-"),
                    trailing: const Icon(Icons.download),
                    onTap: () {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text("Dokumen ini read-only dan dikelola oleh Admin HR.")),
                      );
                    },
                  ),
                );
              },
            ),
    );
  }
}
