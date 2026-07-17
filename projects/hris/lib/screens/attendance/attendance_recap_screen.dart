import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';

class AttendanceRecapScreen extends StatefulWidget {
  const AttendanceRecapScreen({super.key});

  @override
  State<AttendanceRecapScreen> createState() => _AttendanceRecapScreenState();
}

class _AttendanceRecapScreenState extends State<AttendanceRecapScreen> {
  final ApiService _apiService = ApiService();
  DateTime _startDate = DateTime.now().subtract(const Duration(days: 30));
  DateTime _endDate = DateTime.now();
  int? _selectedUserId;
  List<dynamic> _users = [];
  Map<String, dynamic>? _recapData;
  bool _isLoading = false;
  bool _isAdmin = false;

  @override
  void initState() {
    super.initState();
    _checkAdminAndLoadUsers();
    _loadRecap();
  }

  Future<void> _checkAdminAndLoadUsers() async {
    try {
      final response = await _apiService.getUser();
      if (response.statusCode == 200) {
        final userData = jsonDecode(response.body);
        setState(() {
          _isAdmin = userData['is_admin'] == true;
          _selectedUserId = userData['id'];
        });

        if (_isAdmin) {
          final usersResponse = await _apiService.getUsers();
          if (usersResponse.statusCode == 200) {
            setState(() {
              _users = jsonDecode(usersResponse.body);
            });
          }
        }
      }
    } catch (e) {
      debugPrint("Error loading user info: $e");
    }
  }

