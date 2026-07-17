<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Office;

class OfficeSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'code' => 'KP',
                'name' => 'Kantor Pusat',
                'address' => 'Jl. Kebon Sirih No.45, Jakarta Pusat',
                'latitude' => -6.183421,
                'longitude' => 106.827153,
                'radius' => 100,
            ],
            [
                'code' => 'KC-01',
                'name' => 'KC Bogor',
                'address' => 'Jl. Pajajaran No.10, Bogor',
                'latitude' => -6.597147,
                'longitude' => 106.806039,
                'radius' => 150,
            ],
        ];

        foreach ($data as $item) {
            Office::updateOrCreate(['code' => $item['code']], $item);
        }
    }
}
