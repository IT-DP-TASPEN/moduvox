# HRIS ABSENSI Server - Moduvox

Backend API dan Admin Portal untuk sistem absensi Moduvox.

## Setup Cepat (Produksi)

1. **Environment**
   Pastikan `.env` sudah terisi dengan:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://172.116.31.2:8026
   GOOGLE_MAPS_API_KEY=AIzaSy...
   ```

2. **Database & Optimization**
   ```bash
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan storage:link
   ```

## Fitur Backend
- **RESTful API**: Untuk konsumsi aplikasi Flutter.
- **Geofencing**: Validasi lokasi absensi.
- **Multi-level Approval**: Alur persetujuan Cuti, Izin, Lembur, dan Tugas Luar.
- **Reporting**: Ekspor data kehadiran.

---
IT Team - Moduvox
