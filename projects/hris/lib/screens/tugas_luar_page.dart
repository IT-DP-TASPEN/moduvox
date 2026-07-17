import 'dart:io';
import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import 'package:image_picker/image_picker.dart';
import 'package:latlong2/latlong.dart' as ll;
import 'package:provider/provider.dart';
import 'dart:convert';
import 'package:intl/intl.dart';
import '../services/api_service.dart';
import '../providers/auth_provider.dart';
import 'package:http/http.dart' as http;
import 'package:gal/gal.dart';
import '../providers/settings_provider.dart';
import 'location_picker_page.dart';

class TugasLuarPage extends StatefulWidget {
  final int initialTab;
  const TugasLuarPage({super.key, this.initialTab = 0});

  @override
  State<TugasLuarPage> createState() => _TugasLuarPageState();
}

class _TugasLuarPageState extends State<TugasLuarPage> {
  final GlobalKey<FormTugasLuarTabState> _formKey = GlobalKey<FormTugasLuarTabState>();

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
          title: const Text("Tugas Luar", style: TextStyle(fontWeight: FontWeight.bold)),
          backgroundColor: const Color(0xFF004A99),
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
            FormTugasLuarTab(key: _formKey),
            const _RiwayatTugasLuarTab(),
            if (canApprove) const _PersetujuanTugasLuarTab(),
          ],
        ),
      ),
    );
  }
}

class FormTugasLuarTab extends StatefulWidget {
  const FormTugasLuarTab({super.key});
  @override
  State<FormTugasLuarTab> createState() => FormTugasLuarTabState();
}

class FormTugasLuarTabState extends State<FormTugasLuarTab> {
  ll.LatLng? _currentLatLng;
  File? _imageFile;
  String? _selectedTugasType;
  final TextEditingController _keteranganController = TextEditingController();
  final TextEditingController _overtimeController = TextEditingController(text: "0");
  DateTime _startDate = DateTime.now();
  DateTime _endDate = DateTime.now();
  TimeOfDay _startTime = TimeOfDay.now();
  TimeOfDay _endTime = TimeOfDay.now();
  bool _isSubmitting = false;

