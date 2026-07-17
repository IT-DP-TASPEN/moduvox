import 'package:flutter/material.dart';
import 'dart:convert';
import '../services/api_service.dart';

class ChangePasswordPage extends StatefulWidget {
  const ChangePasswordPage({super.key});

  @override
  State<ChangePasswordPage> createState() => _ChangePasswordPageState();
}

class _ChangePasswordPageState extends State<ChangePasswordPage> {
  final _oldPassController = TextEditingController();
  final _newPassController = TextEditingController();
  final _confirmPassController = TextEditingController();
  bool _isLoading = false;

  void _submit() async {
    if (_newPassController.text != _confirmPassController.text) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text("Konfirmasi password tidak cocok")));
      return;
    }

    setState(() => _isLoading = true);
    try {
      final result = await ApiService().post('/change-password', {
        'old_password': _oldPassController.text,
        'new_password': _newPassController.text,
        'new_password_confirmation': _confirmPassController.text,
      });

      if (mounted) {
        final data = jsonDecode(result.body);
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(data['message'] ?? 'Berhasil')));
        if ((data['message'] ?? '').toString().toLowerCase().contains("berhasil")) Navigator.pop(context);
      }
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text("Gagal: $e")));
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final cs = Theme.of(context).colorScheme;
    return Scaffold(
      appBar: AppBar(title: const Text("Ubah Password")),
      body: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            TextField(controller: _oldPassController, obscureText: true, decoration: const InputDecoration(labelText: "Password Lama")),
            const SizedBox(height: 12),
            TextField(controller: _newPassController, obscureText: true, decoration: const InputDecoration(labelText: "Password Baru")),
            const SizedBox(height: 12),
            TextField(controller: _confirmPassController, obscureText: true, decoration: const InputDecoration(labelText: "Konfirmasi Password Baru")),
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              child: FilledButton(
                onPressed: _isLoading ? null : _submit,
                child: _isLoading
                    ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                    : Text("Update Password", style: TextStyle(color: cs.onPrimary)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
