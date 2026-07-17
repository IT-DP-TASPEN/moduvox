import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/settings_provider.dart';
import 'change_pin_page.dart';
import 'change_password_page.dart';

class SettingsPage extends StatelessWidget {
  const SettingsPage({super.key});

  @override
  Widget build(BuildContext context) {
    const primaryColor = Color(0xFF004A99); // Taspen Blue
    final settings = context.watch<SettingsProvider>();
    
    return Scaffold(
      backgroundColor: const Color(0xFFF3F4F6),
      appBar: AppBar(
        title: const Text('Pengaturan', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
        backgroundColor: primaryColor,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // --- PENGATURAN UMUM ---
          _buildSection(
            title: "Pengaturan umum",
            icon: Icons.settings,
            primaryColor: primaryColor,
            children: [
              const Padding(
                padding: EdgeInsets.fromLTRB(16, 16, 16, 8),
                child: Text("Pilih bahasa", style: TextStyle(color: Colors.grey, fontSize: 12)),
              ),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12),
                  decoration: BoxDecoration(
                    border: Border.all(color: Colors.grey.shade300),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: DropdownButtonHideUnderline(
                    child: DropdownButton<String>(
                      value: settings.language,
                      isExpanded: true,
                      items: ["Bahasa Indonesia", "English"].map((l) {
                        return DropdownMenuItem(value: l, child: Text(l, style: const TextStyle(fontSize: 14)));
                      }).toList(),
                      onChanged: (v) => settings.setLanguage(v!),
                    ),
                  ),
                ),
              ),
              _buildSwitchTile("Simpan foto ke galeri", settings.saveToGallery, (v) => settings.setSaveToGallery(v), primaryColor),
              _buildSwitchTile("Smart warning absensi", settings.smartWarning, (v) => settings.setSmartWarning(v), primaryColor),
              _buildSwitchTile("Nonaktifkan pengiriman notifikasi melalui email", settings.disableEmailNotif, (v) => settings.setDisableEmailNotif(v), primaryColor),
              const SizedBox(height: 8),
            ],
          ),

          const SizedBox(height: 20),

          // --- KEAMANAN AKUN ---
          _buildSection(
            title: "Keamanan Akun",
            icon: Icons.lock_person,
            primaryColor: primaryColor,
            children: [
              _buildActionTile("Ubah PIN", () {
                Navigator.push(context, MaterialPageRoute(builder: (_) => const ChangePinPage()));
              }),
              _buildActionTile("Ubah password", () {
                Navigator.push(context, MaterialPageRoute(builder: (_) => const ChangePasswordPage()));
              }),
              const SizedBox(height: 8),
            ],
          ),

          const SizedBox(height: 20),

          // --- PENGATURAN REMINDER ---
          _buildSection(
            title: "Pengaturan reminder",
            icon: Icons.access_time_filled,
            primaryColor: primaryColor,
            children: [
              Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text("Reminder absensi", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                        Switch(
                          value: settings.isReminderActive,
                          onChanged: (v) => settings.toggleReminder(v),
                          activeColor: primaryColor,
                        ),
                      ],
                    ),
                    const SizedBox(height: 4),
                    const Text(
                      "Menerima notifikasi pengingat absen.",
                      style: TextStyle(color: Colors.grey, fontSize: 12),
                    ),
                    const SizedBox(height: 15),
                    
                    // CUSTOM TIME PICKERS
                    Row(
                      children: [
                        Expanded(
                          child: _buildTimePickerTile(
                            context,
                            "Jam Masuk",
                            settings.reminderInTime,
                            (t) => settings.setReminderInTime(t),
                            primaryColor,
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: _buildTimePickerTile(
                            context,
                            "Jam Pulang",
                            settings.reminderOutTime,
                            (t) => settings.setReminderOutTime(t),
                            primaryColor,
                          ),
                        ),
                      ],
                    ),

                    const SizedBox(height: 15),
                    const Text(
                      "Untuk dapat menggunakan fitur ini, Anda harus mengizinkan aplikasi ini berjalan dilatar belakang",
                      style: TextStyle(color: Colors.redAccent, fontSize: 11, fontStyle: FontStyle.italic),
                    ),
                    const SizedBox(height: 10),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildTimePickerTile(BuildContext context, String label, TimeOfDay time, Function(TimeOfDay) onSelected, Color color) {
    return InkWell(
      onTap: () async {
        final picked = await showTimePicker(context: context, initialTime: time);
        if (picked != null) onSelected(picked);
      },
      child: Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          border: Border.all(color: Colors.grey.shade300),
          borderRadius: BorderRadius.circular(10),
        ),
        child: Column(
          children: [
            Text(label, style: const TextStyle(fontSize: 10, color: Colors.grey)),
            const SizedBox(height: 4),
            Text(
              time.format(context),
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: color),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSection({required String title, required IconData icon, required Color primaryColor, required List<Widget> children}) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 10, offset: const Offset(0, 2)),
        ],
      ),
      clipBehavior: Clip.antiAlias,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            color: primaryColor,
            child: Row(
              children: [
                Icon(icon, color: Colors.white, size: 20),
                const SizedBox(width: 10),
                Text(
                  title,
                  style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14),
                ),
              ],
            ),
          ),
          ...children,
        ],
      ),
    );
  }

  Widget _buildSwitchTile(String title, bool value, ValueChanged<bool> onChanged, Color primaryColor) {
    return Column(
      children: [
        const Divider(height: 1, indent: 16, endIndent: 16),
        SwitchListTile(
          title: Text(title, style: const TextStyle(fontSize: 13, color: Colors.black87)),
          value: value,
          onChanged: onChanged,
          activeColor: primaryColor,
          contentPadding: const EdgeInsets.symmetric(horizontal: 16),
        ),
      ],
    );
  }

  Widget _buildActionTile(String title, VoidCallback onTap) {
    return Column(
      children: [
        const Divider(height: 1, indent: 16, endIndent: 16),
        ListTile(
          title: Text(title, style: const TextStyle(fontSize: 13, color: Colors.black87)),
          trailing: const Icon(Icons.chevron_right, size: 20, color: Colors.grey),
          onTap: onTap,
          contentPadding: const EdgeInsets.symmetric(horizontal: 16),
          dense: true,
        ),
      ],
    );
  }
}
