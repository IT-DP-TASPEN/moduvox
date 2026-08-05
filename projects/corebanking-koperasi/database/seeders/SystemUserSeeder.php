<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SystemUserSeeder extends Seeder
{
    public function run(): void
    {
        if (!DB::table('users')->where('username', 'system')->exists()) {
            DB::table('users')->insert([
                'name'       => 'SYSTEM',
                'username'   => 'system',
                'email'      => 'system@sirara.id',
                'password'   => Hash::make('system-sirara-' . bin2hex(random_bytes(8))),
                'branch_id'  => 1,
                'company_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
