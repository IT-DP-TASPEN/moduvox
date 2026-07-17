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

class CutiPage extends StatefulWidget {
  final int initialTab;
  const CutiPage({super.key, this.initialTab = 0});

  @override
  State<CutiPage> createState() => _CutiPageState();
}

class _CutiPageState extends State<CutiPage> {
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
    
    // Check if user is approver or director
    final bool canApprove = user?['is_approver'] == true;

    return DefaultTabController(
      key: ValueKey("cuti_tabs_${canApprove}_${widget.initialTab}"), // Rebuild if role or initial tab changes
      length: canApprove ? 3 : 2,
      initialIndex: widget.initialTab,
      child: Scaffold(
        backgroundColor: const Color(0xFFF0F4F3),
        appBar: AppBar(
          title: const Text("Pengajuan Cuti", style: TextStyle(fontWeight: FontWeight.bold)),
          backgroundColor: const Color(0xFF004A99),
          foregroundColor: Colors.white,
          elevation: 0,
          bottom: TabBar(
            labelColor: Colors.white,
            unselectedLabelColor: Colors.white70,
            indicatorColor: Colors.white,
            tabs: [
              const Tab(text: "Pengajuan", icon: Icon(Icons.add_task)),
              const Tab(text: "Riwayat Saya", icon: Icon(Icons.history)),
              if (canApprove) const Tab(text: "Persetujuan", icon: Icon(Icons.rule)),
            ],
          ),
        ),
        body: TabBarView(
          children: [
            const _FormPengajuanTab(),
            const _RiwayatCutiTab(),
            if (canApprove) const _PersetujuanCutiTab(),
          ],
        ),
      ),
    );
  }
}

/// --- TAB 1: FORM PENGAJUAN ---
class _FormPengajuanTab extends StatefulWidget {
  const _FormPengajuanTab();

  @override
  State<_FormPengajuanTab> createState() => _FormPengajuanTabState();
}

class _FormPengajuanTabState extends State<_FormPengajuanTab> {
  LatLng? _currentLatLng;
  File? _imageFile;
  String? _selectedCutiType;
  final TextEditingController _keteranganController = TextEditingController();
  DateTime _startDate = DateTime.now();
  DateTime _endDate = DateTime.now();
  bool _isSubmitting = false;

