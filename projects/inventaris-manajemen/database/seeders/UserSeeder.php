<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\MstKantor;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userData = [
            [
                "name" => "Moduvox Admin",
                "username" => "admin",
                "employee_id" => "0000000",
                "email" => "admin@moduvox.id",
                "phone" => "080000000000",
                "title" => "super_admin",
                "unit_name" => "IT",
                "division_name" => "IT",
                "office_type" => "head_office",
                "branch_code" => "00",
                "is_admin" => 1,
                "password" => "password"
            ],
            [
                "name" => "Moduvox Staff Akunting 1",
                "username" => "staff_akt_1",
                "employee_id" => "1111111",
                "email" => "staff1.akt@moduvox.id",
                "phone" => "081111111111",
                "title" => "staff",
                "unit_name" => "Accounting",
                "division_name" => "Accounting",
                "office_type" => "head_office",
                "branch_code" => null,
                "is_admin" => 0,
                "password" => "password"
            ],
            [
                "name" => "Moduvox Staff Akunting 2",
                "username" => "staff_akt_2",
                "employee_id" => "1111112",
                "email" => "staff2.akt@moduvox.id",
                "phone" => "081111111112",
                "title" => "staff",
                "unit_name" => "Accounting",
                "division_name" => "Accounting",
                "office_type" => "head_office",
                "branch_code" => null,
                "is_admin" => 0,
                "password" => "password"
            ],
            [
                "name" => "Moduvox Asst Manager Akunting",
                "username" => "asmen_akt",
                "employee_id" => "2222222",
                "email" => "asmen.akt@moduvox.id",
                "phone" => "082222222222",
                "title" => "asst_manager",
                "unit_name" => "Accounting",
                "division_name" => "Accounting",
                "office_type" => "head_office",
                "branch_code" => null,
                "is_admin" => 0,
                "password" => "password"
            ],
            [
                "name" => "Moduvox General Manager Akunting",
                "username" => "gm_akt",
                "employee_id" => "3333333",
                "email" => "gm.akt@moduvox.id",
                "phone" => "083333333333",
                "title" => "general_manager",
                "unit_name" => "Accounting",
                "division_name" => "Accounting",
                "office_type" => "head_office",
                "branch_code" => null,
                "is_admin" => 0,
                "password" => "password"
            ],
            [
                "name" => "Moduvox Staff Cabang A",
                "username" => "staff_cab_a",
                "employee_id" => "4444441",
                "email" => "staff.caba@moduvox.id",
                "phone" => "084444444441",
                "title" => "staff",
                "unit_name" => "Operasional",
                "division_name" => "Cabang",
                "office_type" => "branch_office",
                "branch_code" => "02",
                "is_admin" => 0,
                "password" => "password"
            ],
        ];

        foreach ($userData as $data) {
            // Find kantor by branch_code if available
            $kantorId = null;
            if (!empty($data['branch_code'])) {
                $kantor = MstKantor::where('kode', $data['branch_code'])->first();
                if ($kantor) {
                    $kantorId = $kantor->id;
                } else {
                    // Fallback, if '00' then it's typically '01' (KP Operasional) in this system 
                    // depending on how it's matched in MstKantor
                    if ($data['branch_code'] == '00') {
                        $kantorId = MstKantor::where('kode', '01')->first()?->id;
                    }
                }
            } else {
                // If branch_code is null but office_type is head_office, set it to KP Operasional
                if ($data['office_type'] === 'head_office') {
                    $kantorId = MstKantor::where('kode', '01')->first()?->id;
                }
            }

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'username' => $data['username'],
                    'employee_id' => $data['employee_id'],
                    'phone' => $data['phone'],
                    'title' => $data['title'],
                    'unit_name' => $data['unit_name'],
                    'division_name' => $data['division_name'],
                    'office_type' => $data['office_type'],
                    'branch_code' => $data['branch_code'],
                    'is_admin' => $data['is_admin'],
                    'password' => bcrypt($data['password']),
                    'kantor_id' => $kantorId,
                ]
            );

            // Assign Spatie Roles based on title or unit
            if ($data['title'] === 'super_admin' || $data['is_admin'] == 1) {
                $user->assignRole('Super Admin');
            } else if ($data['title'] === 'staff') {
                $user->assignRole('Akunting Maker');
            } else if (in_array($data['title'], ['asst_manager', 'general_manager'])) {
                $user->assignRole('Akunting Checker');
            } else {
                $user->assignRole('Cabang');
            }
        }
    }
}
