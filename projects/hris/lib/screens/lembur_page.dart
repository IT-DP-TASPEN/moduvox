import 'dart:io';
import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import 'package:image_picker/image_picker.dart';
import 'package:latlong2/latlong.dart';
import 'package:provider/provider.dart';
import 'dart:convert';
import 'package:intl/intl.dart';
import '../services/api_service.dart';
import '../providers/auth_provider.dart';
import 'package:http/http.dart' as http;

class LemburPage extends StatefulWidget {
  final int initialTab;
  const LemburPage({super.key, this.initialTab = 0});

  @override
  State<LemburPage> createState() => _LemburPageState();
}

class _LemburPageState extends State<LemburPage> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<AuthProvider>().loadUser();
    });
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;
    final bool canApprove = user?['is_approver'] == true;

    return DefaultTabController(
      length: canApprove ? 3 : 2,
      initialIndex: widget.initialTab,
      child: Scaffold(
        backgroundColor: const Color(0xFFF0F4F3),
        appBar: AppBar(
          title: const Text("Pengajuan Lembur", style: TextStyle(fontWeight: FontWeight.bold)),
          backgroundColor: const Color(0xFF673AB7),
          foregroundColor: Colors.white,
          elevation: 0,
          bottom: TabBar(
            labelColor: Colors.white,
            unselectedLabelColor: Colors.white70,
            indicatorColor: Colors.white,
            tabs: [
              const Tab(text: "Pengajuan", icon: Icon(Icons.add_task)),
              const Tab(text: "Riwayat", icon: Icon(Icons.history)),
              if (canApprove) const Tab(text: "Persetujuan", icon: Icon(Icons.rule)),
            ],
          ),
        ),
        body: TabBarView(
          children: [
            const _FormLemburTab(),
            const _RiwayatLemburTab(),
            if (canApprove) const _PersetujuanLemburTab(),
          ],
        ),
      ),
    );
  }
}

class _FormLemburTab extends StatefulWidget {
  const _FormLemburTab();
  @override
  State<_FormLemburTab> createState() => _FormLemburTabState();
}

class _FormLemburTabState extends State<_FormLemburTab> {
  LatLng? _currentLatLng;
  File? _imageFile;
  String? _selectedLemburType = 'Lembur Kerja';
  final TextEditingController _keteranganController = TextEditingController();
  final TextEditingController _jamIstirahatController = TextEditingController(text: '0');
  DateTime _startDate = DateTime.now();
  DateTime _endDate = DateTime.now();
  TimeOfDay _startTime = TimeOfDay.now();
  TimeOfDay _endTime = TimeOfDay.now();
  bool _isSubmitting = false;

  @override
  void initState() {
    super.initState();
    _getCurrentLocation();
  }

  Future<void> _getCurrentLocation() async {
    try {
      Position position = await Geolocator.getCurrentPosition(
        locationSettings: const LocationSettings(accuracy: LocationAccuracy.high),
      );
      if (mounted) setState(() => _currentLatLng = LatLng(position.latitude, position.longitude));
    } catch (e) {
      debugPrint("Location Error: $e");
    }
  }

  Future<void> _pickImage() async {
    final pickedFile = await ImagePicker().pickImage(source: ImageSource.camera, imageQuality: 70);
    if (pickedFile != null) setState(() => _imageFile = File(pickedFile.path));
  }

