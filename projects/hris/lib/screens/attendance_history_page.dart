import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:table_calendar/table_calendar.dart';
import 'package:intl/intl.dart';
import '../services/api_service.dart';

class AttendanceHistoryPage extends StatefulWidget {
  const AttendanceHistoryPage({super.key});

  @override
  State<AttendanceHistoryPage> createState() => _AttendanceHistoryPageState();
}

class _AttendanceHistoryPageState extends State<AttendanceHistoryPage> {
  late Future<_HistoryResult> _future;
  DateTime _focusedDay = DateTime.now();
  DateTime? _selectedDay;
  Map<DateTime, List<dynamic>> _events = {};

  @override
  void initState() {
    super.initState();
    _selectedDay = _focusedDay;
    _future = _load();
  }

  Future<_HistoryResult> _load() async {
    final api = ApiService();
    final resp = await api.getHistory();
    if (resp.statusCode != 200) {
      return _HistoryResult.error('Gagal memuat riwayat: ${resp.body}');
    }

    final decoded = jsonDecode(resp.body);
    final items = (decoded is Map && decoded['data'] is List) ? (decoded['data'] as List) : const [];
    
    // Process items into events map
    final Map<DateTime, List<dynamic>> eventMap = {};
    for (var item in items) {
      final dt = _parseCreatedAt(item['created_at']);
      if (dt != null) {
        final dateKey = DateTime(dt.year, dt.month, dt.day);
        eventMap.putIfAbsent(dateKey, () => []).add(item);
      }
    }

    setState(() {
      _events = eventMap;
    });

    return _HistoryResult.ok(items.cast<dynamic>());
  }

  static String _formatType(dynamic type) {
    if (type == 'masuk') return 'Masuk';
    if (type == 'keluar') return 'Keluar';
    return (type ?? '-').toString();
  }

  static DateTime? _parseCreatedAt(dynamic createdAt) {
    if (createdAt == null) return null;
    final raw = createdAt.toString().trim();
    if (raw.isEmpty) return null;
    try {
      return DateTime.parse(raw).toLocal();
    } catch (_) {
      return null;
    }
  }

  static String _two(int v) => v.toString().padLeft(2, '0');

  String _formatTime(DateTime dt) {
    return '${_two(dt.hour)}:${_two(dt.minute)}';
  }

  String _getDayName(DateTime dt) {
    final names = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
    return names[dt.weekday - 1];
  }

