<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SavingBookSequenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('saving_book_sequences')->insert([
            'last_number' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
