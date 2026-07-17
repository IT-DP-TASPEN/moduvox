# HRIS ABSENSI - Bank DP Taspen

Sistem Manajemen Kehadiran dan SDM terintegrasi untuk Bank DP Taspen. Sistem ini terdiri dari **Web Admin Portal** (Laravel) dan **Mobile Application** (Flutter).

## 🚀 Fitur Utama

### Mobile App (Pegawai)
- **Absensi Real-time**: Check-in/out berbasis lokasi (Geofencing) dan Foto Selfie.
- **Pengajuan Cuti**: Manajemen cuti dengan alur approval bertingkat.
- **Izin & Lembur**: Pengajuan izin kerja dan lembur secara digital.
- **Tugas Luar**: Pencatatan aktivitas di luar kantor dengan validasi koordinat.
- **Profil HR**: Akses data kepegawaian dan riwayat kehadiran pribadi.

### Web Admin (Manajemen)
- **Dashboard Monitoring**: Pantau kehadiran pegawai secara realtime.
- **Master Data**: Pengelolaan data karyawan, divisi, dan kantor.
- **Approval System**: Panel khusus untuk menyetujui pengajuan cuti, izin, dan lembur.
- **Geofencing Config**: Pengaturan lokasi kantor dan radius absensi menggunakan Google Maps.
- **Laporan**: Rekapitulasi kehadiran untuk kebutuhan payroll.

## 🛠️ Tech Stack

- **Backend**: Laravel 11, Filament/Custom Admin.
- **Mobile**: Flutter (Material 3), Google Maps Flutter SDK.
- **Database**: MySQL.
- **Maps API**: Google Maps JavaScript & SDK API.

## ⚙️ Persyaratan Sistem

- PHP >= 8.2
- Composer
- Flutter SDK >= 3.22
- MySQL / MariaDB

## 📦 Instalasi Backend

1. **Clone Repository**
   ```bash
   git clone https://github.com/IT-DP-TASPEN/hris-DP-Taspen.git
   cd hris-DP-Taspen/absensi-server
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install && npm run build
   ```

3. **Konfigurasi Environment**
   Salin `.env.example` ke `.env` dan lengkapi data berikut:
   ```env
   APP_URL=https://hris.bankdptaspn.co.id
   DB_DATABASE=db_absensi
   DB_USERNAME=bankdptaspen
   DB_PASSWORD=Dptaspen25!
   GOOGLE_MAPS_API_KEY=AIzaSy...
   ```

4. **Setup Database & Key**
   ```bash
   php artisan key:generate
   php artisan migrate --seed
   php artisan storage:link
   ```

## 📱 Instalasi Mobile

1. **Konfigurasi Maps**
   - iOS: Masukkan API Key di `ios/Flutter/MapsKeys.xcconfig`.
   - Android: Masukkan API Key di `android/maps.properties`.

2. **Build / Run**
   Gunakan flag `API_BASE_URL` untuk mengarahkan ke server produksi:
   ```bash
   flutter run --dart-define=API_BASE_URL=https://hris.bankdptaspn.co.id/api
   ```

## 🔒 Keamanan & Produksi
- **PIN Hashing**: Semua PIN user disimpan menggunakan enkripsi Bcrypt.
- **Rate Limiting**: API dilindungi dengan pembatasan 60 request per menit per user.
- **Data Masking**: Password dan PIN disembunyikan secara otomatis dari response API.

---
© 2024 IT Team - Bank DP Taspen
