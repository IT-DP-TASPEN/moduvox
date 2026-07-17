import {
  Users, FileArchive, BarChart3, CreditCard, Scissors, Network, Building,
  LayoutDashboard, UserCheck, Clock, CalendarOff, CheckSquare, FileText,
  FolderOpen, Upload, Eye, Search, UserCircle, Activity, GitBranch, PieChart,
  Wallet, PiggyBank, HandCoins, Receipt, TrendingUp, Landmark, ArrowLeftRight
} from 'lucide-react';

export const products = [
  {
    id: 'hris',
    name: 'HRIS Enterprise',
    tagline: 'Manajemen SDM Terpadu untuk Organisasi Modern',
    problem: 'Pengelolaan data karyawan, absensi, cuti, dan penggajian masih dilakukan secara manual dengan spreadsheet terpisah, menyebabkan ketidakakuratan data dan inefisiensi operasional HR.',
    benefits: [
      'Otomasi penggajian dan perhitungan pajak',
      'Real-time attendance tracking',
      'Self-service portal untuk karyawan',
      'Approval workflow multi-level',
    ],
    icon: Users,
    color: '#005BAC',
    roles: [
      { id: 'hr', label: 'HR Admin', icon: UserCheck },
      { id: 'manager', label: 'Atasan', icon: Users },
      { id: 'employee', label: 'Pegawai', icon: UserCircle },
    ],
    modules: {
      hr: [
        {
          id: 'dashboard', label: 'Dashboard', icon: LayoutDashboard,
          widgets: [
            { label: 'Total Pegawai', value: 1248, suffix: '', trend: '+12' },
            { label: 'Hadir Hari Ini', value: 1180, suffix: '', trend: '+5' },
            { label: 'Pengajuan Cuti', value: 24, suffix: '', trend: '-3' },
            { label: 'Payroll Bulan Ini', value: 8.4, suffix: 'M', prefix: 'Rp ', trend: '+2.1' },
          ],
          table: {
            title: 'Aktivitas Terbaru',
            headers: ['Pegawai', 'Aktivitas', 'Status', 'Waktu'],
            rows: [
              ['Budi Santoso', 'Clock In', 'Tepat Waktu', '08:02'],
              ['Siti Aminah', 'Pengajuan Cuti', 'Menunggu', '08:15'],
              ['Ahmad Fauzi', 'Lembur Approved', 'Disetujui', '07:45'],
              ['Dewi Lestari', 'Clock In', 'Terlambat', '08:35'],
            ]
          }
        },
        { id: 'pegawai', label: 'Data Pegawai', icon: Users,
          widgets: [
            { label: 'Total Aktif', value: 1180, suffix: '' },
            { label: 'Kontrak', value: 68, suffix: '' },
            { label: 'Baru Bulan Ini', value: 15, suffix: '' },
            { label: 'Resign', value: 3, suffix: '' },
          ],
          table: {
            title: 'Daftar Pegawai',
            headers: ['NIP', 'Nama', 'Departemen', 'Jabatan', 'Status'],
            rows: [
              ['EMP-001', 'Budi Santoso', 'Engineering', 'Senior Dev', 'Aktif'],
              ['EMP-002', 'Siti Aminah', 'Marketing', 'Manager', 'Aktif'],
              ['EMP-003', 'Ahmad Fauzi', 'Finance', 'Analyst', 'Cuti'],
              ['EMP-004', 'Dewi Lestari', 'HR', 'Specialist', 'Aktif'],
              ['EMP-005', 'Riko Pratama', 'Engineering', 'Backend Dev', 'Aktif'],
            ]
          }
        },
        { id: 'absensi', label: 'Absensi', icon: Clock,
          widgets: [
            { label: 'Hadir', value: 94.5, suffix: '%' },
            { label: 'Terlambat', value: 12, suffix: '' },
            { label: 'Izin', value: 8, suffix: '' },
            { label: 'Alpha', value: 2, suffix: '' },
          ],
          table: {
            title: 'Log Absensi Hari Ini',
            headers: ['Pegawai', 'Clock In', 'Clock Out', 'Status'],
            rows: [
              ['Budi Santoso', '08:02', '-', 'Hadir'],
              ['Siti Aminah', '07:55', '-', 'Hadir'],
              ['Dewi Lestari', '08:35', '-', 'Terlambat'],
              ['Riko Pratama', '07:58', '-', 'Hadir'],
            ]
          }
        },
        { id: 'cuti', label: 'Cuti', icon: CalendarOff,
          widgets: [
            { label: 'Pengajuan Baru', value: 8, suffix: '' },
            { label: 'Disetujui', value: 142, suffix: '' },
            { label: 'Ditolak', value: 5, suffix: '' },
            { label: 'Sisa Rata-rata', value: 7, suffix: ' hari' },
          ],
          table: {
            title: 'Pengajuan Cuti Terbaru',
            headers: ['Pegawai', 'Jenis', 'Tanggal', 'Status'],
            rows: [
              ['Siti Aminah', 'Cuti Tahunan', '25-28 Jun', 'Menunggu'],
              ['Ahmad Fauzi', 'Sakit', '20 Jun', 'Disetujui'],
              ['Riko Pratama', 'Cuti Tahunan', '1-3 Jul', 'Menunggu'],
            ]
          }
        },
        { id: 'approval', label: 'Approval', icon: CheckSquare,
          widgets: [
            { label: 'Menunggu', value: 12, suffix: '' },
            { label: 'Disetujui Hari Ini', value: 5, suffix: '' },
            { label: 'Ditolak', value: 1, suffix: '' },
            { label: 'Total Bulan Ini', value: 89, suffix: '' },
          ],
          table: {
            title: 'Antrian Approval',
            headers: ['Pemohon', 'Jenis', 'Tanggal', 'Prioritas'],
            rows: [
              ['Siti Aminah', 'Cuti Tahunan', '25 Jun', 'Normal'],
              ['Budi Santoso', 'Lembur', '24 Jun', 'Tinggi'],
              ['Dewi Lestari', 'Reimburse', '23 Jun', 'Normal'],
            ]
          }
        },
        { id: 'laporan', label: 'Laporan', icon: FileText,
          widgets: [
            { label: 'Laporan Tersedia', value: 24, suffix: '' },
            { label: 'Di-generate Bulan Ini', value: 8, suffix: '' },
            { label: 'Terjadwal', value: 5, suffix: '' },
            { label: 'Export Bulan Ini', value: 15, suffix: '' },
          ],
          table: {
            title: 'Laporan Terbaru',
            headers: ['Nama Laporan', 'Periode', 'Status', 'Terakhir'],
            rows: [
              ['Rekap Absensi', 'Juni 2026', 'Selesai', '24 Jun'],
              ['Slip Gaji', 'Juni 2026', 'Proses', '-'],
              ['Turnover Report', 'Q2 2026', 'Selesai', '20 Jun'],
            ]
          }
        },
      ],
      manager: [
        {
          id: 'dashboard', label: 'Dashboard', icon: LayoutDashboard,
          widgets: [
            { label: 'Tim Saya', value: 12, suffix: ' orang' },
            { label: 'Hadir Hari Ini', value: 11, suffix: '' },
            { label: 'Approval Pending', value: 3, suffix: '' },
            { label: 'Lembur Bulan Ini', value: 45, suffix: ' jam' },
          ],
          table: {
            title: 'Tim Saya',
            headers: ['Nama', 'Jabatan', 'Status', 'Kehadiran'],
            rows: [
              ['Budi Santoso', 'Senior Dev', 'Hadir', '98%'],
              ['Riko Pratama', 'Backend Dev', 'Hadir', '95%'],
              ['Dewi Lestari', 'QA Engineer', 'Terlambat', '92%'],
            ]
          }
        },
        { id: 'approval', label: 'Approval', icon: CheckSquare,
          widgets: [
            { label: 'Menunggu', value: 3, suffix: '' },
            { label: 'Disetujui Minggu Ini', value: 4, suffix: '' },
          ],
          table: {
            title: 'Perlu Persetujuan Anda',
            headers: ['Pemohon', 'Jenis', 'Tanggal'],
            rows: [
              ['Budi Santoso', 'Lembur', '24 Jun'],
              ['Riko Pratama', 'Cuti', '1-3 Jul'],
              ['Dewi Lestari', 'Reimburse', '23 Jun'],
            ]
          }
        },
      ],
      employee: [
        {
          id: 'dashboard', label: 'Dashboard', icon: LayoutDashboard,
          widgets: [
            { label: 'Sisa Cuti', value: 8, suffix: ' hari' },
            { label: 'Kehadiran', value: 96, suffix: '%' },
            { label: 'Lembur Bulan Ini', value: 12, suffix: ' jam' },
            { label: 'Status Payroll', value: 0, suffix: '', displayValue: 'Selesai' },
          ],
          table: {
            title: 'Riwayat Absensi',
            headers: ['Tanggal', 'Clock In', 'Clock Out', 'Status'],
            rows: [
              ['24 Jun', '08:02', '17:05', 'Hadir'],
              ['23 Jun', '07:55', '17:10', 'Hadir'],
              ['22 Jun', '08:30', '17:00', 'Terlambat'],
            ]
          }
        },
        { id: 'cuti', label: 'Pengajuan Cuti', icon: CalendarOff,
          widgets: [
            { label: 'Sisa Cuti', value: 8, suffix: ' hari' },
            { label: 'Diajukan', value: 4, suffix: '' },
            { label: 'Disetujui', value: 3, suffix: '' },
          ],
          table: {
            title: 'Riwayat Cuti Saya',
            headers: ['Tanggal', 'Jenis', 'Durasi', 'Status'],
            rows: [
              ['1-3 Mar', 'Tahunan', '3 hari', 'Disetujui'],
              ['15 Apr', 'Sakit', '1 hari', 'Disetujui'],
            ]
          }
        },
      ],
    }
  },
  {
    id: 'siardi',
    name: 'SIARDI',
    tagline: 'Sistem Arsip Digital Terintegrasi',
    problem: 'Dokumen penting perusahaan tersebar di berbagai lokasi fisik dan digital, sulit ditemukan, rentan hilang, dan tidak memiliki audit trail yang jelas.',
    benefits: [
      'Digitalisasi dan indexing dokumen otomatis',
      'Pencarian cepat dengan metadata',
      'Approval workflow untuk dokumen sensitif',
      'Audit trail lengkap setiap akses dokumen',
    ],
    icon: FileArchive,
    color: '#7C3AED',
    roles: [
      { id: 'admin', label: 'Admin Arsip', icon: UserCheck },
      { id: 'user', label: 'Pengguna', icon: UserCircle },
    ],
    modules: {
      admin: [
        {
          id: 'dashboard', label: 'Dashboard', icon: LayoutDashboard,
          widgets: [
            { label: 'Total Dokumen', value: 24580, suffix: '' },
            { label: 'Upload Hari Ini', value: 45, suffix: '' },
            { label: 'Pending Review', value: 12, suffix: '' },
            { label: 'Storage Used', value: 82, suffix: '%' },
          ],
          table: {
            title: 'Dokumen Terbaru',
            headers: ['Nama File', 'Kategori', 'Uploader', 'Status'],
            rows: [
              ['SK_Pengangkatan_2026.pdf', 'SK', 'Admin HR', 'Approved'],
              ['Laporan_Keuangan_Q2.xlsx', 'Keuangan', 'Finance', 'Review'],
              ['MOU_Vendor_ABC.pdf', 'Legal', 'Legal Team', 'Approved'],
              ['Notulen_Rapat_240626.docx', 'Internal', 'Sekretariat', 'Draft'],
            ]
          }
        },
        { id: 'repository', label: 'Repository', icon: FolderOpen,
          widgets: [
            { label: 'Folder Aktif', value: 156, suffix: '' },
            { label: 'File Terindeks', value: 24580, suffix: '' },
          ],
          table: {
            title: 'Struktur Folder',
            headers: ['Folder', 'Jumlah File', 'Terakhir Diupdate', 'Akses'],
            rows: [
              ['📁 Surat Keputusan', '1,245', '24 Jun 2026', 'Restricted'],
              ['📁 Laporan Keuangan', '892', '23 Jun 2026', 'Finance Only'],
              ['📁 Legal & MOU', '456', '22 Jun 2026', 'Legal Only'],
              ['📁 Notulen Rapat', '2,100', '24 Jun 2026', 'All Staff'],
            ]
          }
        },
        { id: 'upload', label: 'Upload', icon: Upload,
          widgets: [
            { label: 'Antrian Upload', value: 3, suffix: '' },
            { label: 'Selesai Hari Ini', value: 42, suffix: '' },
          ],
          table: {
            title: 'Antrian Upload',
            headers: ['File', 'Ukuran', 'Progress', 'Status'],
            rows: [
              ['Kontrak_Baru.pdf', '2.4 MB', '100%', 'Selesai'],
              ['Foto_Dokumentasi.zip', '45 MB', '67%', 'Uploading'],
              ['Surat_Edaran.pdf', '1.1 MB', 'Pending', 'Antrian'],
            ]
          }
        },
        { id: 'approval', label: 'Approval', icon: CheckSquare,
          widgets: [
            { label: 'Menunggu', value: 12, suffix: '' },
            { label: 'Disetujui', value: 340, suffix: '' },
          ],
          table: {
            title: 'Dokumen Perlu Persetujuan',
            headers: ['Dokumen', 'Pemohon', 'Kategori', 'Tanggal'],
            rows: [
              ['SK_Mutasi_2026.pdf', 'HR Admin', 'SK', '24 Jun'],
              ['Budget_Q3.xlsx', 'Finance', 'Keuangan', '23 Jun'],
            ]
          }
        },
        { id: 'viewer', label: 'Viewer', icon: Eye,
          widgets: [
            { label: 'Dilihat Hari Ini', value: 89, suffix: '' },
            { label: 'Download', value: 23, suffix: '' },
          ],
          table: {
            title: 'Log Akses Dokumen',
            headers: ['Dokumen', 'Pengakses', 'Aksi', 'Waktu'],
            rows: [
              ['SK_Pengangkatan.pdf', 'Budi S.', 'View', '10:02'],
              ['Laporan_Q1.xlsx', 'Siti A.', 'Download', '09:45'],
              ['MOU_Vendor.pdf', 'Legal', 'Print', '09:30'],
            ]
          }
        },
      ],
      user: [
        {
          id: 'dashboard', label: 'Dashboard', icon: LayoutDashboard,
          widgets: [
            { label: 'Dokumen Saya', value: 45, suffix: '' },
            { label: 'Baru Minggu Ini', value: 3, suffix: '' },
          ],
          table: {
            title: 'Dokumen Terbaru untuk Anda',
            headers: ['Nama File', 'Kategori', 'Tanggal'],
            rows: [
              ['Surat_Edaran_012.pdf', 'Internal', '24 Jun'],
              ['SK_Kenaikan_Gaji.pdf', 'SK', '20 Jun'],
            ]
          }
        },
        { id: 'search', label: 'Cari Dokumen', icon: Search,
          widgets: [
            { label: 'Hasil Pencarian', value: 0, suffix: '', displayValue: '-' },
          ],
          table: {
            title: 'Pencarian Terakhir',
            headers: ['Keyword', 'Hasil', 'Tanggal'],
            rows: [
              ['SK Pengangkatan', '12 file', '24 Jun'],
              ['Laporan Keuangan 2025', '8 file', '22 Jun'],
            ]
          }
        },
      ],
    }
  },
  {
    id: 'crm',
    name: 'CRM Solutions',
    tagline: 'Customer Relationship Management untuk Pertumbuhan Bisnis',
    problem: 'Data pelanggan tersebar di berbagai channel, tim sales tidak memiliki visibility terhadap pipeline, dan follow-up sering terlewat sehingga banyak peluang bisnis yang hilang.',
    benefits: [
      'Unified customer database',
      'Visual sales pipeline management',
      'Automated follow-up reminders',
      'Real-time sales analytics dan forecasting',
    ],
    icon: BarChart3,
    color: '#0EA5E9',
    roles: [
      { id: 'manager', label: 'Sales Manager', icon: UserCheck },
      { id: 'sales', label: 'Sales Rep', icon: UserCircle },
    ],
    modules: {
      manager: [
        {
          id: 'dashboard', label: 'Dashboard', icon: LayoutDashboard,
          widgets: [
            { label: 'Revenue YTD', value: 12.5, suffix: 'B', prefix: 'Rp ' },
            { label: 'Active Leads', value: 142, suffix: '' },
            { label: 'Win Rate', value: 68, suffix: '%' },
            { label: 'New Customers', value: 28, suffix: '' },
          ],
          table: {
            title: 'Top Deals Bulan Ini',
            headers: ['Company', 'Contact', 'Value', 'Stage'],
            rows: [
              ['Bank Maju Jaya', 'Sinta (VP IT)', 'Rp 1.2B', 'Negotiation'],
              ['TechCorp', 'Budi (CEO)', 'Rp 800M', 'Proposal'],
              ['Koperasi Sejahtera', 'Hendra', 'Rp 400M', 'Qualified'],
            ]
          }
        },
        { id: 'customers', label: 'Customer', icon: Users,
          widgets: [
            { label: 'Total Customer', value: 892, suffix: '' },
            { label: 'Active', value: 745, suffix: '' },
            { label: 'Churn Rate', value: 2.1, suffix: '%' },
          ],
          table: {
            title: 'Database Pelanggan',
            headers: ['Company', 'Contact', 'Segment', 'Value'],
            rows: [
              ['Bank Maju Jaya', 'Sinta', 'Enterprise', 'Rp 2.4B'],
              ['RetailX', 'Agus', 'Mid-Market', 'Rp 350M'],
              ['Global Logistics', 'Maya', 'Enterprise', 'Rp 1.8B'],
            ]
          }
        },
        { id: 'activity', label: 'Aktivitas', icon: Activity,
          widgets: [
            { label: 'Meeting Minggu Ini', value: 14, suffix: '' },
            { label: 'Follow-up Pending', value: 8, suffix: '' },
          ],
          table: {
            title: 'Activity Log',
            headers: ['Sales Rep', 'Aktivitas', 'Client', 'Waktu'],
            rows: [
              ['Andi', 'Meeting', 'Bank Maju', '10:00'],
              ['Rina', 'Call', 'RetailX', '11:30'],
              ['Dedi', 'Email Follow-up', 'TechCorp', '14:00'],
            ]
          }
        },
        { id: 'pipeline', label: 'Pipeline', icon: GitBranch,
          widgets: [
            { label: 'Total Pipeline', value: 4.2, suffix: 'B', prefix: 'Rp ' },
            { label: 'Deals Active', value: 45, suffix: '' },
          ],
          table: {
            title: 'Pipeline Overview',
            headers: ['Stage', 'Deals', 'Value', 'Avg Days'],
            rows: [
              ['New Lead', '15', 'Rp 800M', '5'],
              ['Qualified', '12', 'Rp 1.2B', '12'],
              ['Proposal', '10', 'Rp 900M', '18'],
              ['Negotiation', '5', 'Rp 800M', '25'],
              ['Closed Won', '3', 'Rp 500M', '-'],
            ]
          }
        },
        { id: 'reporting', label: 'Reporting', icon: PieChart,
          widgets: [
            { label: 'Reports Available', value: 12, suffix: '' },
          ],
          table: {
            title: 'Laporan Tersedia',
            headers: ['Nama', 'Jenis', 'Terakhir Update'],
            rows: [
              ['Sales Performance Q2', 'Quarterly', '24 Jun'],
              ['Pipeline Forecast', 'Monthly', '20 Jun'],
              ['Win/Loss Analysis', 'Monthly', '18 Jun'],
            ]
          }
        },
      ],
      sales: [
        {
          id: 'dashboard', label: 'Dashboard', icon: LayoutDashboard,
          widgets: [
            { label: 'My Deals', value: 8, suffix: '' },
            { label: 'Target Tercapai', value: 72, suffix: '%' },
            { label: 'Follow-up Hari Ini', value: 3, suffix: '' },
          ],
          table: {
            title: 'Deals Saya',
            headers: ['Company', 'Value', 'Stage', 'Next Action'],
            rows: [
              ['RetailX', 'Rp 350M', 'Proposal', 'Send Proposal'],
              ['KSP Mandiri', 'Rp 200M', 'Qualified', 'Schedule Demo'],
            ]
          }
        },
        { id: 'activity', label: 'Aktivitas', icon: Activity,
          widgets: [
            { label: 'Task Hari Ini', value: 5, suffix: '' },
            { label: 'Overdue', value: 1, suffix: '' },
          ],
          table: {
            title: 'To-Do List',
            headers: ['Task', 'Client', 'Deadline'],
            rows: [
              ['Follow-up call', 'RetailX', '24 Jun'],
              ['Send Proposal', 'KSP Mandiri', '25 Jun'],
              ['Update CRM Notes', 'TechCorp', '24 Jun'],
            ]
          }
        },
      ]
    }
  },
  {
    id: 'core-banking',
    name: 'Core Banking',
    tagline: 'Sistem Inti Perbankan untuk BPR & Koperasi',
    problem: 'Lembaga keuangan mikro masih menggunakan sistem legacy yang lambat, tidak terintegrasi, dan tidak memenuhi standar regulasi OJK terbaru.',
    benefits: [
      'Pemrosesan transaksi real-time',
      'Perhitungan bunga dan angsuran otomatis',
      'Laporan regulasi OJK built-in',
      'Multi-cabang dengan sentralisasi data',
    ],
    icon: CreditCard,
    color: '#00A86B',
    roles: [
      { id: 'teller', label: 'Teller', icon: UserCircle },
      { id: 'supervisor', label: 'Supervisor', icon: UserCheck },
      { id: 'manager', label: 'Manager', icon: Users },
    ],
    modules: {
      teller: [
        {
          id: 'dashboard', label: 'Dashboard', icon: LayoutDashboard,
          widgets: [
            { label: 'Transaksi Hari Ini', value: 48, suffix: '' },
            { label: 'Total Setoran', value: 125, suffix: 'jt', prefix: 'Rp ' },
            { label: 'Total Penarikan', value: 87, suffix: 'jt', prefix: 'Rp ' },
            { label: 'Saldo Kas', value: 450, suffix: 'jt', prefix: 'Rp ' },
          ],
          table: {
            title: 'Transaksi Terakhir',
            headers: ['No Rek', 'Nama', 'Jenis', 'Nominal'],
            rows: [
              ['001-234-567', 'Ibu Sari', 'Setoran', 'Rp 5.000.000'],
              ['001-345-678', 'Pak Budi', 'Penarikan', 'Rp 2.000.000'],
              ['001-456-789', 'Bu Ani', 'Transfer', 'Rp 1.500.000'],
            ]
          }
        },
        { id: 'anggota', label: 'Anggota', icon: Users,
          widgets: [
            { label: 'Total Anggota', value: 8420, suffix: '' },
          ],
          table: {
            title: 'Cari Anggota',
            headers: ['No Anggota', 'Nama', 'Alamat', 'Status'],
            rows: [
              ['A-001', 'Sari Dewi', 'Jl. Sudirman 12', 'Aktif'],
              ['A-002', 'Budi Hartono', 'Jl. Gatot Subroto 5', 'Aktif'],
            ]
          }
        },
        { id: 'simpanan', label: 'Simpanan', icon: PiggyBank,
          widgets: [
            { label: 'Total Simpanan', value: 42, suffix: 'M', prefix: 'Rp ' },
            { label: 'Rekening Aktif', value: 7850, suffix: '' },
          ],
          table: {
            title: 'Transaksi Simpanan',
            headers: ['No Rek', 'Nama', 'Jenis', 'Saldo'],
            rows: [
              ['S-001-234', 'Ibu Sari', 'Tabungan', 'Rp 15.200.000'],
              ['S-001-345', 'Pak Budi', 'Deposito', 'Rp 50.000.000'],
            ]
          }
        },
      ],
      supervisor: [
        {
          id: 'dashboard', label: 'Dashboard', icon: LayoutDashboard,
          widgets: [
            { label: 'Transaksi Perlu Otorisasi', value: 5, suffix: '' },
            { label: 'Total Transaksi', value: 248, suffix: '' },
            { label: 'Volume Harian', value: 1.2, suffix: 'M', prefix: 'Rp ' },
          ],
          table: {
            title: 'Otorisasi Pending',
            headers: ['Teller', 'Jenis', 'Nominal', 'Alasan'],
            rows: [
              ['Rina', 'Penarikan', 'Rp 50.000.000', 'Over limit'],
              ['Dedi', 'Transfer', 'Rp 100.000.000', 'Over limit'],
            ]
          }
        },
        { id: 'pinjaman', label: 'Pinjaman', icon: HandCoins,
          widgets: [
            { label: 'Total Outstanding', value: 28, suffix: 'M', prefix: 'Rp ' },
            { label: 'NPL Ratio', value: 2.8, suffix: '%' },
          ],
          table: {
            title: 'Pinjaman Aktif',
            headers: ['Peminjam', 'Plafon', 'Outstanding', 'Kolektibilitas'],
            rows: [
              ['Pak Budi', 'Rp 50jt', 'Rp 35jt', 'Lancar'],
              ['Ibu Sari', 'Rp 25jt', 'Rp 18jt', 'Lancar'],
              ['Pak Agus', 'Rp 100jt', 'Rp 92jt', 'Kurang Lancar'],
            ]
          }
        },
      ],
      manager: [
        {
          id: 'dashboard', label: 'Dashboard', icon: LayoutDashboard,
          widgets: [
            { label: 'Total Aset', value: 85, suffix: 'M', prefix: 'Rp ' },
            { label: 'NPL Ratio', value: 2.8, suffix: '%' },
            { label: 'ROA', value: 3.2, suffix: '%' },
            { label: 'CAR', value: 22, suffix: '%' },
          ],
          table: {
            title: 'Ringkasan Cabang',
            headers: ['Cabang', 'Aset', 'DPK', 'Kredit'],
            rows: [
              ['Pusat', 'Rp 45M', 'Rp 28M', 'Rp 20M'],
              ['Cabang Utara', 'Rp 22M', 'Rp 14M', 'Rp 8M'],
              ['Cabang Selatan', 'Rp 18M', 'Rp 10M', 'Rp 6M'],
            ]
          }
        },
        { id: 'laporan', label: 'Laporan OJK', icon: FileText,
          widgets: [
            { label: 'Laporan Siap', value: 8, suffix: '' },
            { label: 'Deadline Terdekat', value: 0, suffix: '', displayValue: '30 Jun' },
          ],
          table: {
            title: 'Laporan Regulasi',
            headers: ['Laporan', 'Periode', 'Deadline', 'Status'],
            rows: [
              ['LBBU', 'Juni 2026', '10 Jul', 'Draft'],
              ['LBU', 'Q2 2026', '15 Jul', 'Belum'],
              ['Publikasi', 'Semester I', '31 Jul', 'Belum'],
            ]
          }
        },
      ],
    }
  },
  {
    id: 'bantuan-potong',
    name: 'Bantuan Potong',
    tagline: 'Sistem Distribusi Bantuan dengan Mekanisme Potong Otomatis',
    problem: 'Distribusi bantuan sosial atau pinjaman lunak kepada anggota/masyarakat sulit dipantau, pencairan tidak transparan, dan mekanisme angsuran potong gaji sering bermasalah.',
    benefits: [
      'Distribusi bantuan yang transparan dan teraudit',
      'Mekanisme potong otomatis dari gaji/pendapatan',
      'Tracking status penyaluran real-time',
      'Laporan pertanggungjawaban otomatis',
    ],
    icon: Scissors,
    color: '#E11D48',
    roles: [
      { id: 'admin', label: 'Admin', icon: UserCheck },
      { id: 'penerima', label: 'Penerima', icon: UserCircle },
    ],
    modules: {
      admin: [
        {
          id: 'dashboard', label: 'Dashboard', icon: LayoutDashboard,
          widgets: [
            { label: 'Total Penerima', value: 2450, suffix: '' },
            { label: 'Dana Tersalurkan', value: 4.8, suffix: 'M', prefix: 'Rp ' },
            { label: 'Angsuran Terkumpul', value: 3.2, suffix: 'M', prefix: 'Rp ' },
            { label: 'Tunggakan', value: 125, suffix: 'jt', prefix: 'Rp ' },
          ],
          table: {
            title: 'Penyaluran Terbaru',
            headers: ['Penerima', 'Program', 'Nominal', 'Status'],
            rows: [
              ['Ahmad F.', 'BLT', 'Rp 600.000', 'Tersalurkan'],
              ['Siti R.', 'Pinjaman Lunak', 'Rp 5.000.000', 'Proses'],
              ['Budi W.', 'BLT', 'Rp 600.000', 'Tersalurkan'],
            ]
          }
        },
        { id: 'penyaluran', label: 'Penyaluran', icon: HandCoins,
          widgets: [
            { label: 'Batch Aktif', value: 3, suffix: '' },
            { label: 'Menunggu Verifikasi', value: 45, suffix: '' },
          ],
          table: {
            title: 'Batch Penyaluran',
            headers: ['Batch', 'Program', 'Penerima', 'Total Dana'],
            rows: [
              ['B-2026-06', 'BLT Juni', '1,200', 'Rp 720jt'],
              ['B-2026-06-P', 'Pinjaman Lunak', '85', 'Rp 425jt'],
            ]
          }
        },
        { id: 'angsuran', label: 'Angsuran', icon: Receipt,
          widgets: [
            { label: 'Terkumpul Bulan Ini', value: 280, suffix: 'jt', prefix: 'Rp ' },
            { label: 'Target', value: 320, suffix: 'jt', prefix: 'Rp ' },
          ],
          table: {
            title: 'Jadwal Potong',
            headers: ['Penerima', 'Instansi', 'Potong/Bulan', 'Sisa'],
            rows: [
              ['Ahmad F.', 'Pemda Kab.', 'Rp 500.000', '8 bulan'],
              ['Siti R.', 'PT ABC', 'Rp 350.000', '12 bulan'],
            ]
          }
        },
      ],
      penerima: [
        {
          id: 'dashboard', label: 'Dashboard', icon: LayoutDashboard,
          widgets: [
            { label: 'Bantuan Diterima', value: 5, suffix: 'jt', prefix: 'Rp ' },
            { label: 'Sisa Angsuran', value: 3.5, suffix: 'jt', prefix: 'Rp ' },
            { label: 'Angsuran/Bulan', value: 500, suffix: 'rb', prefix: 'Rp ' },
          ],
          table: {
            title: 'Riwayat Potong',
            headers: ['Bulan', 'Nominal', 'Metode', 'Status'],
            rows: [
              ['Juni 2026', 'Rp 500.000', 'Potong Gaji', 'Lunas'],
              ['Mei 2026', 'Rp 500.000', 'Potong Gaji', 'Lunas'],
              ['Apr 2026', 'Rp 500.000', 'Potong Gaji', 'Lunas'],
            ]
          }
        },
      ],
    }
  },
  {
    id: 'sinergi',
    name: 'Sinergi',
    tagline: 'Platform Kolaborasi dan Integrasi Antar Lembaga',
    problem: 'Koordinasi antar divisi atau lembaga masih mengandalkan komunikasi informal, menyebabkan miskomunikasi, duplikasi pekerjaan, dan lambatnya pengambilan keputusan lintas unit.',
    benefits: [
      'Single platform untuk koordinasi lintas unit',
      'Shared workspace dan document collaboration',
      'Tracking progress proyek bersama',
      'Dashboard monitoring terintegrasi',
    ],
    icon: Network,
    color: '#F59E0B',
    roles: [
      { id: 'koordinator', label: 'Koordinator', icon: UserCheck },
      { id: 'anggota', label: 'Anggota Tim', icon: UserCircle },
    ],
    modules: {
      koordinator: [
        {
          id: 'dashboard', label: 'Dashboard', icon: LayoutDashboard,
          widgets: [
            { label: 'Proyek Aktif', value: 12, suffix: '' },
            { label: 'Tim Terlibat', value: 8, suffix: ' unit' },
            { label: 'Task Selesai', value: 78, suffix: '%' },
            { label: 'Overdue', value: 5, suffix: ' task' },
          ],
          table: {
            title: 'Proyek Aktif',
            headers: ['Proyek', 'Lead', 'Progress', 'Deadline'],
            rows: [
              ['Integrasi Sistem A-B', 'Tim IT', '85%', '30 Jun'],
              ['Migrasi Database', 'Tim Infra', '60%', '15 Jul'],
              ['Harmonisasi SOP', 'Tim QA', '40%', '31 Jul'],
            ]
          }
        },
        { id: 'proyek', label: 'Manajemen Proyek', icon: GitBranch,
          widgets: [
            { label: 'Total Task', value: 245, suffix: '' },
            { label: 'In Progress', value: 42, suffix: '' },
          ],
          table: {
            title: 'Kanban Board',
            headers: ['Task', 'Assignee', 'Priority', 'Status'],
            rows: [
              ['Setup API Gateway', 'Andi', 'High', 'In Progress'],
              ['Design UI Module', 'Rina', 'Medium', 'Review'],
              ['Testing Integration', 'Dedi', 'High', 'To Do'],
            ]
          }
        },
        { id: 'monitoring', label: 'Monitoring', icon: TrendingUp,
          widgets: [
            { label: 'SLA Met', value: 92, suffix: '%' },
            { label: 'Avg Response Time', value: 4, suffix: ' jam' },
          ],
          table: {
            title: 'Performance Unit',
            headers: ['Unit', 'Task Selesai', 'On Time', 'Rating'],
            rows: [
              ['Tim IT', '45/50', '92%', '⭐ 4.5'],
              ['Tim Infra', '30/38', '85%', '⭐ 4.2'],
              ['Tim QA', '28/35', '88%', '⭐ 4.3'],
            ]
          }
        },
      ],
      anggota: [
        {
          id: 'dashboard', label: 'Dashboard', icon: LayoutDashboard,
          widgets: [
            { label: 'Task Saya', value: 8, suffix: '' },
            { label: 'Selesai', value: 5, suffix: '' },
            { label: 'Deadline Terdekat', value: 0, suffix: '', displayValue: '26 Jun' },
          ],
          table: {
            title: 'Task Saya',
            headers: ['Task', 'Proyek', 'Deadline', 'Status'],
            rows: [
              ['Review API Docs', 'Integrasi A-B', '26 Jun', 'In Progress'],
              ['Testing Module X', 'Migrasi DB', '28 Jun', 'To Do'],
            ]
          }
        },
      ],
    }
  },
  {
    id: 'btn-channeling',
    name: 'BTN Channeling',
    tagline: 'Sistem Channeling Kredit Perbankan',
    problem: 'Proses penyaluran kredit dari bank induk ke lembaga penyalur masih manual, lambat, dan rawan kesalahan data sehingga menghambat pencairan dan reporting ke bank induk.',
    benefits: [
      'Integrasi langsung dengan sistem bank induk',
      'Otomasi proses pengajuan dan pencairan kredit',
      'Monitoring portfolio pinjaman channeling',
      'Laporan channeling otomatis sesuai format bank',
    ],
    icon: Building,
    color: '#6366F1',
    roles: [
      { id: 'admin', label: 'Admin Channeling', icon: UserCheck },
      { id: 'ao', label: 'Account Officer', icon: UserCircle },
    ],
    modules: {
      admin: [
        {
          id: 'dashboard', label: 'Dashboard', icon: LayoutDashboard,
          widgets: [
            { label: 'Total Penyaluran', value: 15, suffix: 'M', prefix: 'Rp ' },
            { label: 'Debitur Aktif', value: 450, suffix: '' },
            { label: 'NPL Channeling', value: 1.5, suffix: '%' },
            { label: 'Pencairan Bulan Ini', value: 2.4, suffix: 'M', prefix: 'Rp ' },
          ],
          table: {
            title: 'Monitoring Penyaluran',
            headers: ['Bank Induk', 'Skema', 'Outstanding', 'Status'],
            rows: [
              ['BTN', 'KPR Subsidi', 'Rp 8.5M', 'Aktif'],
              ['BTN', 'KPR Non-Subsidi', 'Rp 4.2M', 'Aktif'],
              ['BTN', 'Kredit Multi Guna', 'Rp 2.3M', 'Aktif'],
            ]
          }
        },
        { id: 'pengajuan', label: 'Pengajuan', icon: FileText,
          widgets: [
            { label: 'Pending Review', value: 12, suffix: '' },
            { label: 'Disetujui', value: 340, suffix: '' },
          ],
          table: {
            title: 'Pengajuan Kredit',
            headers: ['Pemohon', 'Skema', 'Plafon', 'Status'],
            rows: [
              ['Ahmad Rasyid', 'KPR Subsidi', 'Rp 150jt', 'Verifikasi'],
              ['Siti Fatimah', 'KPR Subsidi', 'Rp 150jt', 'Approved'],
              ['Budi Prakoso', 'Multi Guna', 'Rp 50jt', 'Survey'],
            ]
          }
        },
        { id: 'portfolio', label: 'Portfolio', icon: Wallet,
          widgets: [
            { label: 'Total Portfolio', value: 15, suffix: 'M', prefix: 'Rp ' },
            { label: 'Kolektibilitas 1', value: 92, suffix: '%' },
          ],
          table: {
            title: 'Portfolio per Skema',
            headers: ['Skema', 'Debitur', 'Outstanding', 'NPL'],
            rows: [
              ['KPR Subsidi', '280', 'Rp 8.5M', '1.2%'],
              ['KPR Non-Subsidi', '120', 'Rp 4.2M', '1.8%'],
              ['Multi Guna', '50', 'Rp 2.3M', '2.1%'],
            ]
          }
        },
        { id: 'laporan', label: 'Laporan', icon: FileText,
          widgets: [
            { label: 'Laporan Siap', value: 5, suffix: '' },
          ],
          table: {
            title: 'Laporan Channeling',
            headers: ['Laporan', 'Format', 'Periode', 'Status'],
            rows: [
              ['Realisasi Penyaluran', 'BTN Format', 'Juni 2026', 'Siap'],
              ['Kualitas Aktiva', 'BTN Format', 'Juni 2026', 'Draft'],
              ['Delinquency Report', 'Internal', 'Juni 2026', 'Siap'],
            ]
          }
        },
      ],
      ao: [
        {
          id: 'dashboard', label: 'Dashboard', icon: LayoutDashboard,
          widgets: [
            { label: 'Debitur Saya', value: 45, suffix: '' },
            { label: 'Pengajuan Aktif', value: 5, suffix: '' },
            { label: 'Target Pencairan', value: 75, suffix: '%' },
          ],
          table: {
            title: 'Debitur Saya',
            headers: ['Nama', 'Skema', 'Outstanding', 'Kol'],
            rows: [
              ['Ahmad R.', 'KPR Subsidi', 'Rp 145jt', '1'],
              ['Siti F.', 'KPR Subsidi', 'Rp 148jt', '1'],
              ['Budi P.', 'Multi Guna', 'Rp 42jt', '2'],
            ]
          }
        },
        { id: 'survey', label: 'Survey & Analisa', icon: Search,
          widgets: [
            { label: 'Survey Pending', value: 3, suffix: '' },
          ],
          table: {
            title: 'Jadwal Survey',
            headers: ['Pemohon', 'Alamat', 'Tanggal', 'Status'],
            rows: [
              ['Hendra S.', 'Jl. Merdeka 12', '25 Jun', 'Scheduled'],
              ['Rina W.', 'Jl. Sudirman 8', '26 Jun', 'Scheduled'],
            ]
          }
        },
      ],
    }
  },
];
