import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:flutter/foundation.dart';
import 'package:latlong2/latlong.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart' as gmaps;

class AppMapWidget extends StatefulWidget {
  final LatLng center;
  final bool useGoogleMaps;
  final LatLng? officeLocation;
  final double? radius;
  final double height;
  final bool myLocationEnabled;
  final bool liteModeEnabled;

  const AppMapWidget({
    super.key,
    required this.center,
    this.useGoogleMaps = true,
    this.officeLocation,
    this.radius,
    this.height = 300,
    this.myLocationEnabled = false,
    this.liteModeEnabled = true,
  });

  @override
  State<AppMapWidget> createState() => _AppMapWidgetState();
}

class _AppMapWidgetState extends State<AppMapWidget> {
  final MapController _mapController = MapController();
  gmaps.GoogleMapController? _googleMapController;

  @override
  void dispose() {
    _googleMapController?.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: widget.height,
      child: ClipRRect(
        borderRadius: BorderRadius.circular(16),
        child: widget.useGoogleMaps ? _buildGoogleMap() : _buildOsmMap(),
      ),
    );
  }

  Widget _buildGoogleMap() {
    final gmaps.LatLng center = gmaps.LatLng(
      widget.center.latitude,
      widget.center.longitude,
    );

    final Set<gmaps.Marker> markers = {};

    markers.add(
      gmaps.Marker(
        markerId: const gmaps.MarkerId('user_location'),
        position: center,
        icon: gmaps.BitmapDescriptor.defaultMarkerWithHue(gmaps.BitmapDescriptor.hueRed),
        infoWindow: const gmaps.InfoWindow(title: 'Lokasi Anda'),
      ),
    );

    if (widget.officeLocation != null) {
      markers.add(
        gmaps.Marker(
          markerId: const gmaps.MarkerId('office_location'),
          position: gmaps.LatLng(
            widget.officeLocation!.latitude,
            widget.officeLocation!.longitude,
          ),
          icon: gmaps.BitmapDescriptor.defaultMarkerWithHue(gmaps.BitmapDescriptor.hueBlue),
          infoWindow: const gmaps.InfoWindow(title: 'Kantor'),
        ),
      );
    }

    final Set<gmaps.Circle> circles = {};
    if (widget.officeLocation != null && widget.radius != null) {
      circles.add(
        gmaps.Circle(
          circleId: const gmaps.CircleId('office_radius'),
          center: gmaps.LatLng(
            widget.officeLocation!.latitude,
            widget.officeLocation!.longitude,
          ),
          radius: widget.radius!,
          fillColor: Colors.blue.withValues(alpha: 0.15),
          strokeColor: Colors.blue,
          strokeWidth: 2,
        ),
      );
    }

    return gmaps.GoogleMap(
      initialCameraPosition: gmaps.CameraPosition(
        target: center,
        zoom: 16,
      ),
      markers: markers,
      circles: circles,
      myLocationEnabled: widget.myLocationEnabled,
      myLocationButtonEnabled: false,
      zoomControlsEnabled: false,
      mapToolbarEnabled: false,
      compassEnabled: false,
      trafficEnabled: false,
      buildingsEnabled: false,
      indoorViewEnabled: false,
      liteModeEnabled: widget.liteModeEnabled && !kIsWeb && defaultTargetPlatform == TargetPlatform.android,
      rotateGesturesEnabled: false,
      tiltGesturesEnabled: false,
      onMapCreated: (controller) {
        _googleMapController = controller;
      },
    );
  }

  Widget _buildOsmMap() {
    final cs = Theme.of(context).colorScheme;
    return FlutterMap(
      mapController: _mapController,
      options: MapOptions(
        initialCenter: widget.center,
        initialZoom: 16,
      ),
      children: [
        TileLayer(
          urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
          userAgentPackageName: 'id.co.bankdptaspen.absensi',
        ),
        if (widget.officeLocation != null && widget.radius != null)
          CircleLayer(
            circles: [
              CircleMarker(
                point: widget.officeLocation!,
                radius: widget.radius!,
                useRadiusInMeter: true,
                color: cs.primary.withValues(alpha: 0.15),
                borderColor: cs.primary,
                borderStrokeWidth: 2,
              ),
            ],
          ),
        MarkerLayer(
          markers: [
            if (widget.officeLocation != null)
              Marker(
                point: widget.officeLocation!,
                width: 40,
                height: 40,
                child: Icon(
                  Icons.business,
                  color: cs.primary,
                  size: 30,
                ),
              ),
            Marker(
              point: widget.center,
              width: 50,
              height: 50,
              child: Icon(
                Icons.my_location,
                color: cs.error,
                size: 40,
              ),
            ),
          ],
        ),
      ],
    );
  }
}
