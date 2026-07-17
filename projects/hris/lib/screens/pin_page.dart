import 'package:flutter/material.dart';
import 'absen_masuk_page.dart';
import 'absen_keluar_page.dart';
import 'home_page.dart';
import 'dart:convert';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';

class PinPage extends StatefulWidget {
  final String nextPage;

  const PinPage({super.key, required this.nextPage});

  @override
  State<PinPage> createState() => _PinPageState();
}

class _PinPageState extends State<PinPage> {
  String pin = "";
  bool _isVerifying = false;

  void _onNumberTap(String number) {
    if (_isVerifying) return;
    if (pin.length < 6) {
      setState(() {
        pin += number;
      });
      if (pin.length == 6) {
        _checkPin();
      }
    }
  }

  Future<void> _checkPin() async {
    setState(() => _isVerifying = true);
    final authProvider = Provider.of<AuthProvider>(context, listen: false);

    try {
      // If token is null, we must login with PIN first
      if (authProvider.token == null) {
        final result = await authProvider.loginWithPin(pin);
        if (!result['success']) {
          setState(() => pin = "");
          ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(result['message'] ?? "PIN salah")));
          return;
        }
      } else {
        // Just verify PIN if already logged in
        final resp = await ApiService().verifyPin(pin);
        if (resp.statusCode != 200) {
          setState(() => pin = "");
          ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text("PIN salah")));
          return;
        }
      }

      if (!mounted) return;

      if (widget.nextPage == "masuk") {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (context) => const AbsenMasukPage()),
        );
      } else if (widget.nextPage == "home") {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (context) => HomePage()),
        );
      } else {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (context) => const AbsenKeluarPage()),
        );
      }
    } catch (e) {
      if (!mounted) return;
      setState(() => pin = "");
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text("Terjadi kesalahan: $e")));
    } finally {
      if (mounted) setState(() => _isVerifying = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final cs = Theme.of(context).colorScheme;
    return Scaffold(
      appBar: AppBar(
        title: const Text("Verifikasi PIN"),
      ),
      body: Column(
        children: [
          const SizedBox(height: 24),

          Icon(Icons.lock, size: 40, color: cs.primary),
          const SizedBox(height: 10),
          const Text(
            "Masukkan PIN Anda untuk melanjutkan",
            style: TextStyle(fontSize: 16),
          ),
          const SizedBox(height: 20),

          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: List.generate(
              6,
              (index) => Container(
                margin: const EdgeInsets.all(6),
                width: 20,
                height: 20,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  border: Border.all(color: cs.primary, width: 2),
                  color: index < pin.length ? cs.primary : Colors.transparent,
                ),
              ),
            ),
          ),
          const SizedBox(height: 16),

          TextButton(
            onPressed: () {
              Navigator.pop(context);
            },
            child: const Text(
              "Login dengan password",
              style: TextStyle(color: Colors.redAccent, decoration: TextDecoration.underline),
            ),
          ),
          const SizedBox(height: 20),

          // Numpad
          Expanded(
            child: GridView.builder(
              padding: const EdgeInsets.symmetric(horizontal: 50),
              itemCount: 12,
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 3,
                childAspectRatio: 1.2,
              ),
              itemBuilder: (context, index) {
                if (index == 9) {
                  return TextButton(
                    onPressed: _isVerifying
                        ? null
                        : () {
                      setState(() {
                        pin = "";
                      });
                    },
                    child: const Text("Batal"),
                  );
                } else if (index == 11) {
                  return IconButton(
                    icon: Icon(Icons.backspace, color: cs.primary),
                    onPressed: _isVerifying
                        ? null
                        : () {
                      if (pin.isNotEmpty) {
                        setState(() {
                          pin = pin.substring(0, pin.length - 1);
                        });
                      }
                    },
                  );
                } else {
                  final number = (index == 10) ? "0" : "${index + 1}";
                  return TextButton(
                    onPressed: _isVerifying ? null : () => _onNumberTap(number),
                    child: Text(
                      number,
                      style: const TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  );
                }
              },
            ),
          ),
        ],
      ),
    );
  }
}
