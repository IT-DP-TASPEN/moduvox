<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\EmploymentDetail;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = base_path('../assets/data user/users_final.json');
        
        if (!file_exists($jsonPath)) {
            $this->command->error("File JSON tidak ditemukan di: $jsonPath");
            return;
        }

        $json = file_get_contents($jsonPath);
        $users = json_decode($json, true);

        if (!$users) {
            $this->command->error("Format JSON tidak valid atau file kosong.");
            return;
        }

        // Create Super Admin Default
        User::updateOrCreate(
            ['email' => 'admin@moduvox.com'],
            [
                'name' => 'Super Admin',
                'username' => 'admin',
                'employee_id' => 'ADMIN001',
                'phone' => '08123456789',
                'title' => 'IT Administrator',
                'division_name' => 'Div. IT',
                'is_admin' => true,
                'password' => Hash::make('DPT@SP3n'),
                'email_verified_at' => now(),
            ]
        );

        foreach ($users as $user) {
            $createdUser = User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'username' => $user['username'],
                    'employee_id' => $user['employee_id'],
                    'phone' => $user['phone'],
                    'title' => $user['title'],
                    'unit_name' => $user['unit_name'],
                    'division_name' => $user['division_name'],
                    'office_type' => $user['office_type'],
                    'branch_code' => $user['branch_code'],
                    'branch_name' => $user['branch_name'],
                    'is_admin' => $user['is_admin'],
                    'password' => Hash::make('DPT@SP3n'),
                    'email_verified_at' => $user['email_verified_at'] ?? now(),
                    'created_at' => $user['created_at'] ?? now(),
                    'updated_at' => $user['updated_at'] ?? now(),
                ]
            );

            // Estimate Basic Salary based on Title
            $salary = 4500000; // Default Staff
            $title = strtolower($user['title']);
            
            if (str_contains($title, 'direktur') || str_contains($title, 'dirut')) {
                $salary = 25000000;
            } elseif (str_contains($title, 'kepala divisi') || str_contains($title, 'kadiv')) {
                $salary = 15000000;
            } elseif (str_contains($title, 'kepala kantor') || str_contains($title, 'kakan')) {
                $salary = 12000000;
            } elseif (str_contains($title, 'supervisor') || str_contains($title, 'kabag')) {
                $salary = 8500000;
            } elseif (str_contains($title, 'senior')) {
                $salary = 6500000;
            }

            // Sync Employment Detail (Grade, SKG, Basic Salary)
            EmploymentDetail::updateOrCreate(
                ['user_id' => $createdUser->id],
                [
                    'grade' => '1',
                    'skg' => '1',
                    'basic_salary' => $salary,
                    'employment_status' => 'Tetap',
                    'join_date' => $user['created_at'] ?? now(),
                    'position' => $user['title'],
                    'department' => $user['division_name'],
                ]
            );
        }

        $this->command->info("UserSeeder & EmploymentDetail berhasil disinkronkan.");
    }
}
