import 'dart:convert';
import 'package:flutter/material.dart';
import '../services/api_service.dart';

class KpiFormPage extends StatefulWidget {
  final Map<String, dynamic> staff;
  final int month;
  final int year;
  final String formattedPeriod;
  final List<dynamic> indicators;

  const KpiFormPage({
    super.key,
    required this.staff,
    required this.month,
    required this.year,
    required this.formattedPeriod,
    required this.indicators,
  });

  @override
  State<KpiFormPage> createState() => _KpiFormPageState();
}

class _KpiFormPageState extends State<KpiFormPage> {
  final ApiService _apiService = ApiService();
  final TextEditingController _notesController = TextEditingController();
  
  final Map<String, double> _scores = {};
  bool _isSaving = false;

  @override
  void initState() {
    super.initState();
    
    // Initialize scores with 5 for all active indicators
    for (var indicator in widget.indicators) {
      _scores[indicator['slug']] = 5;
    }

    // Pre-fill if already rated
    if (widget.staff['kpi'] != null && widget.staff['kpi']['indicators'] != null) {
      final existingIndicators = widget.staff['kpi']['indicators'];
      existingIndicators.forEach((key, value) {
        if (_scores.containsKey(key)) {
          _scores[key] = double.tryParse(value.toString()) ?? 5;
        }
      });
      _notesController.text = widget.staff['kpi']['notes'] ?? '';
    }
  }

  Future<void> _saveKpi() async {
    setState(() => _isSaving = true);
    try {
      final data = {
        'user_id': widget.staff['id'],
        'month': widget.month,
        'year': widget.year,
        'indicators': _scores,
        'notes': _notesController.text,
      };

      final resp = await _apiService.saveKpi(data);
      if (resp.statusCode == 200) {
        if (mounted) {
          Navigator.pop(context, true);
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text("KPI berhasil disimpan")),
          );
        }
      } else {
        final error = jsonDecode(resp.body);
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(error['message'] ?? "Gagal menyimpan KPI")),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text("Terjadi kesalahan koneksi")),
        );
      }
    } finally {
      if (mounted) setState(() => _isSaving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF1F5F9),
      appBar: AppBar(
        title: const Text("Penilaian KPI", style: TextStyle(fontWeight: FontWeight.bold)),
        backgroundColor: const Color(0xFF004A99),
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Staff Info
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(24),
                boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 10, offset: const Offset(0, 4))],
              ),
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 25,
                    backgroundColor: const Color(0xFF004A99).withOpacity(0.1),
                    child: Text(widget.staff['name'][0], style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF004A99))),
                  ),
                  const SizedBox(width: 15),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(widget.staff['name'], style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                        Text("${widget.staff['employee_id']} • ${widget.staff['division']}", style: const TextStyle(color: Colors.grey, fontSize: 12)),
                        const SizedBox(height: 4),
                        Text("Periode: ${widget.formattedPeriod}", style: const TextStyle(color: Color(0xFF004A99), fontWeight: FontWeight.bold, fontSize: 11)),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 25),
            const Text("Indikator Penilaian", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: Color(0xFF1E293B))),
            const Text("Berikan nilai 1 - 10 untuk setiap kriteria", style: TextStyle(color: Colors.grey, fontSize: 12)),
            const SizedBox(height: 15),

            // Dynamic Indicators
            ...widget.indicators.map((indicator) {
              return _buildIndicatorItem(
                indicator['label'], 
                indicator['slug'], 
                indicator['description'] ?? ''
              );
            }).toList(),

            const SizedBox(height: 10),
            const Text("Catatan Performa", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
            const SizedBox(height: 8),
            TextField(
              controller: _notesController,
              maxLines: 3,
              decoration: InputDecoration(
                hintText: "Masukkan catatan atau evaluasi tambahan...",
                fillColor: Colors.white,
                filled: true,
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide.none),
              ),
            ),
            const SizedBox(height: 30),

            SizedBox(
              width: double.infinity,
              height: 55,
              child: ElevatedButton(
                onPressed: _isSaving ? null : _saveKpi,
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF004A99),
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                  elevation: 0,
                ),
                child: _isSaving 
                  ? const CircularProgressIndicator(color: Colors.white)
                  : const Text("SIMPAN PENILAIAN", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
              ),
            ),
            const SizedBox(height: 40),
          ],
        ),
      ),
    );
  }

  Widget _buildIndicatorItem(String title, String key, String desc) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: const Color(0xFF004A99).withOpacity(0.1),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  _scores[key]!.toInt().toString(),
                  style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF004A99), fontSize: 16),
                ),
              ),
            ],
          ),
          if (desc.isNotEmpty) Text(desc, style: const TextStyle(color: Colors.grey, fontSize: 11)),
          const SizedBox(height: 8),
          Slider(
            value: _scores[key]!,
            min: 1,
            max: 10,
            divisions: 9,
            activeColor: const Color(0xFF004A99),
            inactiveColor: Colors.grey.shade100,
            onChanged: (val) {
              setState(() {
                _scores[key] = val;
              });
            },
          ),
        ],
      ),
    );
  }
}
