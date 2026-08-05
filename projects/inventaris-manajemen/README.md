<p align="center">
  <img src="public/images/logo-bank-dp-taspen.png" alt="Logo Bank DP Taspen" width="300">
</p>

# Inventaris v2 — Sistem Informasi Manajemen Inventaris & Penyusutan
### PT BPR Dana Pensiun Taspen

Sistem Inventaris v2 adalah aplikasi berbasis web yang dibangun menggunakan **Laravel 13** untuk mengelola data inventaris perusahaan secara menyeluruh — mulai dari pencatatan aset, perhitungan penyusutan otomatis, hingga pengiriman jurnal ke Core Banking (FinCloud). Aplikasi ini merupakan versi penyempurnaan (v2) yang terintegrasi langsung dengan API Core General Ledger (GL).

---

## Daftar Modul

### 1. 🏠 Dashboard
Halaman utama yang menampilkan ringkasan statistik seluruh aset perusahaan, termasuk jumlah total aset, nilai perolehan, nilai buku, dan distribusi aset per cabang/golongan.

### 2. 📦 Manajemen Inventaris
Modul inti untuk pencatatan dan pengelolaan seluruh aset perusahaan.
- **Daftar Inventaris Umum** — CRUD data aset lengkap (nama, rekening, harga perolehan, lokasi, golongan, dll).
- **Daftar Kendaraan Bermotor** — Sub-modul khusus untuk pencatatan detail kendaraan (nomor polisi, nomor rangka, merk, dll).
- **Daftar Tanah & Bangunan** — Sub-modul khusus pencatatan detail tanah (luas, sertifikat, alamat, dll).
- **Daftar Penyusutan per Aset** — Melihat seluruh aset beserta histori nilai penyusutannya.
- **Histori Transaksi/Mutasi** — Pencatatan perpindahan aset antar cabang/ruangan.
- **Import Data** — Import data aset dan mutasi dari file Excel.
- **Cetak Label/Barcode** — Cetak label inventaris satuan maupun massal.

### 3. 📊 Penyusutan (Depreciation)
Modul perhitungan penyusutan otomatis berdasarkan metode garis lurus.
- **Generate Batch Penyusutan** — Proses penyusutan bulanan secara batch untuk seluruh aset yang memenuhi syarat.
- **Review Batch (Draft)** — Preview rincian penyusutan sebelum disetujui.
- **Approve & Kirim Jurnal** — Persetujuan batch lalu otomatis mengirim jurnal ke Core Banking (GL to GL).
- **Histori Batch** — Daftar seluruh batch penyusutan yang telah diproses.
- **Catatan:** Aset golongan **Tanah & Gedung** (kode 01) dikecualikan dari penyusutan.

### 4. 📒 Histori Jurnal API
Modul pemantauan seluruh transaksi jurnal yang dikirim ke Core Banking.
- **Daftar Jurnal** — Melihat semua jurnal yang pernah dikirim beserta statusnya (Success/Failed).
- **Detail Jurnal** — Melihat detail payload JSON yang dikirim ke API Core.
- **Retry Jurnal Gagal** — Mengirim ulang jurnal yang gagal secara otomatis.

### 5. 📄 Laporan
Modul cetak dan ekspor laporan dalam format **PDF (Print)** dan **Excel (.xls)**.
- **Laporan Rincian Nominatif Aktiva Tetap & Inventaris** — Laporan lengkap seluruh aset dikelompokkan per golongan, termasuk nilai perolehan, penyusutan, akumulasi, dan nilai buku.
- **Laporan Penyusutan** — Laporan khusus aset yang mengalami penyusutan pada periode tertentu, dikelompokkan **per cabang** lalu **per golongan**. Aset Tanah & Gedung dikecualikan.
- **Logo Bank DP Taspen** otomatis tampil di header laporan cetak.
- **Format Excel** mendukung `mso-number-format` agar kolom nominal terbaca sebagai angka dan nomor rekening tetap sebagai teks.

### 6. ⚙️ Data Master
Konfigurasi referensi data yang digunakan di seluruh aplikasi.
- **Kantor Cabang** — Daftar cabang (KPO, Bogor, Bekasi, dll).
- **Golongan Aset** — Kategori aset (Tanah & Gedung, Gol I–IV, ATB) beserta umur standar dan COA akun debet/kredit.
- **Jenis Aset** — Jenis inventaris (Elektronik, Furniture, dll).
- **Lokasi** — Lokasi fisik penempatan aset.
- **Ruangan** — Ruangan per kantor cabang.
- **Sumber Dana** — Asal pendanaan perolehan aset.

### 7. 🔐 Sistem & Keamanan
- **Manajemen User** — CRUD user dengan assign role (Spatie Permission).
- **Role-Based Access:**
  - `Super Admin` — Akses penuh ke seluruh modul.
  - `Akunting Maker` — Staff akunting, input dan review data.
  - `Akunting Checker` — Manager akunting, approval penyusutan.
  - `Audit` — Auditor, akses read-only.
  - `Cabang` — User cabang, akses terbatas per cabang.
- **Audit Trail** — Rekam jejak seluruh aktivitas pengguna di dalam sistem.
- **Profile** — Ubah password akun.

---

## Tech Stack

