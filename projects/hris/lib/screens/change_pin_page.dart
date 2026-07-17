import 'package:flutter/material.dart';
import 'dart:convert';
import '../services/api_service.dart';

class ChangePinPage extends StatefulWidget {
  const ChangePinPage({super.key});

  @override
  State<ChangePinPage> createState() => _ChangePinPageState();
}

class _ChangePinPageState extends State<ChangePinPage> {
  final _oldPinController = TextEditingController();
  final _newPinController = TextEditingController();
  final _confirmPinController = TextEditingController();
  bool _isLoading = false;

  void _submit() async {
    if (_newPinController.text != _confirmPinController.text) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text("Konfirmasi PIN tidak cocok")));
      return;
    }

    setState(() => _isLoading = true);
    try {
      final result = await ApiService().post('/change-pin', {
        'old_pin': _oldPinController.text,
        'new_pin': _newPinController.text,
        'new_pin_confirmation': _confirmPinController.text,
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
      appBar: AppBar(title: const Text("Ubah PIN")),
      body: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            TextField(controller: _oldPinController, obscureText: true, keyboardType: TextInputType.number, maxLength: 6, decoration: const InputDecoration(labelText: "PIN Lama")),
            const SizedBox(height: 12),
            TextField(controller: _newPinController, obscureText: true, keyboardType: TextInputType.number, maxLength: 6, decoration: const InputDecoration(labelText: "PIN Baru")),
            const SizedBox(height: 12),
            TextField(controller: _confirmPinController, obscureText: true, keyboardType: TextInputType.number, maxLength: 6, decoration: const InputDecoration(labelText: "Konfirmasi PIN Baru")),
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              child: FilledButton(
                onPressed: _isLoading ? null : _submit,
                child: _isLoading
                    ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                    : Text("Update PIN", style: TextStyle(color: cs.onPrimary)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
