import 'dart:io';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:absensi/providers/auth_provider.dart';
import 'package:absensi/services/api_service.dart';
import 'package:image_picker/image_picker.dart';
import 'personal_info_page.dart';
import 'employment_info_page.dart';
import 'mutation_history_page.dart';
import 'warning_history_page.dart';
import 'user_files_page.dart';
import 'change_password_page.dart';
import 'change_pin_page.dart';

class ProfilPage extends StatefulWidget {
  const ProfilPage({super.key});

  @override
  State<ProfilPage> createState() => _ProfilPageState();
}

class _ProfilPageState extends State<ProfilPage> {
  final ApiService _apiService = ApiService();
  bool _isUploading = false;

  @override
  void initState() {
    super.initState();
    // Refresh user data when entering profile
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<AuthProvider>(context, listen: false).loadUser();
    });
  }

  Future<void> _pickAndUploadImage() async {
    final picker = ImagePicker();
    final XFile? image = await picker.pickImage(
      source: ImageSource.gallery,
      imageQuality: 50,
    );

    if (image == null) return;

    setState(() => _isUploading = true);

    try {
      final response = await _apiService.uploadPhotoProfile(File(image.path));
      final responseData = jsonDecode(response.body);

      if (response.statusCode == 200) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text("Foto profil berhasil diperbarui")),
          );
          // Refresh user data in provider
          Provider.of<AuthProvider>(context, listen: false).loadUser();
        }
      } else {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text("Gagal mengunggah foto: ${responseData['message'] ?? 'Unknown error'}")),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text("Terjadi kesalahan: $e")),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isUploading = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);
    final user = authProvider.user;
    final cs = Theme.of(context).colorScheme;

    return Scaffold(
      body: Column(
        children: [
          // Header hijau + foto
          Container(
            width: double.infinity,
            color: cs.primary,
            padding: const EdgeInsets.symmetric(vertical: 20, horizontal: 16),
            child: Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        "Profil",
                        style: TextStyle(
                            color: Colors.white,
                            fontSize: 16,
                            fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 12),
                      Text(
                        user != null ? user['name'] : "Nama User",
                        style: const TextStyle(
                            color: Colors.white,
                            fontSize: 18,
                            fontWeight: FontWeight.w600),
                      ),
                      Text(
                        user != null ? user['employee_id'] ?? "-" : "NIP",
                        style: const TextStyle(color: Colors.white70, fontSize: 14),
                      ),
                    ],
                  ),
                ),
                Stack(
                  alignment: Alignment.center,
                  children: [
                    GestureDetector(
                      onTap: _isUploading ? null : _pickAndUploadImage,
                      child: CircleAvatar(
                        radius: 35,
                        backgroundColor: Colors.white.withOpacity(0.2),
                        backgroundImage: user != null && user['photo_profile'] != null
                            ? NetworkImage(user['photo_profile'])
                            : const AssetImage("assets/images/profile.jpg") as ImageProvider,
                      ),
                    ),
                    if (_isUploading)
                      const CircularProgressIndicator(color: Colors.white),
                    Positioned(
                      bottom: 0,
                      right: 0,
                      child: Container(
                        padding: const EdgeInsets.all(4),
                        decoration: BoxDecoration(
                          color: cs.secondary,
                          shape: BoxShape.circle,
                          border: Border.all(color: cs.primary, width: 2),
                        ),
                        child: const Icon(
                          Icons.camera_alt,
                          size: 14,
                          color: Colors.white,
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),

          // List menu profil
          Expanded(
            child: ListView(
              children: const [
                _ProfilMenuItem(
                  title: "Informasi personal",
                  icon: Icons.people,
                ),
                _ProfilMenuItem(
                  title: "Informasi kepegawaian",
                  icon: Icons.business,
                ),
                _ProfilMenuItem(
                  title: "Riwayat mutasi",
                  icon: Icons.credit_card,
                ),
                _ProfilMenuItem(
                  title: "Riwayat SP",
                  icon: Icons.warning,
                ),
                _ProfilMenuItem(
                  title: "File saya",
                  icon: Icons.insert_drive_file,
                ),
                _ProfilMenuItem(
                  title: "Ubah password",
                  icon: Icons.password,
                ),
                _ProfilMenuItem(
                  title: "Ubah PIN",
                  icon: Icons.pin,
                ),
              ],
            ),
          )
        ],
      ),
    );
  }
}

class _ProfilMenuItem extends StatelessWidget {
  final String title;
  final IconData icon;

  const _ProfilMenuItem({required this.title, required this.icon});

  @override
  Widget build(BuildContext context) {
    final cs = Theme.of(context).colorScheme;
    return ListTile(
      leading: Icon(icon, color: cs.primary),
      title: Text(title),
      onTap: () {
        if (title == "Informasi personal") {
          Navigator.push(context, MaterialPageRoute(builder: (_) => const PersonalInfoPage()));
        } else if (title == "Informasi kepegawaian") {
          Navigator.push(context, MaterialPageRoute(builder: (_) => const EmploymentInfoPage()));
        } else if (title == "Riwayat mutasi") {
          Navigator.push(context, MaterialPageRoute(builder: (_) => const MutationHistoryPage()));
        } else if (title == "Riwayat SP") {
          Navigator.push(context, MaterialPageRoute(builder: (_) => const WarningHistoryPage()));
        } else if (title == "File saya") {
          Navigator.push(context, MaterialPageRoute(builder: (_) => const UserFilesPage()));
        } else if (title == "Ubah password") {
          Navigator.push(context, MaterialPageRoute(builder: (_) => const ChangePasswordPage()));
        } else if (title == "Ubah PIN") {
          Navigator.push(context, MaterialPageRoute(builder: (_) => const ChangePinPage()));
        }
      },
    );
  }
}
