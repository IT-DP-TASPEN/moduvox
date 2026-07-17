<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KpiIndicatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $indicators = [
            [
                'label' => 'Kedisiplinan',
                'slug' => 'kedisiplinan',
                'description' => 'Ketepatan waktu, kehadiran, dan kepatuhan aturan.',
                'sort_order' => 1,
            ],
            [
                'label' => 'Kualitas Kerja',
                'slug' => 'kualitas_kerja',
                'description' => 'Keakuratan, kerapihan, dan efektivitas hasil kerja.',
                'sort_order' => 2,
            ],
            [
                'label' => 'Kerjasama',
                'slug' => 'kerjasama',
                'description' => 'Kemampuan berkolaborasi dengan tim dan departemen lain.',
                'sort_order' => 3,
            ],
            [
                'label' => 'Tanggung Jawab',
                'slug' => 'tanggung_jawab',
                'description' => 'Komitmen menyelesaikan tugas dan kepedulian terhadap hasil.',
                'sort_order' => 4,
            ],
            [
                'label' => 'Sikap & Etika',
                'slug' => 'sikap_etika',
                'description' => 'Profesionalisme, integritas, dan perilaku di lingkungan kerja.',
                'sort_order' => 5,
            ],
        ];

        foreach ($indicators as $indicator) {
            \App\Models\KpiIndicator::updateOrCreate(['slug' => $indicator['slug']], $indicator);
        }
    }
}
