<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ShieldSeeder::class,
            MitraMasterSeeder::class,
            MitraBranchSeeder::class,
            UserSeeder::class,
            MasterProvinceSeeder::class,
            MasterDati2Seeder::class,
            SavingBookSequenceSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
