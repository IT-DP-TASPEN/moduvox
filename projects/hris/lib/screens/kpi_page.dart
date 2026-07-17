import 'dart:convert';
import 'package:flutter/material.dart';
import '../services/api_service.dart';
import 'kpi_form_page.dart';

class KpiPage extends StatefulWidget {
  const KpiPage({super.key});

  @override
  State<KpiPage> createState() => _KpiPageState();
}

class _KpiPageState extends State<KpiPage> {
  final ApiService _apiService = ApiService();
  Map<String, dynamic>? _data;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    try {
      final resp = await _apiService.getKpis();
      if (resp.statusCode == 200) {
        setState(() {
          _data = jsonDecode(resp.body);
          _isLoading = false;
        });
      } else {
        setState(() => _isLoading = false);
      }
    } catch (e) {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF1F5F9),
      appBar: AppBar(
        title: const Text("Manajemen KPI", style: TextStyle(fontWeight: FontWeight.bold)),
        backgroundColor: const Color(0xFF004A99),
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadData,
          )
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadData,
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Period Header
                    if (_data != null)
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(16),
                        margin: const EdgeInsets.only(bottom: 20),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: Colors.white),
                        ),
                        child: Row(
                          children: [
                            const Icon(Icons.calendar_month, color: Color(0xFF004A99)),
                            const SizedBox(width: 12),
                            Text(
                              "Periode: ${_data!['period']['formatted']}",
                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                            ),
                          ],
                        ),
                      ),

                    // Own KPI Section
                    const Text("KPI SAYA", style: TextStyle(fontWeight: FontWeight.bold, color: Colors.grey, fontSize: 12, letterSpacing: 1.2)),
                    const SizedBox(height: 10),
                    _buildOwnKpiCard(),

                    const SizedBox(height: 25),

                    // Staff KPI Section (If Approver)
                    if (_data != null && _data!['is_approver'] == true) ...[
                      const Text("PENILAIAN STAFF", style: TextStyle(fontWeight: FontWeight.bold, color: Colors.grey, fontSize: 12, letterSpacing: 1.2)),
                      const SizedBox(height: 10),
                      ...(_data!['staff_list'] as List).map((s) => _buildStaffKpiCard(s)).toList(),
                    ],
                    const SizedBox(height: 30),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _buildOwnKpiCard() {
    final ownKpi = _data?['own_kpi'];
    if (ownKpi == null) {
      return Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(24)),
        child: const Center(child: Text("KPI Anda belum tersedia untuk periode ini.")),
      );
    }

    final score = double.tryParse(ownKpi['score'].toString()) ?? 0;
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: const Color(0xFF004A99),
        borderRadius: BorderRadius.circular(24),
        boxShadow: [BoxShadow(color: const Color(0xFF004A99).withOpacity(0.3), blurRadius: 15, offset: const Offset(0, 8))],
      ),
      child: Column(
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text("Skor Akhir", style: TextStyle(color: Colors.white70, fontSize: 14)),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(color: Colors.white24, borderRadius: BorderRadius.circular(10)),
                child: Text("GRADE ${ownKpi['grade']}", style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12)),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Text(score.toStringAsFixed(2), style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 48)),
          const Divider(color: Colors.white24, height: 30),
          Row(
            children: [
              const Icon(Icons.info_outline, color: Colors.white70, size: 16),
              const SizedBox(width: 8),
              Expanded(child: Text(ownKpi['notes'] ?? "Tidak ada catatan.", style: const TextStyle(color: Colors.white70, fontSize: 12))),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildStaffKpiCard(Map<String, dynamic> staff) {
    final bool isRated = staff['is_rated'];
    final kpi = staff['kpi'];

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Theme(
        data: Theme.of(context).copyWith(dividerColor: Colors.transparent),
        child: ExpansionTile(
          tilePadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          leading: CircleAvatar(
            backgroundColor: isRated ? Colors.green.withOpacity(0.1) : Colors.orange.withOpacity(0.1),
            child: Icon(isRated ? Icons.check_circle : Icons.pending, color: isRated ? Colors.green : Colors.orange),
          ),
          title: Text(staff['name'], style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
          subtitle: Text(staff['employee_id'], style: const TextStyle(fontSize: 11, color: Colors.grey)),
          trailing: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              if (isRated) 
                Text(
                  double.parse(kpi['score'].toString()).toStringAsFixed(1),
                  style: const TextStyle(fontWeight: FontWeight.w900, color: Color(0xFF004A99), fontSize: 18),
                )
              else 
                const Text("Belum Dinilai", style: TextStyle(color: Colors.orange, fontWeight: FontWeight.bold, fontSize: 10)),
              
              if (isRated)
                Text("GRADE ${kpi['grade']}", style: const TextStyle(color: Colors.grey, fontSize: 9, fontWeight: FontWeight.bold)),
            ],
          ),
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
              child: Column(
                children: [
                  const Divider(),
                  if (isRated && kpi['indicators'] != null) ...[
                    _buildIndicatorRow("Kedisiplinan", kpi['indicators']['kedisiplinan']),
                    _buildIndicatorRow("Kualitas Kerja", kpi['indicators']['kualitas_kerja']),
                    _buildIndicatorRow("Kerjasama", kpi['indicators']['kerjasama']),
                    _buildIndicatorRow("Tanggung Jawab", kpi['indicators']['tanggung_jawab']),
                    _buildIndicatorRow("Sikap & Etika", kpi['indicators']['sikap_etika']),
                    const SizedBox(height: 10),
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(color: Colors.grey.shade50, borderRadius: BorderRadius.circular(12)),
                      child: Text("Catatan: ${kpi['notes'] ?? '-'}", style: const TextStyle(fontSize: 11, fontStyle: FontStyle.italic)),
                    ),
                  ],
                  const SizedBox(height: 15),
                  SizedBox(
                    width: double.infinity,
                    child: OutlinedButton.icon(
                      onPressed: () async {
                        final result = await Navigator.push(
                          context, 
                          MaterialPageRoute(builder: (_) => KpiFormPage(
                            staff: staff,
                            month: _data!['period']['month'],
                            year: _data!['period']['year'],
                            formattedPeriod: _data!['period']['formatted'],
                            indicators: _data!['indicators'] as List,
                          ))
                        );
                        if (result == true) _loadData();
                      },
                      icon: Icon(isRated ? Icons.edit : Icons.add_task, size: 18),
                      label: Text(isRated ? "REVISI NILAI" : "BERIKAN NILAI"),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: const Color(0xFF004A99),
                        side: const BorderSide(color: Color(0xFF004A99)),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ),
                ],
              ),
            )
          ],
        ),
      ),
    );
  }

  Widget _buildIndicatorRow(String label, dynamic value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(fontSize: 12, color: Colors.grey)),
          Text("$value / 10", style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
        ],
      ),
    );
  }
}