  final List<String> _jenisTugas = [
    'Antar Bilyet',
    'Antar Surat',
    'Ke DP Taspen',
    'Pelatihan',
    'Penagihan',
    'Work Form Home (WFH)',
    'Lain-lain'
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
      if (mounted) setState(() => _currentLatLng = ll.LatLng(position.latitude, position.longitude));
    } catch (e) {
      debugPrint("Location Error: $e");
    }
  }

  Future<void> _pickImage() async {
    final pickedFile = await ImagePicker().pickImage(source: ImageSource.camera, imageQuality: 70);
    if (pickedFile != null) {
      final file = File(pickedFile.path);
      setState(() => _imageFile = file);
      
      // Save to gallery if enabled in settings
      final settings = Provider.of<SettingsProvider>(context, listen: false);
      if (settings.saveToGallery) {
        await Gal.putImage(file.path);
      }
    }
  }

  Future<void> _selectDate(BuildContext context, bool isStart) async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: isStart ? _startDate : _endDate,
      firstDate: DateTime.now().subtract(const Duration(days: 7)),
      lastDate: DateTime.now().add(const Duration(days: 60)),
    );
    if (picked != null) setState(() => isStart ? _startDate = picked : _endDate = picked);
  }

  Future<void> _selectTime(BuildContext context, bool isStart) async {
    final TimeOfDay? picked = await showTimePicker(context: context, initialTime: isStart ? _startTime : _endTime);
    if (picked != null) setState(() => isStart ? _startTime = picked : _endTime = picked);
  }

  Future<void> submit() async {
    if (_isSubmitting) return;
    _submit();
  }

  Future<void> _submit() async {
    if (_selectedTugasType == null || _imageFile == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text("Jenis tugas dan foto wajib diisi")));
      return;
    }

    setState(() => _isSubmitting = true);
    try {
      final startAt = DateTime(_startDate.year, _startDate.month, _startDate.day, _startTime.hour, _startTime.minute);
      final endAt = DateTime(_endDate.year, _endDate.month, _endDate.day, _endTime.hour, _endTime.minute);
      
      final resp = await ApiService().postMultipart(
        '/outside-duty-requests',
        fields: {
          'type': _selectedTugasType!,
          'start_at': startAt.toIso8601String(),
          'end_at': endAt.toIso8601String(),
          'notes': _keteranganController.text.trim(),
          'overtime_minutes': ((double.tryParse(_overtimeController.text) ?? 0) * 60).toInt().toString(),
          'latitude': _currentLatLng?.latitude.toString() ?? "0",
          'longitude': _currentLatLng?.longitude.toString() ?? "0",
        },
        photo: _imageFile,
      );

      if (resp.statusCode == 200 && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text("Tugas luar berhasil dikirim!")));
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
          // Form Card
          Card(
            elevation: 0,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16), side: BorderSide(color: Colors.grey.shade200)),
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text("Detail Pengajuan", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                  const SizedBox(height: 20),
                  
                  DropdownButtonFormField<String>(
                    value: _selectedTugasType,
                    decoration: const InputDecoration(labelText: "Jenis Tugas", prefixIcon: Icon(Icons.category_outlined), border: OutlineInputBorder()),
                    items: _jenisTugas.map((e) => DropdownMenuItem(value: e, child: Text(e))).toList(),
                    onChanged: (v) => setState(() => _selectedTugasType = v),
                  ),
                  const SizedBox(height: 16),
                  
                  Row(
                    children: [
                      Expanded(child: _buildDateInput("Tanggal awal", _startDate, true)),
                      const SizedBox(width: 12),
                      Expanded(child: _buildDateInput("Tanggal akhir", _endDate, false)),
                    ],
                  ),
                  const SizedBox(height: 16),
                  
                  Row(
                    children: [
                      Expanded(child: _buildTimeInput("Jam awal", _startTime, true)),
                      const SizedBox(width: 12),
                      Expanded(child: _buildTimeInput("Jam akhir", _endTime, false)),
                    ],
                  ),
                  const SizedBox(height: 16),

                  TextField(
                    controller: _overtimeController,
                    decoration: const InputDecoration(labelText: "Jam lembur per hari", prefixIcon: Icon(Icons.timer_outlined), border: OutlineInputBorder()),
                    keyboardType: TextInputType.number,
                  ),
                  const SizedBox(height: 16),

                  InkWell(
                    onTap: () async {
                      final result = await Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) => LocationPickerPage(
                            initialCenter: _currentLatLng ?? const ll.LatLng(-6.200000, 106.816666),
                          ),
                        ),
                      );
                      if (result != null && result is ll.LatLng) {
                        setState(() => _currentLatLng = result);
                      }
                    },
                    child: InputDecorator(
                      decoration: const InputDecoration(
                        labelText: "Lokasi Tugas",
                        prefixIcon: Icon(Icons.location_on, color: Colors.red),
                        border: OutlineInputBorder(),
                        contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                        hintText: "Klik untuk mencari lokasi tugas luar",
                      ),
                      child: Text(
                        _currentLatLng == null 
                            ? "Klik untuk pilih lokasi (bisa cari di peta)" 
                            : "Terpilih: ${_currentLatLng!.latitude.toStringAsFixed(6)}, ${_currentLatLng!.longitude.toStringAsFixed(6)}",
                        style: TextStyle(
                          fontSize: 13,
                          color: _currentLatLng == null ? Colors.grey : Colors.black87,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),

                  const Text("Lampiran Foto Bukti", style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.grey)),
                  const SizedBox(height: 8),
                  InkWell(
                    onTap: _pickImage,
                    child: Container(
                      height: 140,
                      width: double.infinity,
                      decoration: BoxDecoration(
                        color: Colors.grey[50],
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: Colors.grey[300]!, style: BorderStyle.solid),
                      ),
                      child: _imageFile == null
                          ? const Column(mainAxisAlignment: MainAxisAlignment.center, children: [Icon(Icons.camera_enhance_outlined, size: 40, color: Colors.grey), Text("Klik untuk ambil foto", style: TextStyle(color: Colors.grey))])
                          : ClipRRect(borderRadius: BorderRadius.circular(12), child: Image.file(_imageFile!, fit: BoxFit.cover)),
                    ),
                  ),
                  
                  const SizedBox(height: 16),
                  TextField(
                    controller: _keteranganController,
                    decoration: const InputDecoration(labelText: "Keterangan / Tujuan", prefixIcon: Icon(Icons.notes), border: OutlineInputBorder()),
                    maxLines: 2,
                  ),
                  
                  const SizedBox(height: 24),
                  const Divider(),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      const Icon(Icons.person_search, color: Colors.blue, size: 20),
                      const SizedBox(width: 8),
                      const Text("Target Approval:", style: TextStyle(fontSize: 12, color: Colors.grey)),
                      const SizedBox(width: 4),
                      Text(approverName, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                    ],
                  ),
                  const SizedBox(height: 24),
                  
                  SizedBox(
                    width: double.infinity,
                    height: 50,
                    child: ElevatedButton(
                      onPressed: _isSubmitting ? null : _submit,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF004A99),
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      child: _isSubmitting ? const CircularProgressIndicator(color: Colors.white) : const Text("KIRIM PENGAJUAN SEKARANG", style: TextStyle(fontWeight: FontWeight.bold)),
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

  Widget _buildDateInput(String label, DateTime date, bool isStart) {
    return InkWell(
      onTap: () => _selectDate(context, isStart),
      child: InputDecorator(
        decoration: InputDecoration(labelText: label, border: const OutlineInputBorder(), contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10)),
        child: Text(DateFormat('dd/MM/yyyy').format(date), style: const TextStyle(fontSize: 13)),
      ),
    );
  }

  Widget _buildTimeInput(String label, TimeOfDay time, bool isStart) {
    return InkWell(
      onTap: () => _selectTime(context, isStart),
      child: InputDecorator(
        decoration: InputDecoration(labelText: label, border: const OutlineInputBorder(), contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10)),
        child: Text(time.format(context), style: const TextStyle(fontSize: 13)),
      ),
    );
  }
}