| Komponen | Teknologi |
|---|---|
| **Backend** | Laravel 13 (PHP 8.3) |
| **Frontend** | Blade + Tailwind CSS (Vite) |
| **Database** | MySQL / MariaDB |
| **UI Library** | DataTables, SweetAlert2, Toastr, Alpine.js |
| **Icons** | FontAwesome 6.5 |
| **Font** | Google Fonts (Inter) |
| **Auth & RBAC** | Spatie Laravel-Permission |
| **API Integration** | FinCloud Core Banking (GL to GL) |

---

## Persyaratan Sistem

- **PHP** >= 8.2
- **Composer** >= 2.0
- **Node.js** >= 18
- **Database** MySQL / MariaDB
- **Ekstensi PHP:** OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype, JSON, cURL.

---

## Panduan Instalasi

### Development (Lokal)

1. Clone repositori:
   ```bash
   git clone https://github.com/IT-DP-TASPEN/inventaris-v2.git
   cd inventaris-v2
   ```

2. Salin file `.env.example` menjadi `.env` dan sesuaikan konfigurasi database:
   ```bash
   cp .env.example .env
   ```

3. Install dependencies:
   ```bash
   composer install
   npm install && npm run build
   ```

4. Generate Application Key:
   ```bash
   php artisan key:generate
   ```

5. Migrasi Database dan jalankan Seeder:
   ```bash
   php artisan migrate --seed
   ```
   > Seeder akan otomatis mengisi: Role & Permission, Data Master (Golongan, Kantor, dll), dan User default.

6. Jalankan server:
   ```bash
   php artisan serve
   ```

### Production (Server)

1. Upload source code ke server.
2. Import database dari file dump:
   ```bash
   mysql -u [user] -p [nama_database] < db_inventaris_v2_export.sql
   ```
3. Sesuaikan file `.env` (database, APP_URL, APP_ENV=production, APP_DEBUG=false).
4. Install dependencies:
   ```bash
   composer install --optimize-autoloader --no-dev
   npm install && npm run build
   ```
5. Optimasi Laravel:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

---

## Konfigurasi API Core (GL)

Pastikan di dalam file `.env` sudah terdapat parameter kredensial API:
```env
<<<<<<< HEAD
GL_API_ENDPOINT=http://[IP_CORE]:17000/trx/transfer/gl-to-gl
GL_API_SECRET="[SECRET_KEY]"
=======
GL_API_ENDPOINT=#########
GL_API_SECRET=###########
>>>>>>> 8060eca74c0ebf6a830722d939c11a49bc21fb64
```
Konfigurasi ini digunakan oleh `FinCloudApiService` untuk mengirimkan batch data penyusutan melalui skema JSON beserta Header `Signature` (SHA256 HMAC).

---

## Akun Default (Seeder)

| Nama | Email | Password | Role |
|---|---|---|---|
| Admin Super | adminsuper@bankdptaspen.co.id | password | Super Admin |
| Rhomandani Mustika B. | rhomandanimustika@gmail.com | DPT@SP3n | Akunting Maker |
| Bayu Oryan | oryanhs.bayu64@gmail.com | DPT@SP3n | Akunting Maker |
| Dini Dwi Utami | dinidwiutami@yahoo.com | DPT@SP3n | Akunting Checker |
| Dwi Sulastri | dwisulastri512@gmail.com | DPT@SP3n | Akunting Checker |
| Tiara Adrianti | tiara.adrianti@yahoo.com | DPT@SP3n | Akunting Maker |
| Indriani Hardianti | hardiant755@gmail.com | DPT@SP3n | Akunting Maker |
| Elsa Febriyansi | elssyi02@gmail.com | DPT@SP3n | Akunting Maker |
| Wisnu Cipto Baskoro | nubaskoro@gmail.com | DPT@SP3n | Akunting Checker |

> ⚠️ **Segera ganti password default setelah deploy ke production!**

---

## Struktur Direktori Utama

```
inventaris-v2/
├── app/
│   ├── Http/Controllers/      # Controller utama
│   ├── Models/                 # Eloquent Models
│   ├── Services/               # FinCloudApiService, PayloadBuilder
│   └── Helpers/                # FormatHelper (rupiah, tanggal Indonesia)
├── database/
│   ├── migrations/             # Skema tabel
│   └── seeders/                # RolePermission, MasterData, User
├── resources/views/
│   ├── layouts/                # Layout utama + sidebar + topbar
│   ├── dashboard/              # Dashboard
│   ├── inventaris/             # CRUD Inventaris
│   ├── motor/                  # Daftar Kendaraan
│   ├── tanah/                  # Daftar Tanah
│   ├── penyusutan/             # Batch Penyusutan
│   ├── penyusutan_list/        # Daftar Penyusutan per Aset
│   ├── journals/               # Histori Jurnal API
│   ├── reports/                # Cetak Laporan (Nominatif & Penyusutan)
│   ├── transaksi/              # Histori Mutasi
│   ├── master/                 # CRUD Data Master
│   ├── system/                 # Manajemen User & Audit Trail
│   └── profile/                # Profil User
├── routes/web.php              # Routing utama
└── public/images/              # Logo dan aset gambar
```

---

## Lisensi

Aplikasi ini dikembangkan secara internal untuk **PT BPR Dana Pensiun Taspen**. Hak cipta dilindungi.
