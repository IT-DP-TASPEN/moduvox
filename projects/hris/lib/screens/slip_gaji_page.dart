import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:path_provider/path_provider.dart';
import 'package:open_filex/open_filex.dart';
import '../services/api_service.dart';

class SlipGajiPage extends StatefulWidget {
  const SlipGajiPage({super.key});

  @override
  State<SlipGajiPage> createState() => _SlipGajiPageState();
}

class _SlipGajiPageState extends State<SlipGajiPage> {
  final ApiService _apiService = ApiService();
  List<dynamic> _salaries = [];
  bool _isLoading = true;
  int _selectedMonth = DateTime.now().month;
  int _selectedYear = DateTime.now().year;

  @override
  void initState() {
    super.initState();
    _loadSalaries();
  }

  Future<void> _loadSalaries() async {
    setState(() => _isLoading = true);
    try {
      final resp = await _apiService.getSalaries(month: _selectedMonth, year: _selectedYear);
      if (resp.statusCode == 200) {
        setState(() {
          _salaries = jsonDecode(resp.body);
          _isLoading = false;
        });
      } else {
        setState(() {
          _salaries = [];
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _salaries = [];
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text("Slip Gaji", style: TextStyle(fontWeight: FontWeight.bold)),
        backgroundColor: const Color(0xFF004A99),
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: Column(
        children: [
          _buildFilterRow(),
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _salaries.isEmpty
                    ? const Center(child: Text("Belum ada data slip gaji untuk bulan ini."))
                    : ListView.builder(
                        padding: const EdgeInsets.all(16),
                        itemCount: _salaries.length,
                        itemBuilder: (context, index) {
                          final s = _salaries[index];
                          final monthName = DateFormat('MMMM').format(DateTime(s['year'], s['month']));
                          return Card(
                            margin: const EdgeInsets.only(bottom: 12),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                            child: ListTile(
                              contentPadding: const EdgeInsets.all(16),
                              leading: Container(
                                padding: const EdgeInsets.all(10),
                                decoration: BoxDecoration(color: Colors.blue.shade50, borderRadius: BorderRadius.circular(12)),
                                child: const Icon(Icons.receipt_long, color: Color(0xFF004A99)),
                              ),
                              title: Text("Slip Gaji $monthName ${s['year']}", style: const TextStyle(fontWeight: FontWeight.bold)),
                              subtitle: Text("ID: ${s['id']}"),
                              trailing: const Icon(Icons.chevron_right),
                              onTap: () => _showSlipDetail(s),
                            ),
                          );
                        },
                      ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterRow() {
    final List<String> months = [
      'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
      'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    int currentYear = DateTime.now().year;
    List<int> years = List.generate(5, (index) => currentYear - index);

    return Container(
      color: Colors.white,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      child: Row(
        children: [
          Expanded(
            flex: 2,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 12),
              decoration: BoxDecoration(
                border: Border.all(color: Colors.grey.shade300),
                borderRadius: BorderRadius.circular(8),
              ),
              child: DropdownButtonHideUnderline(
                child: DropdownButton<int>(
                  isExpanded: true,
                  value: _selectedMonth,
                  items: List.generate(12, (index) {
                    return DropdownMenuItem(
                      value: index + 1,
                      child: Text(months[index]),
                    );
                  }),
                  onChanged: (val) {
                    if (val != null) {
                      setState(() => _selectedMonth = val);
                      _loadSalaries();
                    }
                  },
                ),
              ),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            flex: 1,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 12),
              decoration: BoxDecoration(
                border: Border.all(color: Colors.grey.shade300),
                borderRadius: BorderRadius.circular(8),
              ),
              child: DropdownButtonHideUnderline(
                child: DropdownButton<int>(
                  isExpanded: true,
                  value: _selectedYear,
                  items: years.map((year) {
                    return DropdownMenuItem(
                      value: year,
                      child: Text(year.toString()),
                    );
                  }).toList(),
                  onChanged: (val) {
                    if (val != null) {
                      setState(() => _selectedYear = val);
                      _loadSalaries();
                    }
                  },
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  void _showSlipDetail(Map<String, dynamic> s) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => DraggableScrollableSheet(
        initialChildSize: 0.9,
        minChildSize: 0.5,
        maxChildSize: 0.95,
        builder: (_, controller) => Container(
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
          ),
          child: Column(
            children: [
              const SizedBox(height: 12),
              Container(width: 40, height: 4, decoration: BoxDecoration(color: Colors.grey[300], borderRadius: BorderRadius.circular(2))),
              Expanded(
                child: ListView(
                  controller: controller,
                  padding: const EdgeInsets.all(24),
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Image.network(
                          "https://bankdptaspen.co.id/wp-content/uploads/2024/01/Logo-Bank-DP-Taspen-Version-New.png",
                          height: 40,
                          errorBuilder: (context, error, stackTrace) => const Text("PT BPR DP TASPEN", style: TextStyle(fontWeight: FontWeight.bold)),
                        ),
                        const Column(
                          crossAxisAlignment: CrossAxisAlignment.end,
                          children: [
                            Text("Slip Gaji", style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                            Text("Bank DP Taspen", style: TextStyle(fontSize: 12, color: Colors.grey)),
                          ],
                        )
                      ],
                    ),
                    const Divider(height: 40),
                    _buildInfoRow("Bulan", "${DateFormat('MMMM').format(DateTime(s['year'], s['month']))} ${s['year']}"),
                    const SizedBox(height: 20),
                    
                    const Text("PENDAPATAN", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Color(0xFF004A99))),
                    const SizedBox(height: 8),
                    _buildAmountRow("Gaji Pokok", s['basic_salary']),
                    _buildAmountRow("Uang Lembur", s['overtime_pay']),
                    _buildAmountRow("Uang Makan Lembur", s['overtime_meal_pay']),
                    _buildAmountRow("Tunjangan Pajak", s['tax_allowance']),
                    _buildAmountRow("Tunjangan Jabatan", s['position_allowance']),
                    _buildAmountRow("Tunjangan Kinerja", s['performance_allowance']),
                    // Dynamic Earnings from global allowances
                    ..._getDynamicComponents(s, 'earning').map((c) => _buildAmountRow(c['name'], c['amount'])),
                    const Divider(),
                    _buildAmountRow("Total Pendapatan", s['total_earnings'], isBold: true),
                    
                    const SizedBox(height: 24),
                    const Text("POTONGAN", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Colors.red)),
                    const SizedBox(height: 8),
                    _buildAmountRow("Pajak (PPh 21)", s['income_tax']),
                    // Dynamic Deductions from global allowances
                    ..._getDynamicComponents(s, 'deduction').map((c) => _buildAmountRow(c['name'], c['amount'])),
                    const Divider(),
                    _buildAmountRow("Total Potongan", s['total_deductions'], isBold: true),

                    const SizedBox(height: 24),
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(color: Colors.blue.shade50, borderRadius: BorderRadius.circular(16)),
                      child: _buildAmountRow("TAKE HOME PAY", s['net_salary'], isBold: true, color: const Color(0xFF004A99)),
                    ),

                    const SizedBox(height: 32),
                    // Dynamic Company Paid (Non-THP)
                    if (_getDynamicComponents(s, 'company_paid').isNotEmpty) ...[
                      const Text("PENDAPATAN NON-THP (Ditanggung Perusahaan)", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Colors.grey)),
                      const SizedBox(height: 8),
                      ..._getDynamicComponents(s, 'company_paid').map((c) => _buildAmountRow(c['name'], c['amount'], isSmall: true)),
                      const Divider(),
                      _buildAmountRow("Total Gross", s['total_gross'], isBold: true, isSmall: true),
                    ],

                    const SizedBox(height: 40),
                    ElevatedButton.icon(
                      onPressed: () => _promptPin(s['id']),
                      icon: const Icon(Icons.download),
                      label: const Text("Download PDF"),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF004A99),
                        foregroundColor: Colors.white,
                        minimumSize: const Size(double.infinity, 50),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                      ),
                    ),
                    const SizedBox(height: 20),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _promptPin(int id) {
    final TextEditingController pinController = TextEditingController();
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text("Keamanan PIN", style: TextStyle(fontWeight: FontWeight.bold)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text("Masukkan 6 digit PIN Anda untuk mengenkripsi file PDF slip gaji."),
            const SizedBox(height: 16),
            TextField(
              controller: pinController,
              keyboardType: TextInputType.number,
              obscureText: true,
              maxLength: 6,
              autofocus: true,
              decoration: InputDecoration(
                hintText: "PIN Anda",
                prefixIcon: const Icon(Icons.lock_outline),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text("Batal")),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF004A99),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            onPressed: () {
              final pin = pinController.text;
              if (pin.length == 6) {
                Navigator.pop(context);
                _downloadPdf(id, pin: pin);
              } else {
                ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text("PIN harus 6 digit")));
              }
            },
            child: const Text("Download"),
          ),
        ],
      ),
    );
  }

  Future<void> _downloadPdf(int id, {String? pin}) async {
    try {
      // Jika PIN tidak diberikan, minta PIN dulu
      if (pin == null) {
        _promptPin(id);
        return;
      }

      // Verifikasi PIN via API terlebih dahulu agar tidak salah enkripsi
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text("Memverifikasi PIN..."), duration: Duration(milliseconds: 500)),
      );

      final verifyResp = await _apiService.verifyPin(pin);
      if (verifyResp.statusCode != 200) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text("PIN yang Anda masukkan salah.")),
        );
        return;
      }

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text("Mengunduh slip gaji..."), duration: Duration(seconds: 1)),
      );

      final resp = await _apiService.get('/salaries/$id/download-slip?password=$pin');
      
      if (resp.statusCode == 200) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: const Text("Slip gaji berhasil diunduh. Password PDF = PIN yang Anda masukkan."),
            duration: const Duration(seconds: 4),
            backgroundColor: Colors.green.shade700,
          ),
        );
        // Ambil binary PDF dari response
        final bytes = resp.bodyBytes;
        