class _RiwayatTugasLuarTab extends StatefulWidget {
  const _RiwayatTugasLuarTab();
  @override
  State<_RiwayatTugasLuarTab> createState() => _RiwayatTugasLuarTabState();
}

class _RiwayatTugasLuarTabState extends State<_RiwayatTugasLuarTab> {
  late Future<http.Response> _future;
  @override
  void initState() {
    super.initState();
    _future = ApiService().getOutsideDutyRequests();
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
            Icon(Icons.location_on_outlined, color: Color(0xFF004A99)),
            SizedBox(width: 10),
            Text("Detail Tugas Luar", style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
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
              _buildPopupRow("Mulai", DateFormat('dd MMM yyyy HH:mm').format(DateTime.parse(item['start_at']))),
              _buildPopupRow("Selesai", DateFormat('dd MMM yyyy HH:mm').format(DateTime.parse(item['end_at']))),
              const Divider(),
              const Text("Keterangan / Tujuan:", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
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
      onRefresh: () async => setState(() => _future = ApiService().getOutsideDutyRequests()),
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
                      child: Icon(Icons.location_on_outlined, color: statusColor, size: 20),
                    ),
                    title: Text(item['type'], style: const TextStyle(fontWeight: FontWeight.bold)),
                    subtitle: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const SizedBox(height: 4),
                        Text("${DateFormat('dd MMM').format(DateTime.parse(item['start_at']))} - ${DateFormat('dd MMM').format(DateTime.parse(item['end_at']))}"),
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

class _PersetujuanTugasLuarTab extends StatefulWidget {
  const _PersetujuanTugasLuarTab();
  @override
  State<_PersetujuanTugasLuarTab> createState() => _PersetujuanTugasLuarTabState();
}

class _PersetujuanTugasLuarTabState extends State<_PersetujuanTugasLuarTab> {
  late Future<http.Response> _future;
  @override
  void initState() {
    super.initState();
    _future = ApiService().getPendingOutsideDutyApprovals();
  }

  void _refresh() => setState(() => _future = ApiService().getPendingOutsideDutyApprovals());

  Future<void> _process(int id, bool approve) async {
    showDialog(context: context, barrierDismissible: false, builder: (_) => const Center(child: CircularProgressIndicator()));
    try {
      final resp = approve ? await ApiService().approveOutsideDutyRequest(id) : await ApiService().rejectOutsideDutyRequest(id);
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
        title: const Text("Detail Tugas Luar Karyawan", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
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
              Text("Waktu: ${DateFormat('dd/MM HH:mm').format(DateTime.parse(item['start_at']))} s/d ${DateFormat('dd/MM HH:mm').format(DateTime.parse(item['end_at']))}"),
              const Divider(),
              const Text("Keterangan / Tujuan:", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
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
                margin: const EdgeInsets.only(bottom: 16),
                elevation: 4,
                shadowColor: Colors.black12,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(requester, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                      Text(item['type'], style: const TextStyle(color: Colors.blue)),
                      const Divider(height: 24),
                      Text("Waktu: ${DateFormat('dd/MM HH:mm').format(DateTime.parse(item['start_at']))} s/d ${DateFormat('dd/MM HH:mm').format(DateTime.parse(item['end_at']))}"),
                      Text("Keterangan: ${item['notes'] ?? '-'}"),
                      const SizedBox(height: 16),
                      Row(
                        children: [
                          Expanded(
                            child: ElevatedButton.icon(
                              onPressed: () => _showDetailDialog(item),
                              icon: const Icon(Icons.image_search, size: 18),
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
