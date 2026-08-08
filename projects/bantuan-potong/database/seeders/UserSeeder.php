<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Buat admin global
        User::factory()->create([
            'name' => 'Admin Utama',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'mitra_master_id' => null,
        ]);

        // Buat 4 user untuk masing-masing mitra
        $mitraIds = [1, 2]; // dari MitraMasterSeeder

        foreach ($mitraIds as $mitraId) {
            for ($i = 1; $i <= 4; $i++) {
                User::factory()->create([
                    'name' => "User Mitra{$mitraId} - {$i}",
                    'email' => "mitra{$mitraId}_user{$i}@example.com",
                    'password' => Hash::make('password'),
                    'mitra_master_id' => $mitraId,
                ]);
            }
        }
    }
}