        // Dapatkan direktori penyimpanan internal
        final dir = await getApplicationDocumentsDirectory();
        final file = File('${dir.path}/SlipGaji_$id.pdf');
        
        // Simpan file
        await file.writeAsBytes(bytes);
        
        // Buka file PDF
        final result = await OpenFilex.open(file.path);
        
        if (result.type != ResultType.done) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text("Tidak ada aplikasi untuk membuka PDF: ${result.message}")),
          );
        }
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text("Gagal mengunduh slip gaji (Akses Ditolak).")),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text("Terjadi kesalahan koneksi.")),
      );
    }
  }

  List<Map<String, dynamic>> _getDynamicComponents(Map<String, dynamic> salary, String category) {
    try {
      final raw = salary['dynamic_components'];
      if (raw == null) return [];
      
      List<dynamic> components;
      if (raw is String) {
        components = jsonDecode(raw);
      } else if (raw is List) {
        components = raw;
      } else {
        return [];
      }
      
      return components
          .where((c) => c['category'] == category)
          .map<Map<String, dynamic>>((c) => {'name': c['name'], 'amount': c['amount']})
          .toList();
    } catch (e) {
      return [];
    }
  }

  Widget _buildInfoRow(String label, String value) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label, style: const TextStyle(color: Colors.grey, fontSize: 13)),
        Text(value, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
      ],
    );
  }

  Widget _buildAmountRow(String label, dynamic value, {bool isBold = false, bool isSmall = false, Color? color}) {
    final formatter = NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);
    final amount = double.tryParse(value.toString()) ?? 0;
    
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: TextStyle(fontSize: isSmall ? 11 : 13, fontWeight: isBold ? FontWeight.bold : FontWeight.normal)),
          Text(
            formatter.format(amount),
            style: TextStyle(
              fontSize: isSmall ? 11 : 14, 
              fontWeight: isBold ? FontWeight.bold : FontWeight.w600,
              color: color
            )
          ),
        ],
      ),
    );
  }
}
