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
        // 3. Seed Default Payroll Settings
        PayrollSetting::create([
            'overtime_rate_permanent' => 30000, // Contoh default
            'overtime_rate_contract' => 25000,
            'overtime_meal_allowance' => 15000,
            'max_overtime_hours_contract' => 3,
        ]);
    }
}
