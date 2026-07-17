<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = \App\Models\User::first();
        if (!$user) return;

        // Mutasi
        \App\Models\Mutation::create([
            'user_id' => $user->id,
            'type' => 'Promosi',
            'old_position' => 'Staff IT',
            'new_position' => 'Senior IT Developer',
            'date' => '2025-01-01',
            'description' => 'Promosi jabatan awal tahun'
        ]);

        // SP
        \App\Models\Warning::create([
            'user_id' => $user->id,
            'level' => 'SP 1',
            'reason' => 'Terlambat lebih dari 5x dalam sebulan',
            'date' => '2026-03-15',
            'expiry_date' => '2026-09-15'
        ]);

        // Files
        \App\Models\UserFile::create([
            'user_id' => $user->id,
            'name' => 'KTP.pdf',
            'path' => 'files/ktp.pdf',
            'file_type' => 'pdf'
        ]);
        \App\Models\UserFile::create([
            'user_id' => $user->id,
            'name' => 'Ijazah.pdf',
            'path' => 'files/ijazah.pdf',
            'file_type' => 'pdf'
        ]);
    }
}
