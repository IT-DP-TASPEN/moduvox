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
        $divisions = \App\Models\Division::all();
        $admin = \App\Models\User::where('is_admin', true)->first();

        foreach ($divisions as $div) {
            // Pick a staff in this division to be the approver
            $approver = \App\Models\User::where('division_name', $div->name)->first();

            if ($approver) {
                DivisionApprover::updateOrCreate(
                    ['division_name' => $div->name],
                    [
                        'approver_id' => $approver->id,
                        'director_id' => $admin ? $admin->id : $approver->id,
                    ]
                );
            }
        }

        $this->command->info("DivisionApproverSeeder berhasil dijalankan.");
    }
}
