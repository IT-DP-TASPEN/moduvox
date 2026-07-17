# Moduvox Enterprise

**Moduvox Enterprise** adalah *Enterprise Application Experience Center* yang didesain khusus untuk menampilkan kapabilitas, simulasi interaktif, dan portofolio produk perangkat lunak operasional tingkat _enterprise_.

## 🌟 Fitur Utama
- **Showcase Interaktif:** Pengalaman menggunakan simulasi aplikasi operasional (HRIS, CRM, Core Banking Koperasi, SIARDI).
- **Katalog Portofolio:** Daftar lengkap seluruh modul dan aplikasi yang telah dibangun untuk beragam sektor.
- **Role Switcher:** Simulasi tampilan antarmuka berdasarkan _role_ pengguna (contoh: Admin, Manajer, Karyawan).
- **Desain Responsif & Modern:** Antarmuka responsif tingkat tinggi yang rapi dan elegan.

## 🛠️ Teknologi yang Digunakan
- **[React](https://reactjs.org/)** (v19) untuk _frontend library_.
- **[Vite](https://vitejs.dev/)** untuk _build tool_ yang super cepat.
- **[Tailwind CSS v4](https://tailwindcss.com/)** & **CSS Modules** untuk tata letak dan _styling_.
- **[Lucide React](https://lucide.dev/)** dan **Material Symbols** untuk ikonografi.
- **[Framer Motion](https://www.framer.com/motion/)** untuk animasi halus.
- **[React Router DOM](https://reactrouter.com/)** untuk navigasi halaman (_routing_).

## 🚀 Instalasi dan Menjalankan Proyek

Ikuti langkah-langkah di bawah ini untuk menjalankan Moduvox secara lokal:

1. **Clone repository ini**
   ```bash
   git clone https://github.com/IT-DP-TASPEN/moduvox.git
   cd moduvox
   ```

2. **Instal dependensi**
   ```bash
   npm install
   ```

3. **Jalankan server pengembangan**
   ```bash
   npm run dev
   ```

4. **Mulai Mengeksplorasi**
   Buka [http://localhost:5173](http://localhost:5173) di browser favorit Anda.

## 📁 Struktur Direktori

```text
src/
├── assets/        # Aset gambar, ikon statis, dan logo
├── components/    # Komponen React yang dapat digunakan ulang
│   ├── home/      # Bagian-bagian penyusun halaman utama (Beranda)
│   ├── layout/    # Tata letak pembungkus (Navbar, Footer)
│   ├── showcase/  # Komponen simulator aplikasi dan portofolio UI
│   └── ui/        # Komponen fundamental (Tombol, Badge, Animasi, dll)
├── data/          # Konfigurasi data statis (informasi produk & modul)
├── pages/         # Tampilan halaman utuh (Home, Portfolio, SolutionDetail)
├── index.css      # File CSS global dengan pengaturan variabel tema dasar
└── App.jsx        # Pusat registrasi route halaman
```

## 📝 Lisensi
Hak Cipta © Moduvox Enterprise. Seluruh hak dilindungi undang-undang.
