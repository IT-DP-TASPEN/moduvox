<?php

namespace Database\Seeders;

use App\Models\Career;
use App\Models\Client;
use App\Models\Message;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Seed Services
        $services = [
            [
                'name' => 'Konsultasi IT & Transformasi Digital',
                'description' => 'Membantu perusahaan bertransformasi melalui penerapan teknologi digital yang tepat guna untuk meningkatkan efisiensi operasional.',
                'content' => 'Layanan ini mencakup audit sistem berjalan, perencanaan arsitektur IT, strategi migrasi cloud, serta penyusunan roadmap transformasi digital perusahaan yang terukur dan berkesinambungan. Tim ahli kami akan mendampingi dari tahap inisiasi hingga implementasi akhir.',
                'icon' => 'computer',
            ],
            [
                'name' => 'Pengembangan Perangkat Lunak Khusus',
                'description' => 'Membangun aplikasi enterprise (ERP, HRIS, CRM) yang disesuaikan dengan kebutuhan unik dan alur kerja bisnis Anda.',
                'content' => 'Kami memiliki pengalaman luas dalam mengembangkan perangkat lunak kustom menggunakan teknologi terkini seperti Laravel, Vue.js, React, dan arsitektur microservices. Sistem yang kami bangun difokuskan pada skalabilitas, keamanan, dan kemudahan penggunaan (user-friendly).',
                'icon' => 'code',
            ],
            [
                'name' => 'Layanan Cloud & Infrastruktur',
                'description' => 'Solusi manajemen server, migrasi ke cloud, dan optimasi infrastruktur IT untuk menjamin ketersediaan sistem 99.9%.',
                'content' => 'Bermitra dengan penyedia cloud terkemuka seperti AWS, Google Cloud, dan Azure, kami membantu mengelola arsitektur server, setup CI/CD, hingga monitoring kinerja secara real-time. Kami memastikan sistem Anda tetap berjalan optimal meskipun dengan lonjakan trafik yang tinggi.',
                'icon' => 'cloud',
            ],
            [
                'name' => 'Keamanan Siber & Audit Sistem',
                'description' => 'Identifikasi kerentanan aplikasi dan penguatan perlindungan data perusahaan dari berbagai ancaman siber.',
                'content' => 'Layanan security assessment kami meliputi penetration testing, vulnerability scanning, dan audit kepatuhan (compliance audit) terhadap standar seperti ISO 27001. Kami memberikan rekomendasi teknis yang jelas untuk menutup setiap celah keamanan pada infrastruktur IT Anda.',
                'icon' => 'security',
            ]
        ];

        foreach ($services as $service) {
            $service['slug'] = Str::slug($service['name']);
            Service::create($service);
        }

        // 2. Seed Projects
        $projects = [
            [
                'title' => 'Sistem Informasi Manajemen Rumah Sakit (SIMRS) Terintegrasi',
                'category' => 'Aplikasi Enterprise',
                'description' => 'Pengembangan SIMRS untuk menunjang operasional rumah sakit dengan modul lengkap mulai dari pendaftaran, rekam medis elektronik, hingga tagihan.',
                'project_scope' => 'Analisis kebutuhan, UI/UX Design, Pengembangan Backend & Frontend, Integrasi BPJS Kesehatan, Deployment, dan Pelatihan Staff.',
                'is_featured' => true,
                'client_name' => 'RSUD Provinsi Jawa Barat',
                'location' => 'Bandung',
                'year' => '2023',
            ],
            [
                'title' => 'Aplikasi Mobile Banking Koperasi Nasional',
                'category' => 'Mobile App',
                'description' => 'Aplikasi mobile untuk anggota koperasi yang memungkinkan transaksi transfer, pembayaran PPOB, cek saldo, dan pengajuan pinjaman secara online.',
                'project_scope' => 'Pengembangan aplikasi mobile berbasis Flutter, perancangan API aman, dan integrasi dengan sistem Core Banking Koperasi eksisting.',
                'is_featured' => true,
                'client_name' => 'KSP Sejahtera Bersama',
                'location' => 'Jakarta',
                'year' => '2022',
            ],
            [
                'title' => 'Portal E-Government Cerdas',
                'category' => 'Sistem Pemerintahan',
                'description' => 'Portal perizinan satu pintu berbasis web yang mempermudah masyarakat mengurus dokumen kependudukan dan perizinan usaha secara online.',
                'project_scope' => 'Pembuatan sistem berbasis web, migrasi data lama, penerapan Tanda Tangan Elektronik (TTE), dan keamanan jaringan.',
                'is_featured' => false,
                'client_name' => 'Pemerintah Kota Surabaya',
                'location' => 'Surabaya',
                'year' => '2023',
            ],
            [
                'title' => 'Human Resource Information System (HRIS) Cloud',
                'category' => 'Aplikasi Enterprise',
                'description' => 'Sistem HRIS berbasis komputasi awan untuk manajemen absensi menggunakan geotagging, perhitungan payroll, pajak PPh 21, dan penilaian kinerja.',
                'project_scope' => 'Pengembangan penuh SaaS HRIS, setup arsitektur multi-tenant, dan integrasi mesin absensi biometrik.',
                'is_featured' => true,
                'client_name' => 'PT Manufaktur Global Indonesia',
                'location' => 'Cikarang',
                'year' => '2024',
            ]
        ];

        foreach ($projects as $project) {
            $project['slug'] = Str::slug($project['title']);
            Project::create($project);
        }

        // 3. Seed Careers
        $careers = [
            [
                'title' => 'Senior Backend Engineer (Laravel)',
                'location' => 'Jakarta Selatan (Hybrid)',
                'type' => 'Full-time',
                'description' => 'Kami mencari Backend Engineer berpengalaman yang mahir menggunakan Laravel untuk membangun dan memelihara aplikasi skala enterprise. Anda akan bekerja sama dengan tim frontend dan arsitek infrastruktur untuk merancang API yang handal.',
                'requirements' => "<ul><li>Minimal 3 tahun pengalaman dengan PHP dan Framework Laravel.</li><li>Memahami konsep arsitektur RESTful API, Microservices, dan desain database relasional (MySQL/PostgreSQL).</li><li>Berpengalaman dengan Redis, sistem antrean (Queue), dan Elasticsearch menjadi nilai tambah.</li><li>Mampu menulis unit test dan menggunakan Git untuk versioning.</li></ul>",
                'is_active' => true,
            ],
            [
                'title' => 'UI/UX Designer',
                'location' => 'Bandung (Remote)',
                'type' => 'Full-time',
                'description' => 'Moduvox membutuhkan UI/UX Designer kreatif untuk merancang antarmuka aplikasi B2B dan B2C. Anda bertanggung jawab melakukan riset pengguna, membuat wireframe, hingga mendesain prototipe interaktif yang meningkatkan pengalaman pengguna.',
                'requirements' => "<ul><li>Portofolio yang menunjukkan desain antarmuka web dan mobile.</li><li>Mahir menggunakan Figma, Sketch, atau Adobe XD.</li><li>Memahami prinsip User-Centered Design (UCD) dan usability testing.</li><li>Mampu bekerja dalam alur kerja Agile/Scrum.</li></ul>",
                'is_active' => true,
            ],
            [
                'title' => 'DevOps Engineer',
                'location' => 'Jakarta',
                'type' => 'Full-time',
                'description' => 'Mencari spesialis DevOps untuk mengelola infrastruktur cloud kami. Fokus utama Anda adalah memastikan proses deployment berjalan mulus (CI/CD) dan menjaga ketersediaan server untuk puluhan klien kami.',
                'requirements' => "<ul><li>Pengalaman dengan layanan Cloud (AWS, GCP, atau DigitalOcean).</li><li>Terbiasa dengan Docker, Kubernetes, dan alat otomatisasi seperti Ansible atau Terraform.</li><li>Keahlian dalam CI/CD pipeline (GitLab CI, GitHub Actions, atau Jenkins).</li><li>Pemahaman kuat mengenai sistem Linux dan keamanan server.</li></ul>",
                'is_active' => false,
            ]
        ];

        foreach ($careers as $career) {
            $career['slug'] = Str::slug($career['title']);
            Career::create($career);
        }

        // 4. Seed Clients
        $clients = [
            ['name' => 'PT Bank Mandiri (Persero) Tbk'],
            ['name' => 'Kementerian Keuangan RI'],
            ['name' => 'PT Pertamina (Persero)'],
            ['name' => 'Koperasi Simpan Pinjam Nusantara'],
            ['name' => 'PT Telekomunikasi Indonesia'],
            ['name' => 'Rumah Sakit Umum Daerah (RSUD)']
        ];

        foreach ($clients as $client) {
            Client::create($client);
        }

        // 5. Seed Messages
        $messages = [
            [
                'name' => 'Budi Santoso - PT Logistik Cepat',
                'email' => 'budi.santoso@logistikcepat.co.id',
                'subject' => 'Permintaan Penawaran Sistem ERP',
                'message' => "Halo Tim Moduvox,\n\nPerusahaan kami berencana mengimplementasikan sistem ERP untuk manajemen logistik dan inventory. Bisakah kami menjadwalkan meeting untuk mendiskusikan kebutuhan sistem kami dan perkiraan budget?\n\nTerima kasih.",
                'is_read' => false,
            ],
            [
                'name' => 'Siti Aminah',
                'email' => 'siti.aminah.hrd@manufaktur.id',
                'subject' => 'Konsultasi Layanan HRIS',
                'message' => "Selamat siang,\n\nSaya melihat portofolio Moduvox untuk aplikasi HRIS. Saat ini perusahaan kami menggunakan sistem manual dan ingin beralih ke digital. Mohon informasi terkait modul yang tersedia dan estimasi waktu pengerjaan.\n\nSalam,\nSiti",
                'is_read' => true,
            ],
        ];

        foreach ($messages as $message) {
            Message::create($message);
        }
    }
}
