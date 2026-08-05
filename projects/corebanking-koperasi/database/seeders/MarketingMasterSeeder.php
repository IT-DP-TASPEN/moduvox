<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\MarketingMaster;
use Illuminate\Database\Seeder;

class MarketingMasterSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::query()->where('is_active', true)->get();
        if ($branches->isEmpty()) {
            return;
        }

        $seedRows = [
            ['suffix' => '001', 'name' => 'Marketing Internal 01', 'phone' => '081234500001'],
            ['suffix' => '002', 'name' => 'Marketing Internal 02', 'phone' => '081234500002'],
        ];

        foreach ($branches as $branch) {
            foreach ($seedRows as $row) {
                $code = sprintf('MKT-%s-%s', $branch->branch_code ?? str_pad((string) $branch->id, 4, '0', STR_PAD_LEFT), $row['suffix']);

                MarketingMaster::updateOrCreate(
                    ['marketing_code' => $code],
                    [
                        'name' => $row['name'] . ' ' . ($branch->name ?? ''),
                        'phone' => $row['phone'],
                        'is_active' => true,
                        'branch_master_id' => $branch->id,
                    ]
                );
            }
        }
    }
}

