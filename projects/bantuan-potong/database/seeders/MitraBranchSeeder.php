<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MitraBranchSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('mitra_branches')->insert([
            [
                'mitra_master_id' => 1,
                'nama_cabang' => 'Jakarta Pusat',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'mitra_master_id' => 1,
                'nama_cabang' => 'Jakarta Selatan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'mitra_master_id' => 2,
                'nama_cabang' => 'Bandung Kota',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'mitra_master_id' => 2,
                'nama_cabang' => 'Surabaya Timur',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
