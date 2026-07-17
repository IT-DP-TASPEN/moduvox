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

class IzinPage extends StatefulWidget {
  final int initialTab;
  const IzinPage({super.key, this.initialTab = 0});

  @override
  State<IzinPage> createState() => _IzinPageState();
}

class _IzinPageState extends State<IzinPage> {
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
          title: const Text("Pengajuan Izin", style: TextStyle(fontWeight: FontWeight.bold)),
          backgroundColor: const Color(0xFF0D8ABC),
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
            const _FormIzinTab(),
            const _RiwayatIzinTab(),
            if (canApprove) const _PersetujuanIzinTab(),
          ],
        ),
      ),
    );
  }
}

class _FormIzinTab extends StatefulWidget {
  const _FormIzinTab();
  @override
  State<_FormIzinTab> createState() => _FormIzinTabState();
}

class _FormIzinTabState extends State<_FormIzinTab> {
  LatLng? _currentLatLng;
  File? _imageFile;
  String? _selectedIzinType;
  final TextEditingController _keteranganController = TextEditingController();
  DateTime _selectedDate = DateTime.now();
  TimeOfDay _selectedTime = TimeOfDay.now();
  bool _isSubmitting = false;

  final List<String> _jenisIzin = [
    'Izin Terlambat',
    'Izin Pulang Awal',
    'Izin Di tengah Jam kerja'
  ];

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

