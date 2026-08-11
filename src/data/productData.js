import {
  Users, FileArchive, CreditCard, Scissors, LayoutDashboard,
  Box, TrendingDown, RefreshCcw, FileText, Database, Shield,
  Activity, ArrowRightLeft, Settings, CheckSquare, Zap, BarChart3,
  Globe, Lock, Layers, DownloadCloud, FileCheck, Search, Users2,
  Clock, Calendar, FileSpreadsheet, Fingerprint, BookOpen,
  Server, MonitorSmartphone
} from 'lucide-react';

export const products = [
  {
    id: 'inventaris',
    name: 'Inventaris Management',
    category: 'Enterprise Asset Management',
    tagline: 'Sistem Informasi Manajemen Inventaris & Penyusutan',
    color: '#10B981',
    icon: LayoutDashboard,
    demoUrl: import.meta.env.VITE_INVENTARIS_URL || 'https://inventaris.moduvox.com',
    longDescription: 'Sistem Inventaris Management adalah aplikasi berbasis web yang dibangun menggunakan Laravel 11 untuk mengelola data inventaris perusahaan secara menyeluruh — mulai dari pencatatan aset, perhitungan penyusutan otomatis, hingga pengiriman jurnal ke Core Banking. Aplikasi ini merupakan versi penyempurnaan (v2) yang terintegrasi langsung dengan API Core General Ledger (GL).',
    highlights: [
      { title: 'Asset Management', icon: Box, desc: 'Pelacakan aset real-time' },
      { title: 'Depreciation', icon: TrendingDown, desc: 'Penyusutan otomatis' },
      { title: 'Core Banking', icon: RefreshCcw, desc: 'Integrasi jurnal API' },
      { title: 'Reporting', icon: BarChart3, desc: 'Analisa & Laporan' }
    ],
    modulesDetail: [
      {
        icon: LayoutDashboard,
        title: 'Dashboard',
        shortDesc: 'Monitor kondisi aset perusahaan secara real-time dalam satu layar.',
        features: [
          'Statistik jumlah aset keseluruhan',
          'Total nilai perolehan & nilai buku',
          'Distribusi aset per cabang/golongan'
        ]
      },
      {
        icon: Box,
        title: 'Manajemen Inventaris',
        shortDesc: 'Pusat kendali seluruh aset dengan pencatatan mutasi dan histori.',
        features: [
          'CRUD aset lengkap',
          'Pencatatan mutasi aset',
          'Histori penyusutan',
          'Import Excel & Barcode printing'
        ]
      },
      {
        icon: TrendingDown,
        title: 'Penyusutan',
        shortDesc: 'Otomatisasi kalkulasi nilai susut aset tanpa proses manual.',
        features: [
          'Batch depreciation bulanan',
          'Draft preview sebelum disetujui',
          'Automatic journal posting',
          'Pengecualian otomatis Tanah & Gedung'
        ]
      },
      {
        icon: RefreshCcw,
        title: 'Integrasi Core Banking',
        shortDesc: 'Komunikasi langsung dengan sistem GL perbankan inti.',
        features: [
          'GL to GL integration',
          'API journal transmission',
          'API response monitoring',
          'Failed journal retry'
        ]
      },
      {
        icon: FileText,
        title: 'Reporting',
        shortDesc: 'Pembuatan laporan aset komprehensif untuk keperluan audit.',
        features: [
          'Laporan inventaris per cabang',
          'Laporan penyusutan per periode',
          'Export ke format Excel',
          'Logo dinamis di header laporan'
        ]
      },
      {
        icon: Database,
        title: 'Data Master',
        shortDesc: 'Konfigurasi dasar sistem untuk klasifikasi aset yang akurat.',
        features: [
          'Kantor Cabang',
          'Golongan & Umur Standar Aset',
          'Lokasi & Ruangan',
          'Mapping COA Akun'
        ]
      }
    ],
    workflow: [
      { step: '01', title: 'Input Aset' },
      { step: '02', title: 'Validasi & Klasifikasi' },
      { step: '03', title: 'Perhitungan Penyusutan' },
      { step: '04', title: 'Approval' },
      { step: '05', title: 'Jurnal API Core Banking' },
      { step: '06', title: 'Reporting & Audit' }
    ],
    reportingStats: {
      totalAsset: '12.450',
      acquisitionValue: 'Rp 45.5 M',
      bookValue: 'Rp 38.2 M',
      depreciationMonth: 'Rp 125 Jt'
    },
    enterpriseCapabilities: [
      { title: 'Web Based', icon: Globe },
      { title: 'Secure Access', icon: Lock },
      { title: 'Centralized Data', icon: Database },
      { title: 'API Integration', icon: Zap },
      { title: 'Excel Import', icon: DownloadCloud },
      { title: 'Audit Trail', icon: Shield },
      { title: 'Role Based Access', icon: Users2 },
      { title: 'Reporting', icon: FileSpreadsheet }
    ],
    techStackList: [
      { category: 'Framework', tech: 'Laravel 11 (PHP 8.2+)' },
      { category: 'Frontend', tech: 'Blade + Tailwind CSS' },
      { category: 'Database', tech: 'MySQL / MariaDB' },
      { category: 'Deployment', tech: 'Web Based (VPS/Cloud)' },
    ],
    systemRequirements: [
      'PHP >= 8.2',
      'Database MySQL / MariaDB',
      'Ekstensi PHP standar Laravel',
      'Koneksi API ke Core Banking'
    ],
    screenshots: [
      'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1200&q=80',
    ]
  },
  {
    id: 'bantuan-potong',
    name: 'Bantuan Potong',
    category: 'Loan & Salary Deduction',
    tagline: 'Sistem Distribusi Bantuan dengan Mekanisme Potong Otomatis',
    color: '#E11D48',
    icon: Scissors,
    demoUrl: import.meta.env.VITE_BANPOT_URL || 'https://bantuan-potong.moduvox.com',
    longDescription: 'Bantuan Potong adalah aplikasi berbasis web yang didesain untuk memfasilitasi proses penyaluran bantuan, pinjaman, dan program kesejahteraan lainnya yang memanfaatkan mekanisme potong gaji otomatis. Sistem ini memastikan distribusi dana transparan, pelacakan angsuran akurat, serta meminimalisir tunggakan melalui integrasi data potong dengan instansi/perusahaan terkait.',
    highlights: [
      { title: 'Deduction', icon: Scissors, desc: 'Potong gaji otomatis' },
      { title: 'Distribution', icon: ArrowRightLeft, desc: 'Penyaluran dana' },
      { title: 'Reconciliation', icon: CheckSquare, desc: 'Rekonsiliasi tagihan' },
      { title: 'Analytics', icon: Activity, desc: 'Monitoring NPL' }
    ],
    modulesDetail: [
      {
        icon: LayoutDashboard,
        title: 'Dashboard Eksekutif',
        shortDesc: 'Pantau pergerakan dana dan tingkat pengembalian.',
        features: [
          'Total dana tersalurkan',
          'Realisasi potong per bulan',
          'Monitoring NPL real-time'
        ]
      },
      {
        icon: ArrowRightLeft,
        title: 'Manajemen Penyaluran',
        shortDesc: 'Atur batch program penyaluran dan verifikasi penerima.',
        features: [
          'Pencatatan batch program',
          'Approval penerima',
          'Jadwal potong otomatis'
        ]
      },
      {
        icon: Scissors,
        title: 'Sistem Potong',
        shortDesc: 'Otomatisasi tagihan ke instansi mitra secara akurat.',
        features: [
          'Generate file billing bulanan',
          'Rekonsiliasi otomatis penerimaan',
          'Penyesuaian pelunasan'
        ]
      },
      {
        icon: Settings,
        title: 'Master & Setup',
        shortDesc: 'Konfigurasi instansi dan parameter skema.',
        features: [
          'Data instansi mitra',
          'Parameter bunga & tenor',
          'Role-based access control'
        ]
      }
    ],
    workflow: [
      { step: '01', title: 'Setup Instansi' },
      { step: '02', title: 'Input Penerima' },
      { step: '03', title: 'Penyaluran Dana' },
      { step: '04', title: 'Generate Tagihan' },
      { step: '05', title: 'Rekonsiliasi Potongan' },
      { step: '06', title: 'Pelaporan' }
    ],
    reportingStats: {
      totalAsset: '4.500 Mitra',
      acquisitionValue: 'Rp 12.5 M',
      bookValue: 'Rp 8.1 M (Realisasi)',
      depreciationMonth: '0.8% NPL'
    },
    enterpriseCapabilities: [
      { title: 'Web Based', icon: Globe },
      { title: 'Secure Access', icon: Lock },
      { title: 'Billing Generate', icon: FileCheck },
      { title: 'Role Based Access', icon: Users2 }
    ],
    techStackList: [
      { category: 'Framework', tech: 'Laravel 11 (PHP 8.2+)' },
      { category: 'Frontend', tech: 'Filament TALL Stack' },
      { category: 'Database', tech: 'MySQL / MariaDB' },
      { category: 'Deployment', tech: 'Web Based (VPS/Cloud)' },
    ],
    systemRequirements: [
      'PHP >= 8.2',
      'Database MySQL / MariaDB'
    ],
    screenshots: [
      'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?auto=format&fit=crop&w=1200&q=80',
    ]
  },
  {
    id: 'core-banking',
    name: 'Core Banking Koperasi',
    category: 'Core Banking System',
    tagline: 'Sistem Inti Operasional Keuangan & Perbankan Koperasi',
    color: '#00A86B',
    icon: CreditCard,
    demoUrl: import.meta.env.VITE_CORE_BANKING_URL || 'https://core-banking.moduvox.com',
    longDescription: 'Core Banking Koperasi merupakan solusi menyeluruh untuk digitalisasi layanan transaksi simpan-pinjam dan operasional perbankan mikro. Aplikasi ini dirancang agar Koperasi atau BPR mampu beroperasi layaknya bank profesional—mulai dari layanan front-office (Teller), pengajuan kredit, akuntansi (General Ledger), hingga integrasi pelaporan sesuai standar Otoritas Jasa Keuangan (OJK).',
    highlights: [
      { title: 'Front Office', icon: Users, desc: 'Layanan Teller & CS' },
      { title: 'Loan System', icon: CreditCard, desc: 'Sistem Perkreditan' },
      { title: 'General Ledger', icon: BookOpen, desc: 'Akuntansi & GL' },
      { title: 'OJK Reporting', icon: FileCheck, desc: 'Laporan Regulasi' }
    ],
    modulesDetail: [
      {
        icon: Users,
        title: 'Teller & Front Office',
        shortDesc: 'Pusat layanan transaksi tunai nasabah harian.',
        features: [
          'Setoran, penarikan, transfer',
          'Cetak validasi & buku tabungan',
          'Manajemen kas harian Teller'
        ]
      },
      {
        icon: Users2,
        title: 'Manajemen CIF',
        shortDesc: 'Penyimpanan data sentral identitas nasabah (KYC).',
        features: [
          'Data nasabah individu & korporasi',
          'Pembukaan rekening',
          'Pemblokiran & penutupan'
        ]
      },
      {
        icon: CreditCard,
        title: 'Sistem Perkreditan',
        shortDesc: 'End-to-end proses pengajuan hingga pelunasan kredit.',
        features: [
          'Analisa & approval',
          'Jadwal angsuran otomatis',
          'Perhitungan denda & kol'
        ]
      },
      {
        icon: FileText,
        title: 'General Ledger',
        shortDesc: 'Jantung akuntansi otomatis dan buku besar.',
        features: [
          'Jurnal otomatis (STP)',
          'Neraca & Laba/Rugi',
          'EOD & EOM Otomatis'
        ]
      }
    ],
    workflow: [
      { step: '01', title: 'Registrasi CIF' },
      { step: '02', title: 'Pembukaan Rekening' },
      { step: '03', title: 'Transaksi Teller' },
      { step: '04', title: 'Proses Kredit' },
      { step: '05', title: 'Jurnal Akuntansi' },
      { step: '06', title: 'EOD / Tutup Buku' }
    ],
    reportingStats: {
      totalAsset: '85.200 Nasabah',
      acquisitionValue: 'Rp 210 M (Asset)',
      bookValue: 'Rp 180 M (DPK)',
      depreciationMonth: 'CAR 24.5%'
    },
    enterpriseCapabilities: [
      { title: 'High Availability', icon: Server },
      { title: 'Secure Access', icon: Lock },
      { title: 'End-of-Day Batch', icon: Zap },
      { title: 'API Ready', icon: Layers }
    ],
    techStackList: [
      { category: 'Framework', tech: 'Laravel 11' },
      { category: 'Frontend', tech: 'Blade + Tailwind' },
      { category: 'Database', tech: 'MySQL Enterprise' },
      { category: 'Architecture', tech: 'Modular' },
    ],
    systemRequirements: [
      'PHP >= 8.2',
      'Database MySQL 8.0+',
      'Redis (Untuk antrian proses)'
    ],
    screenshots: [
      'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=1200&q=80',
    ]
  },
  {
    id: 'siardi',
    name: 'Sistem Arsip Digital',
    category: 'Document Management System',
    tagline: 'Sistem Informasi Arsip Digital Cerdas',
    color: '#7C3AED',
    icon: FileArchive,
    demoUrl: import.meta.env.VITE_SIARDI_URL || 'https://siardi.moduvox.com',
    longDescription: 'Sistem Arsip Digital mentransformasi ruang penyimpanan fisik menjadi repository digital yang cerdas dan terstruktur. Dokumen perusahaan yang dulunya berserakan kini diindeks dengan metadata cerdas, dilindungi oleh hak akses berlapis, dan dilengkapi dengan audit trail komprehensif, sehingga proses pencarian memori instansi hanya membutuhkan hitungan detik.',
    highlights: [
      { title: 'Repository', icon: Database, desc: 'Penyimpanan terpusat' },
      { title: 'Smart Search', icon: Search, desc: 'Pencarian metadata' },
      { title: 'Security', icon: Shield, desc: 'Hak akses dokumen' },
      { title: 'Workflow', icon: CheckSquare, desc: 'Persetujuan dokumen' }
    ],
    modulesDetail: [
      {
        icon: Database,
        title: 'Repository Digital',
        shortDesc: 'Struktur penyimpanan dokumen digital aman.',
        features: [
          'Hierarki folder tak terbatas',
          'Upload batch dokumen',
          'Versioning dokumen'
        ]
      },
      {
        icon: Search,
        title: 'Pencarian Pintar',
        shortDesc: 'Temukan dokumen dalam hitungan detik.',
        features: [
          'Cari via keyword / nomor surat',
          'Filter kategori',
          'Preview tanpa download'
        ]
      },
      {
        icon: Shield,
        title: 'Keamanan & Audit',
        shortDesc: 'Lindungi dokumen rahasia instansi.',
        features: [
          'Hak akses per departemen',
          'Log aktivitas detail',
          'Watermark dinamis'
        ]
      },
      {
        icon: CheckSquare,
        title: 'Workflow',
        shortDesc: 'Alur persetujuan dokumen resmi.',
        features: [
          'Routing persetujuan',
          'Notifikasi email',
          'Retensi dokumen otomatis'
        ]
      }
    ],
    workflow: [
      { step: '01', title: 'Upload Dokumen' },
      { step: '02', title: 'Input Metadata' },
      { step: '03', title: 'Review & Approval' },
      { step: '04', title: 'Indexing' },
      { step: '05', title: 'Pencarian' },
      { step: '06', title: 'Audit Akses' }
    ],
    reportingStats: {
      totalAsset: '1.2M Dokumen',
      acquisitionValue: '450 GB Storage',
      bookValue: '98% Indexed',
      depreciationMonth: '0.2s Search Time'
    },
    enterpriseCapabilities: [
      { title: 'Cloud Storage', icon: DownloadCloud },
      { title: 'Granular ACL', icon: Lock },
      { title: 'OCR Ready', icon: Search },
      { title: 'Audit Log', icon: Shield }
    ],
    techStackList: [
      { category: 'Framework', tech: 'Laravel 11' },
      { category: 'Frontend', tech: 'Vue.js' },
      { category: 'Database', tech: 'MySQL' },
      { category: 'Storage', tech: 'S3 Compatible' },
    ],
    systemRequirements: [
      'PHP >= 8.2',
      'Kapasitas Storage memadai'
    ],
    screenshots: [
      'https://images.unsplash.com/photo-1618044733300-9472054094ee?auto=format&fit=crop&w=1200&q=80',
    ]
  },
  {
    id: 'hris',
    name: 'HRIS & Absensi',
    category: 'Human Resource Information System',
    tagline: 'Manajemen Sumber Daya Manusia dan Payroll Terpadu',
    color: '#005BAC',
    icon: Users,
    demoUrl: import.meta.env.VITE_HRIS_URL || 'https://hris.moduvox.com',
    longDescription: 'HRIS Enterprise membebaskan divisi HR dari tugas administratif repetitif. Platform ini menyatukan proses rekrutmen, absensi real-time, pengajuan cuti, hingga kalkulasi payroll dan pajak (PPh 21) dalam satu sistem. Memberikan pengalaman Employee Self-Service yang meningkatkan transparansi dan kepuasan karyawan.',
    highlights: [
      { title: 'Database', icon: Users, desc: 'Data Karyawan (ESS)' },
      { title: 'Attendance', icon: Clock, desc: 'Absensi GPS & Selfie' },
      { title: 'Time Off', icon: Calendar, desc: 'Cuti & Perizinan' },
      { title: 'Payroll', icon: CreditCard, desc: 'Gaji, Pajak & BPJS' }
    ],
    modulesDetail: [
      {
        icon: Users2,
        title: 'Employee Center',
        shortDesc: 'Pangkalan data kepegawaian sentral.',
        features: [
          'Database karyawan lengkap',
          'Struktur organisasi',
          'Portal ESS (Self Service)'
        ]
      },
      {
        icon: Clock,
        title: 'Manajemen Absensi',
        shortDesc: 'Pelacakan kehadiran akurat dan terintegrasi.',
        features: [
          'Integrasi mesin sidik jari',
          'Clock in/out mobile (GPS)',
          'Kalkulasi lembur & shift'
        ]
      },
      {
        icon: Calendar,
        title: 'Cuti & Perizinan',
        shortDesc: 'Proses pengajuan hari libur yang efisien.',
        features: [
          'Approval atasan berlapis',
          'Perhitungan sisa kuota',
          'Sistem perizinan dinas'
        ]
      },
      {
        icon: FileSpreadsheet,
        title: 'Payroll & Benefit',
        shortDesc: 'Kalkulasi finansial presisi sesuai regulasi.',
        features: [
          'Generate slip gaji otomatis',
          'Kalkulasi BPJS',
          'PPh 21 otomatis (TER)'
        ]
      }
    ],
    workflow: [
      { step: '01', title: 'Onboarding' },
      { step: '02', title: 'Absensi Harian' },
      { step: '03', title: 'Request Cuti/Lembur' },
      { step: '04', title: 'Approval Manager' },
      { step: '05', title: 'Kalkulasi Gaji' },
      { step: '06', title: 'Distribusi Slip Gaji' }
    ],
    reportingStats: {
      totalAsset: '1.250 Karyawan',
      acquisitionValue: '45 Divisi',
      bookValue: '99% Attendance Rate',
      depreciationMonth: 'Payroll 1 Hari'
    },
    enterpriseCapabilities: [
      { title: 'Mobile App', icon: MonitorSmartphone },
      { title: 'Biometric Auth', icon: Fingerprint },
      { title: 'Tax Compliant', icon: CheckSquare },
      { title: 'Bank API', icon: ArrowRightLeft }
    ],
    techStackList: [
      { category: 'Framework', tech: 'Laravel 11' },
      { category: 'Frontend', tech: 'Vue.js + Tailwind' },
      { category: 'Database', tech: 'MySQL' },
      { category: 'Mobile', tech: 'Flutter App' },
    ],
    systemRequirements: [
      'PHP >= 8.2',
      'Database MySQL',
      'Cron Jobs Setup'
    ],
    screenshots: [
      'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&w=1200&q=80',
    ]
  },
  {
    id: 'company-profile',
    name: 'Corporate Website',
    category: 'Company Profile & Showcase',
    tagline: 'Platform Representasi Digital Perusahaan Modern',
    color: '#F59E0B',
    icon: Globe,
    demoUrl: import.meta.env.VITE_COMPRO_URL || 'http://company-profile.moduvox.local',
    longDescription: 'Corporate Website adalah solusi platform profil perusahaan modern yang dirancang khusus untuk mempresentasikan portofolio, layanan unggulan, karir, dan informasi kontak secara profesional. Dilengkapi dengan CMS (Content Management System) di panel admin untuk mempermudah pembaruan konten tanpa perlu kemampuan coding.',
    highlights: [
      { title: 'Portfolio', icon: Box, desc: 'Showcase proyek' },
      { title: 'Services', icon: Layers, desc: 'Layanan unggulan' },
      { title: 'CMS Admin', icon: LayoutDashboard, desc: 'Panel manajemen konten' },
      { title: 'Contact', icon: Users, desc: 'Formulir konsultasi' }
    ],
    modulesDetail: [
      {
        icon: LayoutDashboard,
        title: 'CMS Dashboard',
        shortDesc: 'Pusat manajemen konten website perusahaan.',
        features: [
          'Kelola teks halaman utama',
          'Statistik kunjungan sederhana',
          'Manajemen inbox pesan'
        ]
      },
      {
        icon: Box,
        title: 'Manajemen Portofolio',
        shortDesc: 'Katalog proyek dan pencapaian perusahaan.',
        features: [
          'Galeri proyek dengan gambar',
          'Kategorisasi proyek',
          'Detail spesifikasi teknis'
        ]
      },
      {
        icon: Users,
        title: 'Karir & Publikasi',
        shortDesc: 'Publikasi informasi lowongan pekerjaan dan layanan.',
        features: [
          'Manajemen lowongan kerja',
          'Status aktif/non-aktif karir',
          'Manajemen klien/mitra'
        ]
      },
      {
        icon: Settings,
        title: 'Inquiry & Kontak',
        shortDesc: 'Pengelolaan pesan masuk dari prospek.',
        features: [
          'Form jadwal konsultasi terintegrasi',
          'Notifikasi pesan baru',
          'Manajemen status pesan (read/unread)'
        ]
      }
    ],
    workflow: [
      { step: '01', title: 'Setup Identitas' },
      { step: '02', title: 'Input Layanan' },
      { step: '03', title: 'Upload Portofolio' },
      { step: '04', title: 'Publikasi Web' },
      { step: '05', title: 'Terima Inquiry' },
      { step: '06', title: 'Follow Up Klien' }
    ],
    reportingStats: {
      totalAsset: 'Unlimited Pages',
      acquisitionValue: 'SEO Optimized',
      bookValue: 'Responsive Design',
      depreciationMonth: 'Fast Loading'
    },
    enterpriseCapabilities: [
      { title: 'Web Based', icon: Globe },
      { title: 'CMS Ready', icon: FileText },
      { title: 'Fast Loading', icon: Zap },
      { title: 'Secure Admin', icon: Lock }
    ],
    techStackList: [
      { category: 'Framework', tech: 'Laravel 11' },
      { category: 'Frontend', tech: 'Blade + Tailwind CSS' },
      { category: 'Database', tech: 'MySQL' },
      { category: 'Deployment', tech: 'VPS/Cloud' },
    ],
    systemRequirements: [
      'PHP >= 8.2',
      'Database MySQL',
      'Ekstensi PHP standar Laravel'
    ],
    screenshots: [
      'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1200&q=80',
    ]
  }
];