  Future<void> _selectDate(BuildContext context, bool isStart) async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: isStart ? _startDate : _endDate,
      firstDate: DateTime.now().subtract(const Duration(days: 7)),
      lastDate: DateTime.now().add(const Duration(days: 30)),
    );
    if (picked != null) {
      setState(() {
        if (isStart) {
          _startDate = picked;
          if (_startDate.isAfter(_endDate)) _endDate = _startDate;
        } else {
          _endDate = picked;
          if (_endDate.isBefore(_startDate)) _startDate = _endDate;
        }
      });
    }
  }

  Future<void> _selectTime(BuildContext context, bool isStart) async {
    final TimeOfDay? picked = await showTimePicker(context: context, initialTime: isStart ? _startTime : _endTime);
    if (picked != null) setState(() => isStart ? _startTime = picked : _endTime = picked);
  }

  Future<void> _submit() async {
    if (_imageFile == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text("Foto bukti wajib diambil")));
      return;
    }

    setState(() => _isSubmitting = true);
    try {
      final startAt = DateTime(_startDate.year, _startDate.month, _startDate.day, _startTime.hour, _startTime.minute);
      final endAt = DateTime(_endDate.year, _endDate.month, _endDate.day, _endTime.hour, _endTime.minute);
      
      final breakHours = double.tryParse(_jamIstirahatController.text) ?? 0;
      final breakMinutes = (breakHours * 60).round();

      final resp = await ApiService().postMultipart(
        '/overtime-requests',
        fields: {
          'type': _selectedLemburType!,
          'start_at': startAt.toIso8601String(),
          'end_at': endAt.toIso8601String(),
          'break_minutes': breakMinutes.toString(),
          'notes': _keteranganController.text.trim(),
          'latitude': _currentLatLng?.latitude.toString() ?? "0",
          'longitude': _currentLatLng?.longitude.toString() ?? "0",
        },
        photo: _imageFile,
      );

      if (resp.statusCode == 200 && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text("Lembur berhasil diajukan!")));
        DefaultTabController.of(context).animateTo(1);
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text("Error: $e")));
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthProvider>().user;
    final approverName = user?['approver'] ?? 'Admin HR';

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          Card(
            elevation: 0,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16), side: BorderSide(color: Colors.grey.shade200)),
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text("Form Lembur", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                  const SizedBox(height: 20),
                  Row(
                    children: [
                      Expanded(child: _buildDateTimePicker("Mulai", _startDate, _startTime, true)),
                      const SizedBox(width: 12),
                      Expanded(child: _buildDateTimePicker("Selesai", _endDate, _endTime, false)),
                    ],
                  ),
                  const SizedBox(height: 16),
                  TextField(
                    controller: _jamIstirahatController,
                    decoration: const InputDecoration(labelText: "Jam Istirahat (Contoh: 0.5 atau 1)", border: OutlineInputBorder()),
                    keyboardType: TextInputType.number,
                  ),
                  const SizedBox(height: 16),
                  TextField(
                    controller: _keteranganController,
                    decoration: const InputDecoration(labelText: "Alasan Lembur", border: OutlineInputBorder()),
                    maxLines: 2,
                  ),
                  const SizedBox(height: 16),
                  InkWell(
                    onTap: _pickImage,
                    child: Container(
                      height: 140,
                      width: double.infinity,
                      decoration: BoxDecoration(color: Colors.grey[50], borderRadius: BorderRadius.circular(12), border: Border.all(color: Colors.grey[300]!)),
                      child: _imageFile == null
                          ? const Column(mainAxisAlignment: MainAxisAlignment.center, children: [Icon(Icons.camera_enhance_outlined, size: 40, color: Colors.grey), Text("Foto Aktivitas Lembur", style: TextStyle(color: Colors.grey))])
                          : ClipRRect(borderRadius: BorderRadius.circular(12), child: Image.file(_imageFile!, fit: BoxFit.cover)),
                    ),
                  ),
                  const SizedBox(height: 20),
                  Row(children: [const Icon(Icons.person_search, color: Colors.blue, size: 18), const SizedBox(width: 8), Text("Penyetuju: $approverName", style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12))]),
                  const SizedBox(height: 24),
                  SizedBox(
                    width: double.infinity,
                    height: 50,
                    child: ElevatedButton(
                      onPressed: _isSubmitting ? null : _submit,
                      style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF673AB7), foregroundColor: Colors.white, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12))),
                      child: _isSubmitting ? const CircularProgressIndicator(color: Colors.white) : const Text("KIRIM PENGAJUAN LEMBUR", style: TextStyle(fontWeight: FontWeight.bold)),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDateTimePicker(String label, DateTime date, TimeOfDay time, bool isStart) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: const TextStyle(fontSize: 12, color: Colors.grey, fontWeight: FontWeight.bold)),
        const SizedBox(height: 8),
        InkWell(
          onTap: () => _selectDate(context, isStart),
          child: InputDecorator(decoration: const InputDecoration(contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 8), border: OutlineInputBorder()), child: Text(DateFormat('dd/MM/yy').format(date), style: const TextStyle(fontSize: 12))),
        ),
        const SizedBox(height: 4),
        InkWell(
          onTap: () => _selectTime(context, isStart),
          child: InputDecorator(decoration: const InputDecoration(contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 8), border: OutlineInputBorder()), child: Text(time.format(context), style: const TextStyle(fontSize: 12))),
        ),
      ],
    );
  }
}

