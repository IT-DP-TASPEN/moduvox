import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import 'home_page.dart';

class SetupPinPage extends StatefulWidget {
  const SetupPinPage({super.key});

  @override
  State<SetupPinPage> createState() => _SetupPinPageState();
}

class _SetupPinPageState extends State<SetupPinPage> {
  String pin = "";
  String confirmPin = "";
  bool isConfirming = false;

  void _onNumberTap(String number) {
    if (isConfirming) {
      if (confirmPin.length < 6) {
        setState(() {
          confirmPin += number;
        });
        if (confirmPin.length == 6) {
          _handleSetPin();
        }
      }
    } else {
      if (pin.length < 6) {
        setState(() {
          pin += number;
        });
        if (pin.length == 6) {
          setState(() {
            isConfirming = true;
          });
        }
      }
    }
  }

  void _handleSetPin() async {
    if (pin != confirmPin) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text("PIN tidak cocok, ulangi lagi!")),
      );
      setState(() {
        pin = "";
        confirmPin = "";
        isConfirming = false;
      });
      return;
    }

    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    final success = await authProvider.setPin(pin);

    if (success) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text("PIN berhasil disimpan!")),
        );
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (context) => const HomePage()),
        );
      }
    } else {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text("Gagal menyimpan PIN. Coba lagi.")),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);
    final currentInput = isConfirming ? confirmPin : pin;
    final cs = Theme.of(context).colorScheme;

    return Scaffold(
      appBar: AppBar(
        title: const Text("Atur PIN Keamanan"),
      ),
      body: Column(
        children: [
          const SizedBox(height: 40),
          Icon(
            isConfirming ? Icons.verified_user : Icons.lock_outline,
            size: 64,
            color: cs.primary,
          ),
          const SizedBox(height: 20),
          Text(
            isConfirming ? "Konfirmasi PIN Anda" : "Buat PIN 6 Digit Baru",
            style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
          ),
          const Padding(
            padding: EdgeInsets.symmetric(horizontal: 40, vertical: 10),
            child: Text(
              "PIN ini akan digunakan setiap kali Anda melakukan absensi masuk atau keluar.",
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.black54),
            ),
          ),
          const SizedBox(height: 30),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: List.generate(
              6,
              (index) => Container(
                margin: const EdgeInsets.all(8),
                width: 16,
                height: 16,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: index < currentInput.length ? cs.primary : Colors.grey[300],
                  border: Border.all(color: cs.primary),
                ),
              ),
            ),
          ),
          const SizedBox(height: 40),
          if (authProvider.isLoading)
            const CircularProgressIndicator()
          else
            Expanded(
              child: GridView.builder(
                padding: const EdgeInsets.symmetric(horizontal: 60),
                itemCount: 12,
                gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: 3,
                  childAspectRatio: 1.3,
                ),
                itemBuilder: (context, index) {
                  if (index == 9) {
                    return TextButton(
                      onPressed: () {
                        setState(() {
                          pin = "";
                          confirmPin = "";
                          isConfirming = false;
                        });
                      },
                      child: const Text("Ulang"),
                    );
                  } else if (index == 11) {
                    return IconButton(
                      icon: Icon(Icons.backspace_outlined, color: cs.primary),
                      onPressed: () {
                        if (isConfirming) {
                          if (confirmPin.isNotEmpty) {
                            setState(() {
                              confirmPin = confirmPin.substring(0, confirmPin.length - 1);
                            });
                          } else {
                            setState(() {
                              isConfirming = false;
                            });
                          }
                        } else {
                          if (pin.isNotEmpty) {
                            setState(() {
                              pin = pin.substring(0, pin.length - 1);
                            });
                          }
                        }
                      },
                    );
                  } else {
                    final number = (index == 10) ? "0" : "${index + 1}";
                    return InkWell(
                      onTap: () => _onNumberTap(number),
                      borderRadius: BorderRadius.circular(50),
                      child: Center(
                        child: Text(
                          number,
                          style: const TextStyle(
                            fontSize: 28,
                            fontWeight: FontWeight.w600,
                          ),
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
