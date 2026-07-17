<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GlobalAllowanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $components = [
            // --- TUNJANGAN (ALLOWANCE) ---
            [
                'name' => 'Tunjangan Makan',
                'amount' => 500000,
                'type' => 'fixed',
                'category' => 'earning',
                'target_status' => 'All',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tunjangan Transport',
                'amount' => 300000,
                'type' => 'fixed',
                'category' => 'earning',
                'target_status' => 'All',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tunjangan Kinerja (Insentif)',
                'amount' => 0.1, // 10% dari gapok sebagai base
                'type' => 'percentage_gapok',
                'category' => 'earning',
                'target_status' => 'All',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // --- POTONGAN KARYAWAN (DEDUCTION) ---
            [
                'name' => 'BPJS TK (JHT) - Karyawan 2%',
                'amount' => 0.02,
                'type' => 'percentage_gapok',
                'category' => 'deduction',
                'target_status' => 'All',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'BPJS TK (JP) - Karyawan 1%',
                'amount' => 0.01,
                'type' => 'percentage_gapok',
                'category' => 'deduction',
                'target_status' => 'All',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'BPJS Kesehatan - Karyawan 1%',
                'amount' => 0.01,
                'type' => 'percentage_gapok',
                'category' => 'deduction',
                'target_status' => 'All',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Moduvox Save (Potongan Khusus Tetap)',
                'amount' => 0.04, // Misal 4%
                'type' => 'percentage_gapok',
                'category' => 'deduction',
                'target_status' => 'Tetap',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // --- BEBAN PERUSAHAAN (COMPANY PAID - NON THP) ---
            [
                'name' => 'BPJS JKK - Perusahaan 0.24%',
                'amount' => 0.0024,
                'type' => 'percentage_gapok',
                'category' => 'company_paid',
                'target_status' => 'All',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'BPJS JKM - Perusahaan 0.3%',
                'amount' => 0.003,
                'type' => 'percentage_gapok',
                'category' => 'company_paid',
                'target_status' => 'All',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'BPJS TK (JHT) - Perusahaan 3.7%',
                'amount' => 0.037,
                'type' => 'percentage_gapok',
                'category' => 'company_paid',
                'target_status' => 'All',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'BPJS TK (JP) - Perusahaan 2%',
                'amount' => 0.02,
                'type' => 'percentage_gapok',
                'category' => 'company_paid',
                'target_status' => 'All',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'BPJS Kesehatan - Perusahaan 4%',
                'amount' => 0.04,
                'type' => 'percentage_gapok',
                'category' => 'company_paid',
                'target_status' => 'All',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        \Illuminate\Support\Facades\DB::table('global_allowances')->truncate();
        \Illuminate\Support\Facades\DB::table('global_allowances')->insert($components);
    }
}
