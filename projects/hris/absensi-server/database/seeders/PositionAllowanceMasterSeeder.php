<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PositionAllowanceMaster;
use App\Models\User;

class PositionAllowanceMasterSeeder extends Seeder
{
    public function run()
    {
        // Ambil semua jabatan unik dari tabel users
        $positions = User::whereNotNull('title')
            ->distinct()
            ->pluck('title');

        foreach ($positions as $position) {
            $amount = $this->getDummyAmount($position);
            
            PositionAllowanceMaster::updateOrCreate(
                ['position_name' => $position],
                ['amount' => $amount]
            );
        }
    }

    private function getDummyAmount($title)
    {
        $title = strtolower($title);
        
        if (str_contains($title, 'direktur') || str_contains($title, 'dirut')) {
            return 10000000;
        } elseif (str_contains($title, 'kepala divisi') || str_contains($title, 'kadiv')) {
            return 5000000;
        } elseif (str_contains($title, 'kepala kantor') || str_contains($title, 'kakan')) {
            return 3500000;
        } elseif (str_contains($title, 'supervisor') || str_contains($title, 'kabag')) {
            return 2000000;
        } elseif (str_contains($title, 'senior') || str_contains($title, 'ahli')) {
            return 1250000;
        } elseif (str_contains($title, 'junior') || str_contains($title, 'staf') || str_contains($title, 'staff')) {
            return 750000;
        }
        
        // Default nominal jika tidak terdeteksi keyword
        return 500000;
    }
}