  final List<String> _jenisCuti = [
    'Cuti Tahunan',
    'Cuti Besar',
    'Sakit',
    'Cuti Alasan Penting',
    'Cuti Melahirkan',
    'Izin Tidak Masuk Kerja'
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

  Future<void> _selectDate(BuildContext context, bool isStart) async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: isStart ? _startDate : _endDate,
      firstDate: DateTime.now().subtract(const Duration(days: 30)),
      lastDate: DateTime.now().add(const Duration(days: 365)),
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

  Future<void> _submit() async {
    if (_selectedCutiType == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text("Pilih jenis cuti")));
      return;
    }
    if (_imageFile == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text("Ambil foto bukti/pendukung")));
      return;
    }

    setState(() => _isSubmitting = true);
    try {
      final resp = await ApiService().postMultipart(
        '/leave-requests',
        fields: {
          'type': _selectedCutiType!,
          'start_date': DateFormat('yyyy-MM-dd').format(_startDate),
          'end_date': DateFormat('yyyy-MM-dd').format(_endDate),
          'notes': _keteranganController.text.trim(),
          'latitude': _currentLatLng?.latitude.toString() ?? "0",
          'longitude': _currentLatLng?.longitude.toString() ?? "0",
        },
        photo: _imageFile,
      );

      final data = jsonDecode(resp.body);
      if (resp.statusCode == 200 && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(data['message'] ?? "Pengajuan berhasil dikirim!")));
        DefaultTabController.of(context).animateTo(1); // Swipe to history
      } else {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(data['message'] ?? "Gagal mengirim pengajuan")));
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text("Koneksi Error: $e")));
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final cs = Theme.of(context).colorScheme;
    final user = context.watch<AuthProvider>().user;
    final remainingLeave = user?['employment']?['remaining_leave'] ?? 0;
    final approverName = user?['approver'] ?? 'Admin HR';

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          // Info Card
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: Colors.blue.shade100),
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(color: Colors.blue.shade50, shape: BoxShape.circle),
                  child: const Icon(Icons.info, color: Colors.blue),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text("Sisa Jatah Cuti Anda", style: TextStyle(fontSize: 12, color: Colors.grey)),
                      Text("$remainingLeave Hari Kerja", style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Colors.black87)),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

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
                    value: _selectedCutiType,
                    decoration: const InputDecoration(labelText: "Jenis Cuti", prefixIcon: Icon(Icons.category_outlined), border: OutlineInputBorder()),
                    items: _jenisCuti.map((e) => DropdownMenuItem(value: e, child: Text(e))).toList(),
                    onChanged: (v) => setState(() => _selectedCutiType = v),
                  ),
                  const SizedBox(height: 16),
                  
                  Row(
                    children: [
                      Expanded(
                        child: InkWell(
                          onTap: () => _selectDate(context, true),
                          child: InputDecorator(
                            decoration: const InputDecoration(labelText: "Dari", border: OutlineInputBorder()),
                            child: Text(DateFormat('dd/MM/yyyy').format(_startDate)),
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: InkWell(
                          onTap: () => _selectDate(context, false),
                          child: InputDecorator(
                            decoration: const InputDecoration(labelText: "Sampai", border: OutlineInputBorder()),
                            child: Text(DateFormat('dd/MM/yyyy').format(_endDate)),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  
                  TextField(
                    controller: _keteranganController,
                    decoration: const InputDecoration(labelText: "Alasan / Catatan", prefixIcon: Icon(Icons.notes), border: OutlineInputBorder()),
                    maxLines: 2,
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
}

/// --- TAB 2: RIWAYAT CUTI SAYA ---
class _RiwayatCutiTab extends StatefulWidget {
  const _RiwayatCutiTab();

  @override
  State<_RiwayatCutiTab> createState() => _RiwayatCutiTabState();
}

class _RiwayatCutiTabState extends State<_RiwayatCutiTab> {
  late Future<http.Response> _future;

  @override
  void initState() {
    super.initState();
    _future = ApiService().getLeaveRequests();
  }

  Future<void> _refresh() async {
    setState(() {
      _future = ApiService().getLeaveRequests();
    });
  }

  void _showDetailDialog(Map<String, dynamic> item) {
    final status = item['status'].toString().toLowerCase();
    Color statusColor = Colors.orange;
    if (status == 'approved') statusColor = Colors.green;
    if (status == 'rejected') statusColor = Colors.red;

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Row(
          children: [
            const Icon(Icons.description, color: Color(0xFF004A99)),
            const SizedBox(width: 10),
            const Text("Detail Pengajuan", style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
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
              _buildPopupRow("Tanggal", "${item['start_date'].toString().split('T')[0]} s/d ${item['end_date'].toString().split('T')[0]}"),
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
      onRefresh: _refresh,
      child: FutureBuilder<http.Response>(
        future: _future,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) return const Center(child: CircularProgressIndicator());
          
          if (!snapshot.hasData || snapshot.data!.statusCode != 200) {
            return const Center(child: Text("Gagal mengambil data riwayat"));
          }

          final data = jsonDecode(snapshot.data!.body);
          final List items = data['data'] ?? [];

          if (items.isEmpty) {
            return ListView(children: const [SizedBox(height: 100), Center(child: Text("Belum ada riwayat pengajuan"))]);
          }

          return ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: items.length,
            itemBuilder: (context, index) {
              final item = items[index];
              final status = item['status'].toString().toLowerCase();
              Color statusColor = Colors.orange;
              if (status == 'approved') statusColor = Colors.green;
              if (status == 'rejected') statusColor = Colors.red;

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
                      child: Icon(Icons.calendar_today, color: statusColor, size: 20),
                    ),
                    title: Text(item['type'], style: const TextStyle(fontWeight: FontWeight.bold)),
                    subtitle: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const SizedBox(height: 4),
                        Text("${item['start_date'].toString().split('T')[0]} s/d ${item['end_date'].toString().split('T')[0]}"),
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

/// --- TAB 3: PERSETUJUAN (KHUSUS APPROVER) ---
class _PersetujuanCutiTab extends StatefulWidget {
  const _PersetujuanCutiTab();

  @override
  State<_PersetujuanCutiTab> createState() => _PersetujuanCutiTabState();
}

class _PersetujuanCutiTabState extends State<_PersetujuanCutiTab> {
  late Future<http.Response> _future;

  @override
  void initState() {
    super.initState();
    _future = ApiService().getPendingLeaveApprovals();
  }

  void _refresh() => setState(() => _future = ApiService().getPendingLeaveApprovals());

  Future<void> _process(int id, bool approve) async {
    showDialog(context: context, barrierDismissible: false, builder: (_) => const Center(child: CircularProgressIndicator()));
    try {
      final resp = approve ? await ApiService().approveLeaveRequest(id) : await ApiService().rejectLeaveRequest(id);
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
        title: const Text("Detail Cuti Karyawan", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
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
              Text("Tanggal: ${item['start_date'].toString().split('T')[0]} s/d ${item['end_date'].toString().split('T')[0]}"),
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
          
          if (!snapshot.hasData || snapshot.data!.statusCode != 200) {
            String errorMsg = "Gagal mengambil data persetujuan";
            if (snapshot.hasData) {
              try {
                final d = jsonDecode(snapshot.data!.body);
                if (d['message'] != null) errorMsg = d['message'];
              } catch (_) {}
            }
            return Center(child: Padding(padding: const EdgeInsets.all(20), child: Text(errorMsg, textAlign: TextAlign.center)));
          }

          final List items = jsonDecode(snapshot.data!.body);
          if (items.isEmpty) {
            return ListView(children: const [SizedBox(height: 100), Center(child: Text("Tidak ada pengajuan yang perlu disetujui"))]);
          }

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
                      Row(
                        children: [
                          CircleAvatar(backgroundColor: Colors.blue.shade100, child: Text(requester[0], style: const TextStyle(color: Colors.blue, fontWeight: FontWeight.bold))),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(requester, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                                Text(item['type'], style: TextStyle(color: Colors.blue.shade700, fontWeight: FontWeight.w500)),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const Padding(padding: EdgeInsets.symmetric(vertical: 12), child: Divider()),
                      
                      _buildDetailRow(Icons.calendar_month, "Tanggal", "${item['start_date'].toString().split('T')[0]} s/d ${item['end_date'].toString().split('T')[0]}"),
                      const SizedBox(height: 8),
                      _buildDetailRow(Icons.notes, "Alasan", item['notes'] ?? "-"),
                      
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
                          Expanded(
                            child: OutlinedButton(
                              onPressed: () => _process(item['id'], false),
                              style: OutlinedButton.styleFrom(
                                foregroundColor: Colors.red,
                                side: const BorderSide(color: Colors.red),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                              ),
                              child: const Text("TOLAK"),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: ElevatedButton(
                              onPressed: () => _process(item['id'], true),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: Colors.green,
                                foregroundColor: Colors.white,
                                elevation: 0,
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                              ),
                              child: const Text("SETUJUI"),
                            ),
                          ),
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

  Widget _buildDetailRow(IconData icon, String label, String value) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, size: 16, color: Colors.grey),
        const SizedBox(width: 8),
        Text("$label: ", style: const TextStyle(fontSize: 13, color: Colors.grey)),
        Expanded(child: Text(value, style: const TextStyle(fontSize: 13, color: Colors.black87))),
      ],
    );
  }
}
