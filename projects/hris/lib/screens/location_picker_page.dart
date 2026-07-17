import 'package:flutter/material.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:latlong2/latlong.dart' as ll;
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:geolocator/geolocator.dart';

class LocationPickerPage extends StatefulWidget {
  final ll.LatLng initialCenter;
  const LocationPickerPage({super.key, required this.initialCenter});

  @override
  State<LocationPickerPage> createState() => _LocationPickerPageState();
}

class _LocationPickerPageState extends State<LocationPickerPage> {
  late LatLng _selectedLatLng;
  GoogleMapController? _mapController;
  final TextEditingController _searchController = TextEditingController();
  List<dynamic> _searchResults = [];
  bool _isSearching = false;

  @override
  void initState() {
    super.initState();
    _selectedLatLng = LatLng(widget.initialCenter.latitude, widget.initialCenter.longitude);
  }

  Future<void> _searchLocation(String query) async {
    if (query.isEmpty) return;
    setState(() => _isSearching = true);
    try {
      // 🇮🇩 Limit results to Indonesia (countrycodes=id)
      // 📍 Bias results near current location if available
      final double lat = _selectedLatLng.latitude;
      final double lon = _selectedLatLng.longitude;
      
      // Viewbox around current location (approx 1 degree ~ 111km)
      final String viewbox = "${lon - 0.5},${lat - 0.5},${lon + 0.5},${lat + 0.5}";
      
      final url = Uri.parse(
        'https://nominatim.openstreetmap.org/search?'
        'q=$query&format=json&limit=10&countrycodes=id&viewbox=$viewbox'
      );
      
      final response = await http.get(url, headers: {'User-Agent': 'BankDPTaspenAbsensi/1.0'});
      if (response.statusCode == 200) {
        setState(() {
          _searchResults = jsonDecode(response.body);
        });
      }
    } catch (e) {
      debugPrint("Search error: $e");
    } finally {
      setState(() => _isSearching = false);
    }
  }

  void _moveToLocation(double lat, double lon) {
    final newPos = LatLng(lat, lon);
    setState(() {
      _selectedLatLng = newPos;
      _searchResults = [];
      _searchController.clear();
    });
    _mapController?.animateCamera(CameraUpdate.newLatLng(newPos));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Stack(
        children: [
          GoogleMap(
            initialCameraPosition: CameraPosition(
              target: _selectedLatLng,
              zoom: 16,
            ),
            onMapCreated: (controller) => _mapController = controller,
            onTap: (point) {
              setState(() => _selectedLatLng = point);
            },
            markers: {
              Marker(
                markerId: const MarkerId('selected'),
                position: _selectedLatLng,
                draggable: true,
                onDragEnd: (newPosition) {
                  setState(() => _selectedLatLng = newPosition);
                },
              ),
            },
            myLocationEnabled: true,
            myLocationButtonEnabled: false,
            zoomControlsEnabled: false,
            mapToolbarEnabled: false,
          ),
          
          // 🔍 SEARCH BAR (Floating)
          Positioned(
            top: MediaQuery.of(context).padding.top + 10,
            left: 15,
            right: 15,
            child: Column(
              children: [
                Container(
                  height: 50,
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(25),
                    boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.1), blurRadius: 10, offset: const Offset(0, 2))],
                  ),
                  child: TextField(
                    controller: _searchController,
                    textInputAction: TextInputAction.search,
                    decoration: InputDecoration(
                      hintText: "Ketik nama tempat lalu cari...",
                      hintStyle: const TextStyle(color: Colors.grey, fontSize: 13),
                      prefixIcon: IconButton(
                        icon: const Icon(Icons.arrow_back, color: Colors.grey),
                        onPressed: () => Navigator.pop(context),
                      ),
                      suffixIcon: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          if (_searchController.text.isNotEmpty)
                            IconButton(
                              icon: const Icon(Icons.close, color: Colors.grey, size: 20),
                              onPressed: () {
                                _searchController.clear();
                                setState(() => _searchResults = []);
                              },
                            ),
                          if (_isSearching)
                            const Padding(
                              padding: EdgeInsets.all(12.0),
                              child: SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2)),
                            )
                          else
                            IconButton(
                              icon: const Icon(Icons.search, color: Color(0xFF004A99)),
                              onPressed: () => _searchLocation(_searchController.text),
                            ),
                          const SizedBox(width: 8),
                        ],
                      ),
                      border: InputBorder.none,
                      contentPadding: const EdgeInsets.symmetric(vertical: 12),
                    ),
                    onChanged: (v) => setState(() {}),
                    onSubmitted: _searchLocation,
                  ),
                ),

                // 📍 SEARCH RESULTS
                if (_searchResults.isNotEmpty)
                  Container(
                    margin: const EdgeInsets.only(top: 10),
                    constraints: const BoxConstraints(maxHeight: 300),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(15),
                      boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.1), blurRadius: 10)],
                    ),
                    child: ListView.separated(
                      shrinkWrap: true,
                      padding: EdgeInsets.zero,
                      itemCount: _searchResults.length,
                      separatorBuilder: (_, __) => const Divider(height: 1),
                      itemBuilder: (context, index) {
                        final res = _searchResults[index];
                        return ListTile(
                          dense: true,
                          leading: const Icon(Icons.location_on_outlined, color: Colors.grey, size: 20),
                          title: Text(
                            res['display_name'], 
                            style: const TextStyle(fontSize: 12),
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                          ),
                          onTap: () => _moveToLocation(double.parse(res['lat']), double.parse(res['lon'])),
                        );
                      },
                    ),
                  ),
              ],
            ),
          ),

          // 🎯 MY LOCATION (Top Right)
          Positioned(
            right: 15,
            top: MediaQuery.of(context).padding.top + 70,
            child: _buildMapButton(Icons.my_location, () async {
              Position position = await Geolocator.getCurrentPosition();
              _moveToLocation(position.latitude, position.longitude);
            }),
          ),

          // ✅ SELECT LOCATION BUTTON (Bottom)
          Positioned(
            bottom: 25,
            left: 20,
            right: 20,
            child: SizedBox(
              height: 55,
              child: ElevatedButton(
                onPressed: () => Navigator.pop(context, ll.LatLng(_selectedLatLng.latitude, _selectedLatLng.longitude)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF004A99),
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  elevation: 5,
                ),
                child: const Text(
                  "Pilih lokasi",
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMapButton(IconData icon, VoidCallback onTap) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(8),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.1), blurRadius: 5)],
      ),
      child: IconButton(
        icon: Icon(icon, color: const Color(0xFF555555)),
        onPressed: onTap,
      ),
    );
  }
}
