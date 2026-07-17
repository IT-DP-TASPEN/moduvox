<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            OfficeSeeder::class,
            DivisionSeeder::class,
            UserSeeder::class,
            KpiIndicatorSeeder::class,     // Sync KPI dynamic indicators
            MasterDataSeeder::class,
            GlobalAllowanceSeeder::class,
            // EmployeeRecordSeeder::class, // Optional
            // PayrollSeeder::class,        // Optional
        ]);
    }
}
