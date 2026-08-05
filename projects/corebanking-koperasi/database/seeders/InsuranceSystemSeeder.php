<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InsuranceSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $provider = \App\Models\InsuranceProvider::firstOrCreate(
            ['provider_code' => 'AS-001'],
            [
                'name' => 'Asuransi Jiwa Bersama',
                'is_active' => true,
                'created_by' => 1,
                'updated_by' => 1,
            ]
        );

        $product = \App\Models\InsuranceProduct::firstOrCreate(
            ['product_code' => 'INP-0001'],
            [
                'insurance_provider_id' => $provider->id,
                'name' => 'Proteksi Jiwa Kredit Reguler',
                'type' => 'JIWA',
                'calculation_base' => 'PLAFOND',
                'is_active' => true,
                'created_by' => 1,
                'updated_by' => 1,
            ]
        );

        // Seed Rates individually for Age 17 - 85 and JKW 1 - 20
        $batch = [];
        for ($age = 17; $age <= 85; $age++) {
            for ($year = 1; $year <= 20; $year++) {
                // Determine max tenor for this age (arbitrary logic to match image pattern of shorter tenors for older age)
                // In image: age 69 max is 15. Let's say age 85 max is 5.
                $maxYear = 20;
                if ($age > 65) {
                    $maxYear = 20 - ($age - 65);
                    if ($maxYear < 1) $maxYear = 1;
                }

                if ($year > $maxYear) continue;

                $batch[] = [
                    'insurance_product_id' => $product->id,
                    'age' => $age,
                    'tenor_months' => $year * 12,
                    'rate' => $year * (0.5 + ($age * 0.001)), // Slightly varying rates as requested
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            // Insert in batches to avoid memory issues
            if (count($batch) >= 200) {
                \App\Models\InsuranceRate::insert($batch);
                $batch = [];
            }
        }
        
        if (!empty($batch)) {
            \App\Models\InsuranceRate::insert($batch);
        }
    }
}
