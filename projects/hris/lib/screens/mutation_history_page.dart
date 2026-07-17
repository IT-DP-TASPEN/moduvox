import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';

class MutationHistoryPage extends StatelessWidget {
  const MutationHistoryPage({super.key});

  @override
  Widget build(BuildContext context) {
    final user = Provider.of<AuthProvider>(context).user;
    final List mutations = user?['mutations'] ?? [];
    final cs = Theme.of(context).colorScheme;

    return Scaffold(
      appBar: AppBar(
        title: const Text("Riwayat Mutasi"),
      ),
      body: mutations.isEmpty
          ? const Center(child: Text("Belum ada riwayat mutasi"))
          : ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: mutations.length,
              itemBuilder: (context, index) {
                final item = mutations[index];
                return Card(
                  margin: const EdgeInsets.symmetric(vertical: 8),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  child: ListTile(
                    leading: Icon(Icons.swap_horiz, color: cs.primary),
                    title: Text("${item['type']} - ${item['date']}"),
                    subtitle: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text("${item['old_position']} ➔ ${item['new_position']}"),
                        if (item['description'] != null)
                          Text(item['description'], style: const TextStyle(fontStyle: FontStyle.italic, fontSize: 12)),
                      ],
                    ),
                  ),
                );
              },
            ),
    );
  }
}
