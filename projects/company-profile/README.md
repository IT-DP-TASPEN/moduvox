<p align="center">
  <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCLSw86U1pEGtzs1uiKWM3FDCxTXUkrkcPQC6F8EtZ8i02xwEA7Bx7FqK58frHxOnKW_pflMOuArTR2buwqA8MMkhz84OHXupEyKPU8HGmilq_wgLB2x2FzOIBDVq_SY5WRMPpJIxHjtecmEvzTTIw55uaOBDPT1PzGT0BiSC_pmO0JxZ75H_j4XVOxVNjF5ahpiCPz_VXxFRSv-Pxpkojkz2MTGmhmeifKLDgJeKBmnbY-IZvspqCT0TiU_G3x8ZW7Rqw5Kp6FadBt" width="100%">
</p>

# PT Dwi Artha Prima - Company Profile & Content Management System

Ini adalah repositori website resmi untuk **PT Dwi Artha Prima** - sebuah perusahaan kontraktor dan penyedia jasa infrastruktur kelas atas. Proyek ini bukan sekadar profil perusahaan statis; ini adalah **Sistem Manajemen Konten Berbasis Halaman (Page-Based CMS)** terpadu yang kuat, yang disesuaikan secara khusus demi fleksibilitas kontrol penuh. Dibangun menggunakan Laravel 11.

## 🌟 Fitur Unggulan

- **Halaman Dinamis Premium:** Tampilan Frontend elegan (Material Design 3 & Tailwind) dengan video latar, animasi, *dark mode capability*, navigasi apik, dan kisi/grid asimetris profesional untuk portofolio proyek.
- **Admin Panel Berbasis Halaman (CMS Mode):** Akses backend terpusat untuk mengubah setiap teks hero, keterangan visi & misi (Teks Halaman), hingga *input* multimedia hanya dengan formulir _user-friendly_ tanpa harus menyentuh sebaris pun kode program!
- **Manajemen Moduler Interaktif:** Termasuk CRUD Lengkap (Create, Read, Update, Delete) untuk daftar *"Layanan"*, *"Klien (Mitra)"*, dan *"Portofolio Proyek Utama"* (termasuk filter kategori & gambar sampul rasio otomatis 16:9).
- **Pengaturan Video & Dokumen:** Formulir canggih yang memfasilitasi penggantian aset khusus seperti MP4 untuk _background_ utama, dan PNG transparan untuk _slider_ logo klien.
- **Single-Admin Security:** Fitur keamanan eksklusif di mana jalur pendaftaran luar ditutup, dan dilindungi oleh *authentication shield* tangguh khusus master Administrator. Termasuk Kotak **Pesan Masuk (Inbox)** bagi pengunjung yang mencoba mengontak via _Contact Us_.

## 🚀 Konfigurasi Tumpukan Teknologi (Tech Stack)

*   **Backend Framework:** Laravel 11 (PHP 8.2+) dengan arsitektur Database Relasional MySQL
*   **Authentication & Keamanan:** Laravel Breeze
*   **Frontend UI & UX:** Tailwind CSS v3 dengan utilitas khusus (Aspect Ratio, Forms, Material Icons, Google Fonts: Inter & Manrope).

## 🛠️ Panduan Instalasi (Development Setup)

1. Clone repositori ke mesin lokal Anda:
   ```bash
   git clone https://github.com/ferrianmfatichin97/dwiarthaprima.git
   cd dwi-artha-prima
   ```

2. Unduh semua paket ekstensi (Dependencies):
   ```bash
   composer install
   npm install
   ```

3. Setup Lingkungan (_Environment_):
   - Gandakan (Copy) `.env.example` ke file batu bernama `.env`.
   - Buka pengaturan `.env` lalu hubungkan database Anda:
     ```env
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=dwi_artha_prima
     DB_USERNAME=root
     DB_PASSWORD=
     ```

4. Bangkitkan Kunci rahasia situs Anda, lalu satukan (_migrate & seed_) fondasi awal Database:
   ```bash
   php artisan key:generate
   php artisan migrate --seed
   ```
   > **Sangat Penting:** `migrate --seed` akan memanggil `DatabaseSeeder` yang langsung menciptakan kredensial Administrator utama serta *Dummy Data* proyek, dan tautan teks asli Anda.

5. Pautkan Ruang Penyimpanan Gambar & Video ke jaringan tatanan File Publik:
   ```bash
   php artisan storage:link
   ```

6. Jalankan Server:
   - Server Artisan: `php artisan serve` (atau `.\artisan serve` untuk pengguna Shortcut khusus Windows Anda).
   - *Hot-reload* Aset Web (Tailwind Frontend): `npm run dev`

Buka browser Anda di `http://127.0.0.1:8000`.

## 🔒 Akses Panel Admin
Anda bisa masuk menuju pusat kontrol website (CMS Backend) via tautan: `http://localhost:8000/login`

**Akun Administrator Tunggal bawaan (*Seeder*):**
> **Email:** `admin@dwiarthaprima.com` <br>
> **Sandi:** `password`

Terima kasih. Website ini dirancang dengan presisi & integritas, khusus untuk menyongsong inovasi digital Dwi Artha Prima di masa lampau, kiwari, hingga masa depan. 🏗️🌐