  Future<void> _loadRecap() async {
    setState(() => _isLoading = true);
    try {
      final response = await _apiService.getAttendanceRecap(
        startDate: DateFormat('yyyy-MM-dd').format(_startDate),
        endDate: DateFormat('yyyy-MM-dd').format(_endDate),
        userId: _selectedUserId,
      );

      if (response.statusCode == 200) {
        setState(() {
          _recapData = jsonDecode(response.body);
        });
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal mengambil data rekap (Error: ${response.statusCode})')),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: $e')),
      );
    } finally {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _selectDate(BuildContext context, bool isStart) async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: isStart ? _startDate : _endDate,
      firstDate: DateTime(2020),
      lastDate: DateTime.now(),
    );
    if (picked != null) {
      setState(() {
        if (isStart) {
          _startDate = picked;
        } else {
          _endDate = picked;
        }
      });
      _loadRecap();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF1F5F9),
      appBar: AppBar(
        title: const Text('Rekap Absensi', style: TextStyle(fontWeight: FontWeight.bold)),
        backgroundColor: const Color(0xFF004A99), // Taspen Blue
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadRecap,
          )
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: Color(0xFF004A99)))
          : CustomScrollView(
              slivers: [
                SliverToBoxAdapter(child: _buildHeaderFilter()),
                if (_recapData != null) ...[
                  SliverToBoxAdapter(child: _buildSummaryCards()),
                  SliverPadding(
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                    sliver: SliverList(
                      delegate: SliverChildListDelegate([
                        const Text("Detail Laporan", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: Color(0xFF1E293B))),
                        const SizedBox(height: 12),
                        _buildRecapTile(
                          'Total Hari Kerja',
                          _recapData!['total_working_days'],
                          Icons.calendar_month,
                          Colors.blue,
                          onTap: () => _showDayDetails("Hari Kerja"),
                        ),
                        _buildRecapTile(
                          'Total Kehadiran',
                          _recapData!['total_attendance'],
                          Icons.how_to_reg,
                          Colors.green,
                          onTap: () => _showDayDetails("Kehadiran"),
                        ),
                        _buildRecapTile('Izin', _recapData!['permit'], Icons.assignment_turned_in, Colors.orange),
                        _buildRecapTile('Cuti', _recapData!['leave'], Icons.beach_access, Colors.purple),
                        _buildRecapTile('Tugas Luar', _recapData!['outside_duty'], Icons.location_on, Colors.indigo),
                        _buildRecapTile(
                          'Terlambat',
                          _recapData!['late'],
                          Icons.timer_off,
                          Colors.red,
                          onTap: () => _showDayDetails("Terlambat"),
                        ),
                        _buildRecapTile('Lembur', _recapData!['overtime'], Icons.more_time, Colors.teal),
                        _buildRecapTile('Alpa', _recapData!['absent'], Icons.warning_amber_rounded, Colors.redAccent),
                        const SizedBox(height: 40),
                      ]),
                    ),
                  ),
                ],
              ],
            ),
    );
  }

  Widget _buildHeaderFilter() {
    return Container(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
      decoration: const BoxDecoration(
        color: Color(0xFF004A99),
        borderRadius: BorderRadius.only(
          bottomLeft: Radius.circular(32),
          bottomRight: Radius.circular(32),
        ),
      ),
      child: Column(
        children: [
          if (_isAdmin && _users.isNotEmpty) ...[
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12),
              decoration: BoxDecoration(color: Colors.white.withOpacity(0.15), borderRadius: BorderRadius.circular(12)),
              child: DropdownButtonHideUnderline(
                child: DropdownButton<int>(
                  value: _selectedUserId,
                  dropdownColor: const Color(0xFF1E40AF),
                  style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
                  icon: const Icon(Icons.arrow_drop_down, color: Colors.white),
                  isExpanded: true,
                  items: _users.map((user) {
                    return DropdownMenuItem<int>(value: user['id'], child: Text(user['name']));
                  }).toList(),
                  onChanged: (val) {
                    setState(() => _selectedUserId = val);
                    _loadRecap();
                  },
                ),
              ),
            ),
            const SizedBox(height: 16),
          ],
          Row(
            children: [
              Expanded(child: _buildDateButton(true)),
              const Padding(padding: EdgeInsets.symmetric(horizontal: 8), child: Icon(Icons.arrow_forward, color: Colors.white70, size: 16)),
              Expanded(child: _buildDateButton(false)),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildDateButton(bool isStart) {
    return InkWell(
      onTap: () => _selectDate(context, isStart),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
        decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12)),
        child: Row(
          children: [
            const Icon(Icons.calendar_today, size: 14, color: Color(0xFF004A99)),
            const SizedBox(width: 8),
            Text(
              DateFormat('dd MMM yyyy').format(isStart ? _startDate : _endDate),
              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSummaryCards() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
      child: Row(
        children: [
          _buildQuickStat('Hadir', _recapData!['total_attendance'].split(' ')[0], Colors.green),
          const SizedBox(width: 12),
          _buildQuickStat('Lembur', _recapData!['overtime'].split(' ')[0], Colors.orange),
          const SizedBox(width: 12),
          _buildQuickStat('Alpa', _recapData!['absent'].split(' ')[0], Colors.red),
        ],
      ),
    );
  }

  Widget _buildQuickStat(String label, String value, Color color) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 20),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          boxShadow: [BoxShadow(color: color.withOpacity(0.1), blurRadius: 10, offset: const Offset(0, 4))],
        ),
        child: Column(
          children: [
            Text(value, style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: color)),
            const SizedBox(height: 4),
            Text(label, style: const TextStyle(fontSize: 12, color: Colors.grey, fontWeight: FontWeight.w500)),
          ],
        ),
      ),
    );
  }

  Widget _buildRecapTile(String label, dynamic value, IconData icon, Color color, {VoidCallback? onTap}) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.white),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.03), blurRadius: 10, offset: const Offset(0, 2))],
      ),
      child: ListTile(
        onTap: onTap,
        leading: Container(
          padding: const EdgeInsets.all(10),
          decoration: BoxDecoration(color: color.withOpacity(0.1), borderRadius: BorderRadius.circular(12)),
          child: Icon(icon, color: color, size: 20),
        ),
        title: Text(label, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Color(0xFF334155))),
        trailing: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(value.toString(), style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
            if (onTap != null) const Icon(Icons.chevron_right, color: Colors.grey, size: 20),
          ],
        ),
      ),
    );
  }

  void _showDayDetails(String type) {
    List details = _recapData?['details'] ?? [];
    
    // Filter based on type
    if (type == "Terlambat") {
      details = details.where((d) => (int.tryParse(d['late_minutes'].toString()) ?? 0) > 0).toList();
    } else if (type == "Kehadiran") {
      details = details.where((d) => d['is_attendance'] == true || (d['leave_days'] ?? 0) > 0 || (d['permit_count'] ?? 0) > 0 || (d['outside_duty_count'] ?? 0) > 0).toList();
    } else if (type == "Hari Kerja") {
      details = details.where((d) => d['is_working_day'] == true).toList();
    }

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => Container(
        height: MediaQuery.of(context).size.height * 0.75,
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.only(topLeft: Radius.circular(32), topRight: Radius.circular(32)),
        ),
        child: Column(
          children: [
            const SizedBox(height: 12),
            Container(width: 40, height: 4, decoration: BoxDecoration(color: Colors.grey[300], borderRadius: BorderRadius.circular(2))),
            Padding(
              padding: const EdgeInsets.all(24),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text("Detail $type", style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
                  IconButton(onPressed: () => Navigator.pop(context), icon: const Icon(Icons.close)),
                ],
              ),
            ),
            Expanded(
              child: details.isEmpty 
                ? const Center(child: Text("Tidak ada data untuk kategori ini"))
                : ListView.builder(
                padding: const EdgeInsets.symmetric(horizontal: 24),
                itemCount: details.length,
                itemBuilder: (context, index) {
                  final day = details[index];
                  final date = DateTime.parse(day['date']);
                  final status = day['status'] ?? (day['is_working_day'] ? "Alpa" : "Libur");
                  final lateMinutes = int.tryParse(day['late_minutes'].toString()) ?? 0;

                  return Container(
                    margin: const EdgeInsets.only(bottom: 16),
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF8FAFC),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: Colors.grey.shade200),
                    ),
                    child: Row(
                      children: [
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(_getIndonesianDayName(date), style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                            Text(DateFormat('dd MMM yyyy').format(date), style: TextStyle(color: Colors.grey[600], fontSize: 12)),
                            if (type == "Terlambat" && lateMinutes > 0)
                              Text("Telat: $lateMinutes menit", style: const TextStyle(color: Colors.red, fontSize: 12, fontWeight: FontWeight.bold)),
                          ],
                        ),
                        const Spacer(),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                          decoration: BoxDecoration(
                            color: _getStatusColor(status).withOpacity(0.1),
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Text(
                            status.toUpperCase(),
                            style: TextStyle(color: _getStatusColor(status), fontSize: 10, fontWeight: FontWeight.bold),
                          ),
                        ),
                      ],
                    ),
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }

  String _getIndonesianDayName(DateTime date) {
    switch (date.weekday) {
      case 1: return "Senin";
      case 2: return "Selasa";
      case 3: return "Rabu";
      case 4: return "Kamis";
      case 5: return "Jumat";
      case 6: return "Sabtu";
      case 7: return "Minggu";
      default: return "";
    }
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'hadir': case 'success': return Colors.green;
      case 'alpa': return Colors.red;
      case 'cuti': return Colors.purple;
      case 'izin': return Colors.orange;
      case 'tugas luar': return Colors.indigo;
      case 'libur': return Colors.blueGrey;
      default: return Colors.blue;
    }
  }
}