  Future<void> _selectDate(BuildContext context) async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate,
      firstDate: DateTime.now().subtract(const Duration(days: 7)),
      lastDate: DateTime.now().add(const Duration(days: 7)),
    );
    if (picked != null) setState(() => _selectedDate = picked);
  }

  Future<void> _selectTime(BuildContext context) async {
    final TimeOfDay? picked = await showTimePicker(context: context, initialTime: _selectedTime);
    if (picked != null) setState(() => _selectedTime = picked);
  }

  Future<void> _submit() async {
    if (_selectedIzinType == null || _imageFile == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text("Jenis izin dan foto wajib diisi")));
      return;
    }

    setState(() => _isSubmitting = true);
    try {
      final requestedAt = DateTime(_selectedDate.year, _selectedDate.month, _selectedDate.day, _selectedTime.hour, _selectedTime.minute);
      final resp = await ApiService().postMultipart(
        '/permit-requests',
        fields: {
          'type': _selectedIzinType!,
          'requested_at': requestedAt.toIso8601String(),
          'notes': _keteranganController.text.trim(),
          'latitude': _currentLatLng?.latitude.toString() ?? "0",
          'longitude': _currentLatLng?.longitude.toString() ?? "0",
        },
        photo: _imageFile,
      );

      final data = jsonDecode(resp.body);
      if (resp.statusCode == 200 && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(data['message'] ?? "Izin berhasil dikirim!")));
        DefaultTabController.of(context).animateTo(1);
      } else {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(data['message'] ?? "Gagal mengirim izin")));
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
                  const Text("Detail Izin", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                  const SizedBox(height: 20),
                  DropdownButtonFormField<String>(
                    value: _selectedIzinType,
                    decoration: const InputDecoration(labelText: "Jenis Izin", border: OutlineInputBorder()),
                    items: _jenisIzin.map((e) => DropdownMenuItem(value: e, child: Text(e))).toList(),
                    onChanged: (v) => setState(() => _selectedIzinType = v),
                  ),
                  const SizedBox(height: 16),
                  Row(
                    children: [
                      Expanded(
                        child: InkWell(
                          onTap: () => _selectDate(context),
                          child: InputDecorator(
                            decoration: const InputDecoration(labelText: "Tanggal", border: OutlineInputBorder()),
                            child: Text(DateFormat('dd/MM/yyyy').format(_selectedDate)),
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: InkWell(
                          onTap: () => _selectTime(context),
                          child: InputDecorator(
                            decoration: const InputDecoration(labelText: "Jam", border: OutlineInputBorder()),
                            child: Text(_selectedTime.format(context)),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  TextField(
                    controller: _keteranganController,
                    decoration: const InputDecoration(labelText: "Keterangan", border: OutlineInputBorder()),
                    maxLines: 2,
                  ),
                  const SizedBox(height: 16),
                  const Text("Foto Bukti", style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.grey)),
                  const SizedBox(height: 8),
                  InkWell(
                    onTap: _pickImage,
                    child: Container(
                      height: 140,
                      width: double.infinity,
                      decoration: BoxDecoration(color: Colors.grey[50], borderRadius: BorderRadius.circular(12), border: Border.all(color: Colors.grey[300]!)),
                      child: _imageFile == null
                          ? const Column(mainAxisAlignment: MainAxisAlignment.center, children: [Icon(Icons.camera_enhance_outlined, size: 40, color: Colors.grey), Text("Ambil foto", style: TextStyle(color: Colors.grey))])
                          : ClipRRect(borderRadius: BorderRadius.circular(12), child: Image.file(_imageFile!, fit: BoxFit.cover)),
                    ),
                  ),
                  const SizedBox(height: 24),
                  Row(
                    children: [
                      const Icon(Icons.person_search, color: Colors.blue, size: 18),
                      const SizedBox(width: 8),
                      Text("Penyetuju: $approverName", style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                    ],
                  ),
                  const SizedBox(height: 24),
                  SizedBox(
                    width: double.infinity,
                    height: 50,
                    child: ElevatedButton(
                      onPressed: _isSubmitting ? null : _submit,
                      style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF0D8ABC), foregroundColor: Colors.white, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12))),
                      child: _isSubmitting ? const CircularProgressIndicator(color: Colors.white) : const Text("KIRIM PENGAJUAN", style: TextStyle(fontWeight: FontWeight.bold)),
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
}

class _RiwayatIzinTab extends StatefulWidget {
  const _RiwayatIzinTab();
  @override
  State<_RiwayatIzinTab> createState() => _RiwayatIzinTabState();
}

class _RiwayatIzinTabState extends State<_RiwayatIzinTab> {
  late Future<http.Response> _future;
  @override
  void initState() {
    super.initState();
    _future = ApiService().getPermitRequests();
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
            Icon(Icons.timer_outlined, color: Color(0xFF0D8ABC)),
            SizedBox(width: 10),
            Text("Detail Izin", style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
          ],
        ),
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
              _buildPopupRow("Jenis", item['type']),
              _buildPopupRow("Status", status.toUpperCase(), valueColor: statusColor),
              _buildPopupRow("Waktu", DateFormat('dd MMM yyyy HH:mm').format(DateTime.parse(item['requested_at']))),
              const Divider(),
              const Text("Keterangan:", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
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
      onRefresh: () async => setState(() => _future = ApiService().getPermitRequests()),
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
                      child: Icon(Icons.timer_outlined, color: statusColor, size: 20),
                    ),
                    title: Text(item['type'], style: const TextStyle(fontWeight: FontWeight.bold)),
                    subtitle: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const SizedBox(height: 4),
                        Text(DateFormat('dd MMM yyyy HH:mm').format(DateTime.parse(item['requested_at']))),
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

class _PersetujuanIzinTab extends StatefulWidget {
  const _PersetujuanIzinTab();
  @override
  State<_PersetujuanIzinTab> createState() => _PersetujuanIzinTabState();
}

class _PersetujuanIzinTabState extends State<_PersetujuanIzinTab> {
  late Future<http.Response> _future;
  @override
  void initState() {
    super.initState();
    _future = ApiService().getPendingPermitApprovals();
  }

  void _refresh() => setState(() => _future = ApiService().getPendingPermitApprovals());

  Future<void> _process(int id, bool approve) async {
    showDialog(context: context, barrierDismissible: false, builder: (_) => const Center(child: CircularProgressIndicator()));
    try {
      final resp = approve ? await ApiService().approvePermitRequest(id) : await ApiService().rejectPermitRequest(id);
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
        title: const Text("Detail Izin Karyawan", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
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
              Text("Jenis: ${item['type']}"),
              Text("Waktu: ${DateFormat('dd MMM yyyy HH:mm').format(DateTime.parse(item['requested_at']))}"),
              const Divider(),
              const Text("Keterangan:", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
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
                      Text(item['type'], style: const TextStyle(color: Colors.blue)),
                      const Divider(height: 24),
                      Text("Waktu: ${DateFormat('dd/MM HH:mm').format(DateTime.parse(item['requested_at']))}"),
                      Text("Ket: ${item['notes'] ?? '-'}"),
                      const SizedBox(height: 16),
                      Row(
                        children: [
                          Expanded(
                            child: ElevatedButton.icon(
                              onPressed: () => _showDetailDialog(item),
                              icon: const Icon(Icons.image_search, size: 18),
                              label: const Text("LIHAT BUKTI"),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: Colors.blueGrey,
                                foregroundColor: Colors.white,
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                              ),
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
