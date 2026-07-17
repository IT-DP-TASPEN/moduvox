<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GapokMaster;
use App\Models\HonorariumMaster;
use App\Models\PayrollSetting;

class PayrollSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Gapok Master (Tetap)
        $baseAmount = 3339358;
        $perGolStep = 230000;
        $perSkgRates = [
            'I' => 86250,
            'II' => 115000,
            'III' => 143750,
            'IV' => 172500,
            'V' => 201250,
            'VI' => 258750,
            'VII' => 316250,
            'VIII' => 402500,
            'IX' => 488750,
            'X' => 575000,
        ];

        $grades = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'];

        foreach ($grades as $index => $grade) {
            $baseForGrade = $baseAmount + ($index * $perGolStep);
            for ($skg = 1; $skg <= 30; $skg++) {
                GapokMaster::create([
                    'skg' => $skg,
                    'grade' => $grade,
                    'amount' => $baseForGrade + (($skg - 1) * $perSkgRates[$grade]),
                ]);
            }
        }

        // 2. Seed Honorarium Master (Kontrak)
        $honorData = [
            ['Branch Manager', 7006877, 8442524, 9878171],
            ['Head of Project', 7006877, 8442524, 9878171],
            ['Head of Funding and Research Development Business', 7006877, 8442524, 9878171],
            ['Manager of Business', 6510077, 7583061, 8656044],
            ['General Manager of Operations', 6510077, 7583061, 8656044],
            ['Staf Direksi', 6510077, 7583061, 8656044],
            ['Head of Internal Audit', 6510077, 7583061, 8656044],
            ['Head of Risk Management', 6510077, 7583061, 8656044],
            ['Head of Legal and Compliance', 6510077, 7583061, 8656044],
            ['Sub Branch Manager', 6013277, 6549769, 7086261],
            ['Archive Staff', 4694760, 5354019, 6013277],
            ['Teller', 4694760, 5354019, 6013277],
            ['Kontrak Langsung Jabodetabek', 4694760, 0, 0],
        ];

        foreach ($honorData as $item) {
            if ($item[1] > 0) {
                HonorariumMaster::create(['position_name' => $item[0], 'level' => 'MUDA', 'amount' => $item[1]]);
            }
            if ($item[2] > 0) {
                HonorariumMaster::create(['position_name' => $item[0], 'level' => 'MADYA', 'amount' => $item[2]]);
            }
            if ($item[3] > 0) {
                HonorariumMaster::create(['position_name' => $item[0], 'level' => 'UTAMA', 'amount' => $item[3]]);
            }
        }

        // 3. Seed Default Payroll Settings
        PayrollSetting::create([
            'overtime_rate_permanent' => 30000, // Contoh default
            'overtime_rate_contract' => 25000,
            'overtime_meal_allowance' => 15000,
            'max_overtime_hours_contract' => 3,
        ]);
    }
}
