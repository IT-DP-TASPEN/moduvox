import 'dart:io';

import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:geolocator/geolocator.dart';
import 'package:image_picker/image_picker.dart';
import 'package:latlong2/latlong.dart';
import '../services/api_service.dart';
import '../components/map_widget.dart';
import 'package:provider/provider.dart';
import 'package:gal/gal.dart';
import '../providers/settings_provider.dart';
import '../providers/auth_provider.dart';

class AbsenKeluarPage extends StatefulWidget {
  const AbsenKeluarPage({super.key});

  @override
  State<AbsenKeluarPage> createState() => _AbsenKeluarPageState();
}

class _AbsenKeluarPageState extends State<AbsenKeluarPage> {
  LatLng? _currentLatLng;
  File? _imageFile;
  bool _isLoading = false;
  late Stream<DateTime> _timeStream;

  @override
  void initState() {
    super.initState();
    _getCurrentLocation();
    _timeStream = Stream.periodic(const Duration(seconds: 1), (_) => DateTime.now()).asBroadcastStream();
  }

  Future<void> _getCurrentLocation() async {
    bool serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text("GPS Anda mati. Silakan aktifkan GPS.")),
        );
      }
      return;
    }

    LocationPermission permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
      if (permission == LocationPermission.denied) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text("Izin lokasi ditolak.")),
          );
        }
        return;
      }
    }

    if (permission == LocationPermission.deniedForever) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text("Izin lokasi diblokir permanen. Aktifkan di Pengaturan.")),
        );
      }
      return;
    }

    Position position = await Geolocator.getCurrentPosition(
      locationSettings: const LocationSettings(
        accuracy: LocationAccuracy.high,
      ),
    );

    setState(() {
      _currentLatLng = LatLng(position.latitude, position.longitude);
    });
  }

  Future<void> _pickImage() async {
    final picker = ImagePicker();
    final pickedFile = await picker.pickImage(
      source: ImageSource.camera,
      imageQuality: 70,
      maxWidth: 1280,
      maxHeight: 1280,
    );

    if (pickedFile != null) {
      final file = File(pickedFile.path);
      final fileSizeBytes = await file.length();
      if (fileSizeBytes > (9.5 * 1024 * 1024)) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text("Ukuran foto terlalu besar. Silakan ambil ulang (akan dikompres).")),
          );
        }
      }
      setState(() {
        _imageFile = file;
      });

      // Save to gallery if enabled in settings
      final settings = Provider.of<SettingsProvider>(context, listen: false);
      if (settings.saveToGallery) {
        await Gal.putImage(file.path);
      }
    }
  }

  void _submitAbsensi() async {
    if (_currentLatLng == null || _imageFile == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text("Lokasi & foto wajib diisi")),
      );
      return;
    }

    final userOffice = Provider.of<AuthProvider>(context, listen: false).user?['office'];

    if (userOffice == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text("Anda belum memiliki lokasi kantor. Hubungi HRD.")),
      );
      return;
    }

    final double officeLat = double.parse(userOffice['latitude'].toString());
    final double officeLng = double.parse(userOffice['longitude'].toString());
    final double officeRadius = double.parse(userOffice['radius'].toString());

    final distance = const Distance().distance(
      _currentLatLng!,
      LatLng(officeLat, officeLng),
    );

    if (distance > officeRadius) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text("Anda berada di luar radius kantor (${distance.toStringAsFixed(0)} m / max $officeRadius m)")),
      );
      return;
    }

    setState(() => _isLoading = true);

    try {
      final apiService = ApiService();
      final response = await apiService.submitAttendance(
        type: 'keluar',
        latitude: _currentLatLng!.latitude,
        longitude: _currentLatLng!.longitude,
        photo: _imageFile,
      );

      if (response.statusCode == 200) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text("Absen berhasil")),
          );
          Navigator.pop(context);
        }
      } else {
        throw Exception("Gagal kirim data: ${response.body}");
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text("Terjadi kesalahan: $e")),
        );
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);
    final cs = Theme.of(context).colorScheme;
    return Scaffold(
      appBar: AppBar(
        title: const Text("Absen Keluar"),
        backgroundColor: cs.error,
      ),
      body: _currentLatLng == null
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // 🕒 LIVE CLOCK HEADER
                    StreamBuilder<DateTime>(
                      stream: _timeStream,
                      builder: (context, snapshot) {
                        final now = snapshot.data ?? DateTime.now();
                        return Container(
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: cs.error.withValues(alpha: 0.05),
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: cs.error.withValues(alpha: 0.1)),
                          ),
                          child: Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.all(8),
                                decoration: BoxDecoration(
                                  color: cs.error.withValues(alpha: 0.1),
                                  shape: BoxShape.circle,
                                ),
                                child: Icon(Icons.watch_later, color: cs.error, size: 20),
                              ),
                              const SizedBox(width: 12),
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    DateFormat('EEEE, d MMMM yyyy', 'id').format(now),
                                    style: TextStyle(
                                      fontSize: 12,
                                      fontWeight: FontWeight.w600,
                                      color: Colors.grey.shade600,
                                    ),
                                  ),
                                  Text(
                                    DateFormat('HH:mm:ss').format(now),
                                    style: TextStyle(
                                      fontSize: 20,
                                      fontWeight: FontWeight.w800,
                                      color: cs.error,
                                      letterSpacing: 1,
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        );
                      },
                    ),
                    const SizedBox(height: 16),

                    // MAP
                    Card(
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(16),
                      ),
                      elevation: 4,
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(16),
                        child: Stack(
                          children: [
                            AppMapWidget(
                              center: _currentLatLng!,
                              useGoogleMaps: true,
                              myLocationEnabled: false,
                              officeLocation: (authProvider.user?['office'] != null)
                                  ? LatLng(
                                      double.parse(authProvider.user!['office']['latitude'].toString()),
                                      double.parse(authProvider.user!['office']['longitude'].toString()),
                                    )
                                  : null,
                              radius: (authProvider.user?['office'] != null)
                                  ? double.parse(authProvider.user!['office']['radius'].toString())
                                  : null,
                            ),
                            // 🛰️ TIMESTAMP OVERLAY ON MAP
                            Positioned(
                              top: 12,
                              left: 12,
                              child: Container(
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                                decoration: BoxDecoration(
                                  color: Colors.black.withValues(alpha: 0.6),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: StreamBuilder<DateTime>(
                                  stream: _timeStream,
                                  builder: (context, snapshot) {
                                    final now = snapshot.data ?? DateTime.now();
                                    return Text(
                                      DateFormat('dd/MM/yyyy HH:mm:ss', 'id').format(now),
                                      style: const TextStyle(
                                        color: Colors.white,
                                        fontSize: 11,
                                        fontWeight: FontWeight.bold,
                                        fontFamily: 'monospace',
                                      ),
                                    );
                                  },
                                ),
                              ),
                            ),
                            Positioned(
                              right: 16,
                              bottom: 16,
                              child: FloatingActionButton.small(
                                onPressed: _getCurrentLocation,
                                backgroundColor: cs.error,
                                child: const Icon(Icons.refresh, color: Colors.white),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),

                    // FOTO
                    Card(
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(16),
                      ),
                      elevation: 4,
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          children: [
                            _imageFile == null
                                ? Container(
                                    height: 180,
                                    width: double.infinity,
                                    decoration: BoxDecoration(
                                      color: Colors.grey[200],
                                      borderRadius: BorderRadius.circular(12),
                                    ),
                                    child: const Center(
                                      child: Text(
                                        "Belum ada foto",
                                        style: TextStyle(color: Colors.black54),
                                      ),
                                    ),
                                  )
                                : ClipRRect(
                                    borderRadius: BorderRadius.circular(12),
                                    child: Image.file(
                                      _imageFile!,
                                      height: 180,
                                      width: double.infinity,
                                      fit: BoxFit.cover,
                                    ),
                                  ),
                            const SizedBox(height: 12),
                            ElevatedButton.icon(
                              onPressed: _pickImage,
                              style: ElevatedButton.styleFrom(
                                backgroundColor: cs.error,
                                minimumSize: const Size(double.infinity, 48),
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                              ),
                              icon: const Icon(Icons.camera_alt),
                              label: const Text("Ambil Foto"),
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 20),

                    // ✅ SUBMIT BUTTON
                    ElevatedButton(
                      onPressed: _isLoading ? null : _submitAbsensi,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: cs.error,
                        minimumSize: const Size(double.infinity, 52),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                      child: _isLoading
                          ? const CircularProgressIndicator(color: Colors.white)
                          : const Text(
                              "Kirim Absensi",
                              style: TextStyle(fontSize: 16, color: Colors.white),
                            ),
                    ),
                  ],
                ),
              ),
            ),
    );
  }
}
