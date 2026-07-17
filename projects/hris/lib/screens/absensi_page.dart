import 'dart:convert';

import 'package:flutter/material.dart';

import '../services/api_service.dart';
import 'absen_keluar_page.dart';
import 'absen_masuk_page.dart';

class AbsensiPage extends StatefulWidget {
  const AbsensiPage({super.key});

  @override
  State<AbsensiPage> createState() => _AbsensiPageState();
}

class _AbsensiPageState extends State<AbsensiPage> {
  bool _isLoading = true;
  String? _error;
  List<dynamic> _items = const [];

  @override
  void initState() {
    super.initState();
    _reload();
  }

  Future<void> _reload() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final resp = await ApiService().getHistory();
      if (resp.statusCode != 200) {
        setState(() {
          _error = 'Gagal memuat data absensi: ${resp.body}';
          _items = const [];
          _isLoading = false;
        });
        return;
      }

      final decoded = jsonDecode(resp.body);
      final items =
          (decoded is Map && decoded['data'] is List) ? (decoded['data'] as List) : const [];
      setState(() {
        _items = items;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = 'Terjadi kesalahan: $e';
        _items = const [];
        _isLoading = false;
      });
    }
  }

  static DateTime? _parseCreatedAt(dynamic createdAt) {
    if (createdAt == null) return null;
    try {
      return DateTime.parse(createdAt.toString()).toLocal();
    } catch (_) {
      return null;
    }
  }

  static bool _isSameDay(DateTime a, DateTime b) =>
      a.year == b.year && a.month == b.month && a.day == b.day;

  static String _two(int v) => v.toString().padLeft(2, '0');

  static String _formatTime(DateTime? dt) {
    if (dt == null) return '--:--';
    return '${_two(dt.hour)}:${_two(dt.minute)}';
  }

  static String _formatDuration(Duration? d) {
    if (d == null) return '-- : --';
    final hours = d.inHours;
    final minutes = d.inMinutes.remainder(60);
    return '${_two(hours)} : ${_two(minutes)}';
  }

  static String _weekdayId(int weekday) {
    const names = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
    return names[(weekday - 1).clamp(0, 6)];
  }

  static String _monthId(int month) {
    const names = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    return names[(month - 1).clamp(0, 11)];
  }

  static String _formatDateLong(DateTime dt) {
    return '${_weekdayId(dt.weekday)}, ${_two(dt.day)} ${_monthId(dt.month)} ${dt.year}';
  }

  static String _formatDateShort(DateTime dt) {
    return '${_two(dt.day)} ${_monthId(dt.month)} ${dt.year}';
  }

  ({DateTime? masuk, DateTime? keluar}) _timesForDay(DateTime day) {
    DateTime? masuk;
    DateTime? keluar;

    for (final it in _items) {
      if (it is! Map) continue;
      final created = _parseCreatedAt(it['created_at']);
      if (created == null) continue;
      if (!_isSameDay(created, day)) continue;

      if (it['type'] == 'masuk') {
        masuk ??= created;
        if (created.isBefore(masuk)) masuk = created;
      } else if (it['type'] == 'keluar') {
        keluar ??= created;
        if (created.isAfter(keluar)) keluar = created;
      }
    }

    return (masuk: masuk, keluar: keluar);
  }

  @override
  Widget build(BuildContext context) {
    final now = DateTime.now();
    final today = DateTime(now.year, now.month, now.day);
    final todayTimes = _timesForDay(today);
    final duration = todayTimes.masuk != null
        ? (todayTimes.keluar ?? now).difference(todayTimes.masuk!)
        : null;
    final hasCheckedIn = todayTimes.masuk != null;
    final hasCheckedOut = todayTimes.keluar != null;
    final cs = Theme.of(context).colorScheme;

    return Scaffold(
      appBar: AppBar(
        title: const Text(
          "Kehadiran",
          style: TextStyle(fontWeight: FontWeight.w700),
        ),
        elevation: 0,
        actions: [
          IconButton(
            onPressed: _reload,
            icon: const Icon(Icons.refresh_rounded),
          ),
        ],
      ),
      body: SingleChildScrollView(
        physics: const BouncingScrollPhysics(),
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            if (_isLoading)
              const Padding(
                padding: EdgeInsets.only(bottom: 12),
                child: LinearProgressIndicator(),
              ),
            if (_error != null)
              Padding(
                padding: const EdgeInsets.only(bottom: 12),
                child: Text(_error!, style: const TextStyle(color: Colors.red)),
              ),

            // 📌 Card Hari Ini
            Container(
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: Colors.grey.shade200),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.04),
                    blurRadius: 10,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              "HARI INI",
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.w800,
                                color: Colors.black54,
                                letterSpacing: 0.5,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              _formatDateLong(now),
                              style: const TextStyle(
                                fontWeight: FontWeight.w800,
                                fontSize: 16,
                                color: Colors.black87,
                              ),
                            ),
                          ],
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                          decoration: BoxDecoration(
                            color: cs.primary.withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Text(
                            "07:45 - 16:30",
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                              color: cs.primary,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: 16),
                      child: Divider(),
                    ),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceAround,
                      children: [
                        Column(
                          children: [
                            const Text(
                              "MASUK",
                              style: TextStyle(fontSize: 12, color: Colors.black54, fontWeight: FontWeight.w600),
                            ),
                            const SizedBox(height: 8),
                            Text(
                              _formatTime(todayTimes.masuk),
                              style: TextStyle(
                                fontSize: 24,
                                fontWeight: FontWeight.w800,
                                color: todayTimes.masuk != null ? cs.primary : Colors.black87,
                              ),
                            ),
                          ],
                        ),
                        Container(
                          width: 1,
                          height: 40,
                          color: Colors.grey.shade200,
                        ),
                        Column(
                          children: [
                            const Text(
                              "KELUAR",
                              style: TextStyle(fontSize: 12, color: Colors.black54, fontWeight: FontWeight.w600),
                            ),
                            const SizedBox(height: 8),
                            Text(
                              _formatTime(todayTimes.keluar),
                              style: TextStyle(
                                fontSize: 24,
                                fontWeight: FontWeight.w800,
                                color: todayTimes.keluar != null ? Colors.red.shade600 : Colors.black87,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                    const SizedBox(height: 20),
                    Container(
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      width: double.infinity,
                      decoration: BoxDecoration(
                        color: Colors.grey.shade50,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Column(
                        children: [
                          const Text(
                            "Durasi Kehadiran",
                            style: TextStyle(color: Colors.black54, fontSize: 12),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            _formatDuration(duration),
                            style: TextStyle(
                              fontWeight: FontWeight.w800,
                              color: cs.primary,
                              fontSize: 16,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 20),
                    Row(
                      children: [
                        Expanded(
                          child: FilledButton.icon(
                            style: FilledButton.styleFrom(
                              backgroundColor: cs.primary,
                              disabledBackgroundColor: Colors.grey.shade200,
                              disabledForegroundColor: Colors.grey.shade400,
                              minimumSize: const Size(double.infinity, 50),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(12),
                              ),
                            ),
                            onPressed: (_isLoading || hasCheckedIn) ? null : () async {
                              await Navigator.push(
                                context,
                                MaterialPageRoute(builder: (_) => const AbsenMasukPage()),
                              );
                              await _reload();
                            },
                            icon: const Icon(Icons.login_rounded),
                            label: const Text("Absen Masuk", style: TextStyle(fontWeight: FontWeight.w700)),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: FilledButton.icon(
                            style: FilledButton.styleFrom(
                              backgroundColor: Colors.red.shade600,
                              disabledBackgroundColor: Colors.grey.shade200,
                              disabledForegroundColor: Colors.grey.shade400,
                              minimumSize: const Size(double.infinity, 50),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(12),
                              ),
                            ),
                            onPressed: (_isLoading || !hasCheckedIn || hasCheckedOut) ? null : () async {
                              await Navigator.push(
                                context,
                                MaterialPageRoute(builder: (_) => const AbsenKeluarPage()),
                              );
                              await _reload();
                            },
                            icon: const Icon(Icons.logout_rounded),
                            label: const Text("Absen Keluar", style: TextStyle(fontWeight: FontWeight.w700)),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),

            // 📌 Tabel Jadwal (3 hari terakhir)
            Container(
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: Colors.grey.shade200),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.04),
                    blurRadius: 10,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Padding(
                      padding: EdgeInsets.only(bottom: 12),
                      child: Text(
                        "RIWAYAT TERAKHIR",
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w800,
                          color: Colors.black54,
                          letterSpacing: 0.5,
                        ),
                      ),
                    ),
                    Row(
                      children: [
                        const Expanded(
                          flex: 2,
                          child: Text(
                            "TANGGAL",
                            style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Colors.black54),
                          ),
                        ),
                        Expanded(
                          child: Text(
                            "MASUK",
                            textAlign: TextAlign.center,
                            style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: cs.primary),
                          ),
                        ),
                        Expanded(
                          child: Text(
                            "KELUAR",
                            textAlign: TextAlign.center,
                            style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Colors.red.shade600),
                          ),
                        ),
                      ],
                    ),
                    const Divider(height: 20),
                    for (int back = 1; back <= 3; back++) ...[
                      Builder(
                        builder: (context) {
                          final day = today.subtract(Duration(days: back));
                          final t = _timesForDay(day);
                          return _buildRow(
                            '${_formatDateShort(day)}\n07:45 - 16:30',
                            _formatTime(t.masuk),
                            _formatTime(t.keluar),
                          );
                        },
                      ),
                      if (back < 3) const Divider(height: 20, color: Colors.black12),
                    ],
                  ],
                ),
              ),
            ),
            const SizedBox(height: 20),
          ],
        ),
      ),
    );
  }

  Widget _buildRow(String jadwal, String masuk, String keluar) {
    final cs = Theme.of(context).colorScheme;
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        children: [
          Expanded(
            flex: 2,
            child: Text(
              jadwal,
              style: const TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w500,
                color: Colors.black87,
              ),
            ),
          ),
          Expanded(
            child: Text(
              masuk,
              textAlign: TextAlign.center,
              style: TextStyle(
                color: masuk == "--:--" ? Colors.grey : cs.primary,
                fontWeight: FontWeight.w700,
                fontSize: 14,
              ),
            ),
          ),
          Expanded(
            child: Text(
              keluar,
              textAlign: TextAlign.center,
              style: TextStyle(
                color: keluar == "--:--" ? Colors.grey : Colors.red.shade600,
                fontWeight: FontWeight.w700,
                fontSize: 14,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