class _RiwayatLemburTab extends StatefulWidget {
  const _RiwayatLemburTab();
  @override
  State<_RiwayatLemburTab> createState() => _RiwayatLemburTabState();
}

class _RiwayatLemburTabState extends State<_RiwayatLemburTab> {
  late Future<http.Response> _future;
  @override
  void initState() {
    super.initState();
    _future = ApiService().getOvertimeRequests();
  }

  void _showDetailDialog(Map<String, dynamic> item) {
    final status = item['status'].toString().toLowerCase();
    Color statusColor = status == 'approved' ? Colors.green : (status == 'rejected' ? Colors.red : Colors.orange);

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.more_time_outlined, color: Color(0xFF673AB7)),
            SizedBox(width: 10),
            Text("Detail Lembur", style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
          ],
        ),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              if (item['photo_path'] != null) ...[
                const Text("Foto Aktivitas:", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                const SizedBox(height: 8),
                ClipRRect(
                  borderRadius: BorderRadius.circular(12),
                  child: Image.network(
                    "${ApiService.baseUrl.replaceFirst('/api', '')}/storage/${item['photo_path']}",
                    width: double.infinity,
                    fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) => Container(
                      height: 100,
                      color: Colors.grey[200],
                      child: const Icon(Icons.broken_image, color: Colors.grey),
                    ),
                  ),
                ),
                const SizedBox(height: 16),
              ],
              _buildPopupRow("Status", status.toUpperCase(), valueColor: statusColor),
              _buildPopupRow("Mulai", DateFormat('dd MMM yyyy HH:mm').format(DateTime.parse(item['start_at']))),
              _buildPopupRow("Selesai", DateFormat('dd MMM yyyy HH:mm').format(DateTime.parse(item['end_at']))),
              _buildPopupRow("Istirahat", "${item['break_minutes']} Menit"),
              const Divider(),
              const Text("Alasan Lembur:", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
              const SizedBox(height: 4),
              Text(item['notes'] ?? "-", style: const TextStyle(fontSize: 14)),
            ],
          ),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text("Tutup")),
        ],
      ),
    );
  }

  Widget _buildPopupRow(String label, String value, {Color? valueColor}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(width: 80, child: Text("$label:", style: const TextStyle(color: Colors.grey, fontSize: 13))),
          Expanded(child: Text(value, style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: valueColor))),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: () async => setState(() => _future = ApiService().getOvertimeRequests()),
      child: FutureBuilder<http.Response>(
        future: _future,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) return const Center(child: CircularProgressIndicator());
          if (!snapshot.hasData || snapshot.data!.statusCode != 200) return const Center(child: Text("Gagal memuat data"));

          final data = jsonDecode(snapshot.data!.body);
          final List items = data['data'] ?? [];
          if (items.isEmpty) return ListView(children: const [SizedBox(height: 100), Center(child: Text("Belum ada riwayat"))]);

          return ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: items.length,
            itemBuilder: (context, index) {
              final item = items[index];
              final status = item['status'].toString().toLowerCase();
              Color statusColor = status == 'approved' ? Colors.green : (status == 'rejected' ? Colors.red : Colors.orange);

              return Card(
                margin: const EdgeInsets.only(bottom: 12),
                elevation: 0,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12), side: BorderSide(color: Colors.grey.shade200)),
                child: InkWell(
                  onTap: () => _showDetailDialog(item),
                  borderRadius: BorderRadius.circular(12),
                  child: ListTile(
                    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                    leading: Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(color: statusColor.withOpacity(0.1), shape: BoxShape.circle),
                      child: Icon(Icons.more_time_outlined, color: statusColor, size: 20),
                    ),
                    title: Text("Lembur - ${DateFormat('dd MMM').format(DateTime.parse(item['start_at']))}", style: const TextStyle(fontWeight: FontWeight.bold)),
                    subtitle: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const SizedBox(height: 4),
                        Text("${DateFormat('HH:mm').format(DateTime.parse(item['start_at']))} s/d ${DateFormat('HH:mm').format(DateTime.parse(item['end_at']))}"),
                        if (item['notes'] != null) Text(item['notes'], style: const TextStyle(fontSize: 12, color: Colors.grey), maxLines: 1, overflow: TextOverflow.ellipsis),
                      ],
                    ),
                    trailing: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(color: statusColor.withOpacity(0.1), borderRadius: BorderRadius.circular(20)),
                      child: Text(status.toUpperCase(), style: TextStyle(color: statusColor, fontSize: 10, fontWeight: FontWeight.bold)),
                    ),
                  ),
                ),
              );
            },
          );
        },
      ),
    );
  }
}