  String _getMonthName(int month) {
    final names = [
      'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
      'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    return names[month - 1];
  }

  @override
  Widget build(BuildContext context) {
    final cs = Theme.of(context).colorScheme;
    return Scaffold(
      backgroundColor: const Color(0xFFF8F9FA),
      appBar: AppBar(
        title: const Text('Riwayat Absensi Online', style: TextStyle(fontSize: 18)),
        backgroundColor: cs.primary,
        foregroundColor: Colors.white,
      ),
      body: FutureBuilder<_HistoryResult>(
        future: _future,
        builder: (context, snapshot) {
          if (snapshot.connectionState != ConnectionState.done) {
            return const Center(child: CircularProgressIndicator());
          }

          final result = snapshot.data;
          if (result == null || result.errorMessage != null) {
            return Center(child: Text(result?.errorMessage ?? 'Gagal memuat data.'));
          }

          final selectedEvents = _selectedDay != null ? _events[DateTime(_selectedDay!.year, _selectedDay!.month, _selectedDay!.day)] ?? [] : [];

          return Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Calendar Card
              Container(
                margin: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  boxShadow: [
                    BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 10, offset: const Offset(0, 4)),
                  ],
                ),
                child: TableCalendar(
                  firstDay: DateTime.utc(2020, 1, 1),
                  lastDay: DateTime.utc(2030, 12, 31),
                  focusedDay: _focusedDay,
                  selectedDayPredicate: (day) => isSameDay(_selectedDay, day),
                  onDaySelected: (selectedDay, focusedDay) {
                    setState(() {
                      _selectedDay = selectedDay;
                      _focusedDay = focusedDay;
                    });
                  },
                  eventLoader: (day) => _events[DateTime(day.year, day.month, day.day)] ?? [],
                  calendarStyle: CalendarStyle(
                    todayDecoration: BoxDecoration(
                      color: Colors.orange.shade100, 
                      shape: BoxShape.rectangle, 
                      borderRadius: BorderRadius.circular(8)
                    ),
                    todayTextStyle: const TextStyle(color: Colors.black),
                    selectedDecoration: BoxDecoration(
                      color: cs.primary, 
                      shape: BoxShape.rectangle, 
                      borderRadius: BorderRadius.circular(8)
                    ),
                    markerDecoration: const BoxDecoration(color: Colors.orange, shape: BoxShape.circle),
                    markersMaxCount: 1,
                    outsideDaysVisible: false,
                  ),
                  headerStyle: HeaderStyle(
                    formatButtonVisible: false,
                    titleCentered: false,
                    titleTextStyle: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                    leftChevronIcon: const Icon(Icons.chevron_left, color: Colors.grey),
                    rightChevronIcon: const Icon(Icons.chevron_right, color: Colors.grey),
                  ),
                  daysOfWeekStyle: const DaysOfWeekStyle(
                    weekdayStyle: TextStyle(color: Colors.grey, fontSize: 13),
                    weekendStyle: TextStyle(color: Colors.redAccent, fontSize: 13),
                  ),
                  rowHeight: 50,
                  calendarBuilders: CalendarBuilders(
                    markerBuilder: (context, date, events) {
                      if (events.isNotEmpty) {
                        return Positioned(
                          bottom: 5,
                          child: Container(
                            width: 6,
                            height: 6,
                            decoration: const BoxDecoration(color: Colors.orange, shape: BoxShape.circle),
                          ),
                        );
                      }
                      return null;
                    },
                  ),
                ),
              ),

              // Selected Date Text
              if (_selectedDay != null)
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  child: Text(
                    "${_getDayName(_selectedDay!)}, ${_selectedDay!.day} ${_getMonthName(_selectedDay!.month)} ${_selectedDay!.year}",
                    style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                ),

              // Attendance Details
              Expanded(
                child: selectedEvents.isEmpty
                    ? const Center(child: Text("Tidak ada absensi di tanggal ini", style: TextStyle(color: Colors.grey)))
                    : ListView.builder(
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        itemCount: 1,
                        itemBuilder: (context, index) {
                          dynamic checkIn;
                          dynamic checkOut;
                          for (var e in selectedEvents) {
                            if (e['type'] == 'masuk') checkIn = e;
                            if (e['type'] == 'keluar') checkOut = e;
                          }

                          String timeRange = "-";
                          if (checkIn != null) {
                            final inTime = _parseCreatedAt(checkIn['created_at']);
                            timeRange = inTime != null ? _formatTime(inTime) : "-";
                            if (checkOut != null) {
                              final outTime = _parseCreatedAt(checkOut['created_at']);
                              timeRange += outTime != null ? " - ${_formatTime(outTime)}" : " - -";
                            } else {
                              timeRange += " - --:--";
                            }
                          }

                          return Container(
                            padding: const EdgeInsets.all(16),
                            decoration: BoxDecoration(
                              color: cs.primary.withValues(alpha: 0.12),
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(color: cs.primary.withValues(alpha: 0.2)),
                            ),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Text("Absensi Online", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                                    Text(
                                      "${_getDayName(_selectedDay!)}, ${_selectedDay!.day} ${_getMonthName(_selectedDay!.month)} ${_selectedDay!.year}",
                                      style: const TextStyle(fontSize: 13, color: Colors.black54),
                                    ),
                                  ],
                                ),
                                Text(
                                  timeRange,
                                  style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: cs.primary),
                                ),
                              ],
                            ),
                          );
                        },
                      ),
              ),
            ],
          );
        },
      ),
    );
  }
}

class _HistoryResult {
  final List<dynamic> items;
  final String? errorMessage;

  const _HistoryResult._(this.items, this.errorMessage);

  factory _HistoryResult.ok(List<dynamic> items) => _HistoryResult._(items, null);
  factory _HistoryResult.error(String message) => _HistoryResult._(const [], message);
}
