<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MitraMasterSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('mitra_masters')->insert([
            [
                'nama_mitra' => 'PT Mitra Sejahtera',
                'jenis_fee_banpot' => '1',
                'fee_banpot' => 10000.00,
                'saldo_mengendap' => 5000000.00,
                'biaya_checking' => 2500.00,
                'biaya_check_estimasi' => 1500.00,
                'biaya_flagging_pensiun' => 2000.00,
                'biaya_flagging_prapen' => 2000.00,
                'biaya_flagging_tht' => 2500.00,
                'biaya_flagging_prapen_tht' => 2500.00,
                'biaya_flagging_mutasi_tif' => 3000.00,
                'biaya_flagging_mutasi_tos' => 3000.00,
                'ppn' => 11.00,
                'pph' => 2.50,
                'jenis_pinbuk' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_mitra' => 'PT Solusi Mandiri',
                'jenis_fee_banpot' => '2',
                'fee_banpot' => 15000.00,
                'saldo_mengendap' => 7500000.00,
                'biaya_checking' => 3000.00,
                'biaya_check_estimasi' => 2000.00,
                'biaya_flagging_pensiun' => 2500.00,
                'biaya_flagging_prapen' => 2500.00,
                'biaya_flagging_tht' => 3000.00,
                'biaya_flagging_prapen_tht' => 3000.00,
                'biaya_flagging_mutasi_tif' => 3500.00,
                'biaya_flagging_mutasi_tos' => 3500.00,
                'ppn' => 11.00,
                'pph' => 2.50,
                'jenis_pinbuk' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