class _PersetujuanLemburTab extends StatefulWidget {
  const _PersetujuanLemburTab();
  @override
  State<_PersetujuanLemburTab> createState() => _PersetujuanLemburTabState();
}

class _PersetujuanLemburTabState extends State<_PersetujuanLemburTab> {
  late Future<http.Response> _future;
  @override
  void initState() {
    super.initState();
    _future = ApiService().getPendingOvertimeApprovals();
  }

  void _refresh() => setState(() => _future = ApiService().getPendingOvertimeApprovals());

  Future<void> _process(int id, bool approve) async {
    showDialog(context: context, barrierDismissible: false, builder: (_) => const Center(child: CircularProgressIndicator()));
    try {
      final resp = approve ? await ApiService().approveOvertimeRequest(id) : await ApiService().rejectOvertimeRequest(id);
      if (!mounted) return;
      Navigator.pop(context);
      if (resp.statusCode == 200 && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(approve ? "Berhasil disetujui" : "Berhasil ditolak")));
        _refresh();
      }
    } catch (e) {
      if (mounted) Navigator.pop(context);
    }
  }

  void _showDetailDialog(Map<String, dynamic> item) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text("Detail Lembur Karyawan", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              if (item['photo_path'] != null) ...[
                const Text("Foto Bukti:", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                const SizedBox(height: 8),
                ClipRRect(
                  borderRadius: BorderRadius.circular(12),
                  child: Image.network("${ApiService.baseUrl.replaceFirst('/api', '')}/storage/${item['photo_path']}"),
                ),
                const SizedBox(height: 16),
              ],
              Text("Nama: ${item['user']?['name'] ?? '-'}", style: const TextStyle(fontWeight: FontWeight.bold)),
              const SizedBox(height: 8),
              Text("Waktu: ${DateFormat('dd/MM HH:mm').format(DateTime.parse(item['start_at']))} s/d ${DateFormat('HH:mm').format(DateTime.parse(item['end_at']))}"),
              Text("Istirahat: ${item['break_minutes']} Menit"),
              const Divider(),
              const Text("Alasan:", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
              Text(item['notes'] ?? "-"),
            ],
          ),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text("Tutup")),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: () async => _refresh(),
      child: FutureBuilder<http.Response>(
        future: _future,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) return const Center(child: CircularProgressIndicator());
          if (!snapshot.hasData || snapshot.data!.statusCode != 200) return const Center(child: Text("Tidak ada pengajuan"));

          final List items = jsonDecode(snapshot.data!.body);
          if (items.isEmpty) return ListView(children: const [SizedBox(height: 100), Center(child: Text("Tidak ada antrian"))]);

          return ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: items.length,
            itemBuilder: (context, index) {
              final item = items[index];
              final requester = item['user']?['name'] ?? "Karyawan";
              return Card(
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(requester, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                      const Divider(height: 24),
                      Text("Waktu: ${DateFormat('dd/MM HH:mm').format(DateTime.parse(item['start_at']))} s/d ${DateFormat('HH:mm').format(DateTime.parse(item['end_at']))}"),
                      Text("Istirahat: ${item['break_minutes']} Menit"),
                      Text("Alasan: ${item['notes'] ?? '-'}"),
                      const SizedBox(height: 16),
                      Row(
                        children: [
                          Expanded(
                            child: ElevatedButton.icon(
                              onPressed: () => _showDetailDialog(item),
                              icon: const Icon(Icons.image_outlined, size: 18),
                              label: const Text("LIHAT BUKTI"),
                              style: ElevatedButton.styleFrom(backgroundColor: Colors.blueGrey, foregroundColor: Colors.white),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      Row(
                        children: [
                          Expanded(child: OutlinedButton(onPressed: () => _process(item['id'], false), child: const Text("TOLAK", style: TextStyle(color: Colors.red)))),
                          const SizedBox(width: 12),
                          Expanded(child: ElevatedButton(onPressed: () => _process(item['id'], true), style: ElevatedButton.styleFrom(backgroundColor: Colors.green, foregroundColor: Colors.white), child: const Text("SETUJUI"))),
                        ],
                      )
                    ],
                  ),
                ),
              );
            },
          );
        },
      ),
    );
  }
}
