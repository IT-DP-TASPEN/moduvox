<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Division;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['code' => 'DIV01', 'name' => 'Div. Operasional'],
            ['code' => 'DIV02', 'name' => 'Div. Bisnis'],
            ['code' => 'DIV03', 'name' => 'Div. Legal'],
            ['code' => 'DIV04', 'name' => 'Div. Manajemen Risiko'],
            ['code' => 'DIV05', 'name' => 'Div. RBC'],
            ['code' => 'DIV06', 'name' => 'Div. Audit'],
            ['code' => 'DIV07', 'name' => 'Div. Keuangan'],
            ['code' => 'DIV08', 'name' => 'Div. IT'],
            ['code' => '001', 'name' => 'KPO'],
            ['code' => '002', 'name' => 'KC Bogor'],
            ['code' => '003', 'name' => 'KC Depok'],
            ['code' => '004', 'name' => 'KC Tangerang'],
            ['code' => '005', 'name' => 'KC Jaktim'],
            ['code' => '006', 'name' => 'KC Karawang'],
            ['code' => '007', 'name' => 'KC Cikarang'],
            ['code' => '008', 'name' => 'KC Purwokerto'],
        ];

        foreach ($data as $item) {
            Division::updateOrCreate(['code' => $item['code']], $item);
        }
    }
}
