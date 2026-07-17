<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DivisionApprover;

class DivisionApproverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ["division_name"=>"Div. IT","approver_id"=>45,"director_id"=>82],
            ["division_name"=>"Div. Bisnis","approver_id"=>90,"director_id"=>81],
            ["division_name"=>"Div. Keuangan","approver_id"=>91,"director_id"=>80],
            ["division_name"=>"Div. Operasional","approver_id"=>92,"director_id"=>82],
            ["division_name"=>"Div. Legal","approver_id"=>95,"director_id"=>80],
            ["division_name"=>"Divisi SKAI","approver_id"=>94,"director_id"=>82],
            ["division_name"=>"KC Bogor","approver_id"=>67,"director_id"=>90],
            ["division_name"=>"KC Cikarang","approver_id"=>55,"director_id"=>90],
            ["division_name"=>"KC Karawang","approver_id"=>69,"director_id"=>90],
            ["division_name"=>"KPO","approver_id"=>65,"director_id"=>90],
            ["division_name"=>"KC Jaktim","approver_id"=>64,"director_id"=>90],
            ["division_name"=>"KC Depok","approver_id"=>68,"director_id"=>90],
            ["division_name"=>"KC Tangerang","approver_id"=>66,"director_id"=>90],
            ["division_name"=>"KC Purwokerto","approver_id"=>96,"director_id"=>90],
            ["division_name"=>"Div. RBC","approver_id"=>45,"director_id"=>null],
        ];

        foreach ($data as $item) {
            DivisionApprover::updateOrCreate(
                ['division_name' => $item['division_name']],
                [
                    'approver_id' => $item['approver_id'],
                    'director_id' => $item['director_id'],
                ]
            );
        }

        $this->command->info("DivisionApproverSeeder berhasil dijalankan.");
    }
}
