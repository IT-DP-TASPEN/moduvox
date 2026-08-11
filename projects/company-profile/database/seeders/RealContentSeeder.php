<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\PageSetting;
use App\Models\Project;
use App\Models\Service;
use App\Models\SocialMedia;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class RealContentSeeder extends Seeder
{
    public function run()
    {
        // 1. Page Settings
        $settings = [
            ['page' => 'home', 'key' => 'home_hero_title', 'value' => 'PT Dwi Artha Prima'],
            ['page' => 'home', 'key' => 'home_hero_subtitle', 'value' => 'Mitra Strategis dalam Pembangunan Konstruksi dan Infrastruktur Nasional'],
            ['page' => 'home', 'key' => 'home_hero_video', 'value' => 'settings/home/cXHgt9ZuQUASmia9cmkwukVYgTzvdOuqEDO49qyO.mp4'],
            ['page' => 'home', 'key' => 'home_about_title', 'value' => 'Mewujudkan Infrastruktur yang Tangguh melalui Integritas dan Presisi.'],
            ['page' => 'home', 'key' => 'home_about_desc', 'value' => "PT Dwi Artha Prima adalah mitra strategis dalam sektor konstruksi dan infrastruktur di Indonesia. Kami menghadirkan integritas, inovasi, dan kualitas dalam setiap proyek yang kami tangani.\n\nKami berkomitmen dalam menyediakan layanan pekerjaan secara profesional dan tepat waktu, didukung oleh tenaga kerja berpengalaman serta standar mutu (QA/QC) dan keselamatan kerja (K3) yang konsisten guna memberikan hasil yang andal bagi setiap stakeholder."],
            ['page' => 'home', 'key' => 'home_vision', 'value' => 'Menjadi perusahaan jasa konstruksi terkemuka yang diakui secara nasional atas kualitas dan komitmen terhadap keselamatan kerja.'],
            ['page' => 'home', 'key' => 'home_mission', 'value' => 'Memberikan solusi teknis yang inovatif dan efisien untuk memenuhi harapan klien melalui profesionalisme dan keunggulan operasional.'],
            ['page' => 'home', 'key' => 'home_stats_years', 'value' => '15+'],
            ['page' => 'home', 'key' => 'home_stats_projects', 'value' => '200+'],
            ['page' => 'home', 'key' => 'home_stats_clients', 'value' => '50+'],
            ['page' => 'home', 'key' => 'home_stats_regions', 'value' => '12+'],
            ['page' => 'project', 'key' => 'project_hero_title', 'value' => 'Portofolio Proyek'],
            ['page' => 'project', 'key' => 'project_hero_desc', 'value' => 'Representasi kapabilitas kami dalam menangani berbagai skala pekerjaan konstruksi, infrastruktur, dan engineering dengan mengedepankan standar kualitas serta ketepatan waktu.'],
            ['page' => 'project', 'key' => 'project_cta_title', 'value' => 'Siap memulai proyek Anda?'],
            ['page' => 'project', 'key' => 'project_cta_desc', 'value' => 'Sampaikan kebutuhan proyek Anda. Tim kami siap membantu menyusun rencana kerja awal, estimasi, dan pendekatan pelaksanaan yang terukur.'],
            ['page' => 'about', 'key' => 'about_hero_title', 'value' => 'Mitra Konstruksi & Engineering yang Terukur'],
            ['page' => 'about', 'key' => 'about_hero_desc', 'value' => 'Berkomitmen menyediakan layanan konstruksi dan infrastruktur dengan standar K3, pengendalian mutu, dan manajemen proyek yang disiplin.'],
            ['page' => 'about', 'key' => 'about_story_title', 'value' => 'Profil Perusahaan'],
            ['page' => 'about', 'key' => 'about_story_desc', 'value' => "PT Dwi Artha Prima berfokus pada pekerjaan konstruksi, infrastruktur, dan jasa engineering. Kami memiliki pengalaman dalam menangani berbagai cakupan pekerjaan sipil, mekanikal, dan elektrikal dengan dukungan tenaga kerja yang kompeten di bidangnya.\n\nSetiap langkah operasional kami berlandaskan pada prinsip profesionalisme dan integritas, di mana aspek keselamatan kerja serta pengendalian mutu (QA/QC) menjadi standar baku yang kami terapkan secara konsisten di setiap pekerjaan."],
            ['page' => 'about', 'key' => 'about_vision', 'value' => 'Menjadi perusahaan jasa konstruksi terpercaya yang diakui secara nasional atas kualitas pekerjaan dan integritas dalam bermitra.'],
            ['page' => 'about', 'key' => 'about_mission', 'value' => "Mengimplementasikan standar K3 dan pengendalian mutu (QA/QC) secara ketat di setiap proyek.\nMenjamin ketepatan waktu penyelesaian melalui perencanaan dan pengawasan progres yang sistematis.\nMemberikan solusi teknis dan engineering yang efisien serta dapat dipertanggungjawabkan."],
            ['page' => 'services', 'key' => 'services_hero_title', 'value' => 'Layanan Konstruksi & Engineering Terintegrasi'],
            ['page' => 'services', 'key' => 'services_hero_desc', 'value' => 'Dari tahap perencanaan hingga pelaksanaan lapangan, kami menyediakan layanan komprehensif dengan fokus pada mutu pekerjaan dan keselamatan kerja.'],
            ['page' => 'services', 'key' => 'services_cta_title', 'value' => 'Permintaan Estimasi & Konsultasi Teknis'],
            ['page' => 'services', 'key' => 'services_cta_desc', 'value' => 'Diskusikan spesifikasi proyek Anda bersama tenaga ahli kami untuk mendapatkan estimasi yang realistis sesuai kebutuhan anggaran dan jadwal.'],
            ['page' => 'contact', 'key' => 'contact_email', 'value' => 'dwiarthaprima@gmail.com'],
            ['page' => 'contact', 'key' => 'contact_phone', 'value' => '(0251) 2007064'],
            ['page' => 'contact', 'key' => 'contact_whatsapp', 'value' => 'https://wa.me/6221555123'],
            ['page' => 'contact', 'key' => 'contact_address', 'value' => 'Kp Gg. Kb. Kopi, RT.005/RW.008, Pengasinan, Kec. Sawangan, Kota Depok, Jawa Barat 16518'],
            ['page' => 'contact', 'key' => 'contact_hours', 'value' => 'Senin–Jumat, 08:00–17:00 WIB'],
        ];

        foreach ($settings as $s) {
            PageSetting::updateOrCreate(['page' => $s['page'], 'key' => $s['key']], ['value' => $s['value']]);
        }

        // 2. Social Media
        $socials = [
            ['name' => 'LinkedIn', 'icon' => 'hub', 'url' => 'https://linkedin.com/company/dwiarthaprima', 'order' => 1, 'is_active' => true],
            ['name' => 'Instagram', 'icon' => 'photo_camera', 'url' => 'https://instagram.com/dwiarthaprima', 'order' => 2, 'is_active' => true],
        ];

        foreach ($socials as $soc) {
            SocialMedia::updateOrCreate(['name' => $soc['name']], $soc);
        }

        // 3. Services
        $services = [
            ['name' => 'Konstruksi Infrastruktur', 'description' => 'Pembangunan jalan, drainase, jembatan, dan pekerjaan struktur dengan standar teknis serta mutu material yang terukur.', 'icon' => 'construction'],
            ['name' => 'Gedung & Bangunan', 'description' => 'Pekerjaan gedung perkantoran, fasilitas umum, dan bangunan penunjang dengan pengendalian kualitas dan keselamatan kerja.', 'icon' => 'apartment'],
            ['name' => 'Engineering & Design', 'description' => 'Kajian teknis, estimasi biaya, shop drawing, dan perencanaan metode kerja yang efisien dan dapat dipertanggungjawabkan.', 'icon' => 'architecture'],
            ['name' => 'General Contractor', 'description' => 'Manajemen proyek end-to-end: schedule, kontrol biaya, koordinasi subkontraktor, hingga commissioning dan serah terima.', 'icon' => 'engineering'],
            ['name' => 'Maintenance & Retrofit', 'description' => 'Pemeliharaan aset, perkuatan struktur, perbaikan beton, waterproofing, dan pekerjaan remedial untuk memperpanjang umur bangunan.', 'icon' => 'handyman'],
            ['name' => 'Kawasan Industri', 'description' => 'Pembangunan infrastruktur kawasan: jalan internal, utilitas dasar, dan pekerjaan sipil pendukung untuk area operasional industri.', 'icon' => 'factory'],
            ['name' => 'Pengadaan Material', 'description' => 'Pengadaan material dan peralatan dengan kontrol spesifikasi, dokumentasi, dan ketertelusuran (traceability).', 'icon' => 'inventory_2'],
            ['name' => 'K3 & QA/QC', 'description' => 'Pendampingan implementasi K3, inspeksi, dan pengujian mutu untuk memastikan pekerjaan sesuai standar dan gambar kerja.', 'icon' => 'policy'],
        ];

        foreach ($services as $ser) {
            Service::updateOrCreate(['name' => $ser['name']], $ser);
        }

        // 4. Clients
        $clients = [
            [
                'name' => 'Rumah Sakit SILOAM',
                'logo' => 'clients/NvgXSXIaBKQJgIid53xflxcpRM3ojDgkHFDYSlM9.png'
            ],
            ['name' => 'Kementerian PUPR'],
            ['name' => 'PT PLN (Persero)'],
            ['name' => 'PT Pertamina (Persero)'],
            ['name' => 'PT Jasa Marga (Persero) Tbk'],
        ];

        foreach ($clients as $cli) {
            Client::updateOrCreate(['name' => $cli['name']], $cli);
        }

        // 5. Projects
        $projects = [
            [
                'title' => 'Pembangunan PIT LIFT RS Putera Bahagia Cirebon',
                'category' => 'Konstruksi Rumah Sakit',
                'description' => "PT DWI ARTHA PRIMA melaksanakan proyek pembangunan PIT LIFT di RS Putera Bahagia guna mendukung pengembangan fasilitas dan operasional rumah sakit. Pekerjaan mencakup konstruksi area pit lift, pekerjaan struktur, finishing, serta penyesuaian area pendukung dengan standar keamanan dan kualitas konstruksi yang baik.\n\nProyek dikerjakan secara profesional dengan memperhatikan ketepatan waktu, kualitas pekerjaan, serta mendukung kenyamanan dan efisiensi operasional fasilitas rumah sakit.",
                'image' => 'projects/ThNq5VBGfWrk2MhG15gggSS5lIJs6mCHCSBQ5QII.png',
                'is_featured' => true,
            ],
            [
                'title' => 'Renovasi ICU & NICU RS Siloam Cikarang',
                'category' => 'Konstruksi Rumah Sakit',
                'description' => "Pelaksanaan pekerjaan renovasi area ICU (Intensive Care Unit) dan NICU (Neonatal Intensive Care Unit) RS Siloam Cikarang yang meliputi pekerjaan interior, perbaikan tata ruang, mekanikal, elektrikal, pencahayaan, tata udara, serta instalasi pendukung fasilitas medis sesuai kebutuhan operasional rumah sakit.\n\nPekerjaan renovasi dilakukan dengan memperhatikan standar kualitas, keselamatan kerja, ketepatan waktu pelaksanaan, serta menjaga kenyamanan dan kelancaran aktivitas pelayanan rumah sakit selama proses pekerjaan berlangsung.",
                'image' => 'projects/B5BHeHNOPWOlY9gxIR9TOD6VnG0iCwvijstc1A5t.png',
                'is_featured' => true,
            ],
            [
                'title' => 'Pembangunan PIT LIFT RS Siloam Syubanol Wathon Magelang',
                'category' => 'Konstruksi Rumah Sakit',
                'description' => "Pelaksanaan pekerjaan pembangunan PIT LIFT RS Siloam Syubanol Wathon Magelang yang meliputi pekerjaan sipil, struktur, mekanikal, elektrikal, serta pekerjaan pendukung lainnya sesuai spesifikasi dan kebutuhan operasional rumah sakit.\n\nPekerjaan dilaksanakan dengan mengutamakan kualitas konstruksi, ketepatan waktu, keselamatan kerja, serta standar pelaksanaan proyek guna mendukung fasilitas pelayanan rumah sakit yang aman, modern, dan fungsional.",
                'image' => 'projects/dYL0s6QWNc7Pd2BSGoI9nS0ObqtOeKdY9Vzr5Jxn.png',
                'is_featured' => true,
            ],
        ];

        foreach ($projects as $proj) {
            Project::updateOrCreate(
                ['title' => $proj['title']],
                array_merge($proj, ['slug' => Str::slug($proj['title'])])
            );
        }
    }
}
