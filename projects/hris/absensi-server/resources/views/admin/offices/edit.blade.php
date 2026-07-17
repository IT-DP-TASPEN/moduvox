@extends('admin.layout')

@section('header', 'Edit Kantor')

@section('content')
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places&callback=initMap" async defer></script>

<div class="max-w-4xl">
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <form action="{{ route('admin.offices.update', $office) }}" method="POST" class="p-8 space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-700">Kode Kantor</label>
                    <input type="text" name="code" value="{{ old('code', $office->code) }}" placeholder="Contoh: KP, KC-01" 
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-blue focus:border-transparent transition-all" required>
                    @error('code') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-700">Nama Kantor</label>
                    <input type="text" name="name" value="{{ old('name', $office->name) }}" placeholder="Contoh: Kantor Pusat"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-blue focus:border-transparent transition-all" required>
                </div>
            </div>

            <!-- MAP PICKER WITH SEARCH -->
            <div class="space-y-4">
                <div class="flex gap-2">
                    <input type="text" id="search-address" placeholder="Cari alamat atau nama tempat..." 
                        class="flex-1 px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-blue focus:border-transparent transition-all text-sm">
                </div>
                <div id="map" class="h-80 rounded-2xl border border-slate-200 z-0"></div>
                <p class="text-[11px] text-slate-400">Tarik marker atau klik peta untuk mengubah lokasi.</p>
            </div>

            <!-- Alamat Section -->
            <div class="space-y-2">
                <label class="text-sm font-bold text-slate-700">Alamat Lengkap</label>
                <textarea id="address" name="address" rows="3" 
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all text-sm"
                    placeholder="Alamat akan terisi otomatis saat memilih lokasi di peta..." required>{{ old('address', $office->address) }}</textarea>
                @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Latitude</label>
                    <input type="text" id="lat" name="latitude" value="{{ old('latitude', $office->latitude) }}"
                        class="w-full px-6 py-4 rounded-2xl bg-slate-100 border-none font-bold text-slate-500 cursor-not-allowed" readonly required>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Longitude</label>
                    <input type="text" id="lng" name="longitude" value="{{ old('longitude', $office->longitude) }}"
                        class="w-full px-6 py-4 rounded-2xl bg-slate-100 border-none font-bold text-slate-500 cursor-not-allowed" readonly required>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Radius (Meter)</label>
                    <input type="number" id="radius" name="radius" value="{{ old('radius', $office->radius) }}"
                        class="w-full px-6 py-4 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-brand-blue transition-all font-bold text-slate-700" required>
                </div>
            </div>

            <script>
                let map;
                let marker;
                let circle;
                let autocomplete;
                let geocoder;

                function initMap() {
                    const lat = {{ $office->latitude }};
                    const lng = {{ $office->longitude }};
                    const rad = {{ $office->radius }};
                    const center = { lat: lat, lng: lng };

                    geocoder = new google.maps.Geocoder();
                    map = new google.maps.Map(document.getElementById("map"), {
                        center: center,
                        zoom: 16,
                        mapTypeControl: false,
                        streetViewControl: false,
                        fullscreenControl: false,
                    });

                    marker = new google.maps.Marker({
                        position: center,
                        map: map,
                        draggable: true,
                    });

                    circle = new google.maps.Circle({
                        strokeColor: "#6366f1",
                        strokeOpacity: 0.8,
                        strokeWeight: 2,
                        fillColor: "#6366f1",
                        fillOpacity: 0.2,
                        map: map,
                        center: center,
                        radius: rad,
                    });

                    // Search functionality
                    const input = document.getElementById("search-address");
                    autocomplete = new google.maps.places.Autocomplete(input);
                    autocomplete.bindTo("bounds", map);

                    autocomplete.addListener("place_changed", () => {
                        const place = autocomplete.getPlace();
                        if (!place.geometry || !place.geometry.location) return;

                        if (place.geometry.viewport) {
                            map.fitBounds(place.geometry.viewport);
                        } else {
                            map.setCenter(place.geometry.location);
                            map.setZoom(17);
                        }

                        marker.setPosition(place.geometry.location);
                        updateFields(place.geometry.location.lat(), place.geometry.location.lng(), place.formatted_address);
                    });

                    // Click to move marker
                    map.addListener("click", (e) => {
                        marker.setPosition(e.latLng);
                        reverseGeocode(e.latLng);
                    });

                    // Drag marker
                    marker.addListener("dragend", () => {
                        const pos = marker.getPosition();
                        reverseGeocode(pos);
                    });
                }

                function reverseGeocode(latLng) {
                    geocoder.geocode({ location: latLng }, (results, status) => {
                        if (status === "OK" && results[0]) {
                            updateFields(latLng.lat(), latLng.lng(), results[0].formatted_address);
                        } else {
                            updateFields(latLng.lat(), latLng.lng());
                        }
                    });
                }

                function updateFields(lat, lng, address = null) {
                    document.getElementById('lat').value = lat.toFixed(6);
                    document.getElementById('lng').value = lng.toFixed(6);
                    if (address) {
                        document.getElementById('address').value = address;
                    }
                    circle.setCenter({ lat: lat, lng: lng });
                }

                document.getElementById('radius').oninput = function() {
                    circle.setRadius(parseFloat(this.value));
                }

                // Initialize Google Maps is now handled by callback param in script tag
            </script>

            <div class="pt-4 flex gap-3">
                <button type="submit" class="bg-brand-blue text-white px-8 py-3 rounded-2xl font-bold hover:opacity-90 transition-all shadow-lg shadow-blue-100">
                    Update Kantor
                </button>
                <a href="{{ route('admin.offices.index') }}" class="bg-slate-100 text-slate-600 px-8 py-3 rounded-2xl font-bold hover:bg-slate-200 transition-all">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
