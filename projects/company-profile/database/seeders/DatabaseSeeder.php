<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $adminEmail = env('ADMIN_EMAIL', 'admin@example.com');
        $adminPassword = env('ADMIN_PASSWORD', 'change-me');

        $admin = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Administrator',
                'password' => Hash::make($adminPassword),
            ]
        );
        $admin->forceFill(['is_admin' => true])->save();

        // Run the Data Seeders
        $this->call([
            RealContentSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
