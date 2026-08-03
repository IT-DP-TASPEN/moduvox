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
            DivisionApproverSeeder::class, // Missing: Sets up Division Approvers
            KpiIndicatorSeeder::class,     // Sync KPI dynamic indicators
            MasterDataSeeder::class,
            PositionAllowanceMasterSeeder::class, // Missing: Legacy master for positions
        ]);

        // Migrate Legacy positions into structured positions
        \Illuminate\Support\Facades\Artisan::call('app:migrate-positions');
        $this->command->info("Migrated positions successfully.");

        $this->call([
            GlobalAllowanceSeeder::class,
            EmployeeRecordSeeder::class,
            PayrollSeeder::class,
        ]);

        // Automatically run the monthly cycle to generate dummy attendance, leaves, overtime, KPI, and payrolls!
        \Illuminate\Support\Facades\Artisan::call('demo:monthly-cycle');
        $this->command->info("Demo monthly cycle generated successfully.");
    }
}
