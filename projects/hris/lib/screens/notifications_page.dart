import 'package:flutter/material.dart';
import 'dart:convert';
import '../services/api_service.dart';

class NotificationsPage extends StatefulWidget {
  const NotificationsPage({super.key});

  @override
  State<NotificationsPage> createState() => _NotificationsPageState();
}

class _NotificationsPageState extends State<NotificationsPage> {
  final ApiService _apiService = ApiService();
  bool _isLoading = true;
  List<dynamic> _notifications = [];

  @override
  void initState() {
    super.initState();
    _fetchNotifications();
  }

  Future<void> _fetchNotifications() async {
    setState(() => _isLoading = true);
    try {
      final response = await _apiService.getNotifications();
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['status'] == 'success') {
          setState(() {
            _notifications = data['data'];
          });
        }
      }
    } catch (e) {
      debugPrint("Error fetching notifications: $e");
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _markAsRead(String id, int index) async {
    try {
      final response = await _apiService.markNotificationAsRead(id);
      if (response.statusCode == 200) {
        setState(() {
          _notifications[index]['read_at'] = DateTime.now().toIso8601String();
        });
      }
    } catch (e) {
      debugPrint("Error marking read: $e");
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Notifikasi'),
        backgroundColor: const Color(0xFF004A99),
        foregroundColor: Colors.white,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _notifications.isEmpty
              ? const Center(child: Text('Belum ada notifikasi.'))
              : RefreshIndicator(
                  onRefresh: _fetchNotifications,
                  child: ListView.builder(
                    itemCount: _notifications.length,
                    itemBuilder: (context, index) {
                      final item = _notifications[index];
                      final bool isRead = item['read_at'] != null;

                      return Container(
                        color: isRead ? Colors.transparent : Colors.blue.withOpacity(0.05),
                        child: ListTile(
                          leading: CircleAvatar(
                            backgroundColor: isRead ? Colors.grey[200] : Colors.blue[100],
                            child: Icon(
                              Icons.notifications,
                              color: isRead ? Colors.grey : Colors.blue,
                            ),
                          ),
                          title: Text(
                            item['title'] ?? 'Notifikasi',
                            style: TextStyle(
                              fontWeight: isRead ? FontWeight.normal : FontWeight.bold,
                            ),
                          ),
                          subtitle: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const SizedBox(height: 4),
                              Text(item['message'] ?? ''),
                              const SizedBox(height: 4),
                              Text(
                                item['created_at'] ?? '',
                                style: const TextStyle(fontSize: 12, color: Colors.grey),
                              ),
                            ],
                          ),
                          onTap: () {
                            if (!isRead) {
                              _markAsRead(item['id'].toString(), index);
                            }
                          },
                        ),
                      );
                    },
                  ),
                ),
    );
  }
}
