<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Division;
use App\Models\Office;
use App\Models\GapokMaster;
use App\Models\HonorariumMaster;
use Illuminate\Support\Facades\File;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->importJson('divisions.json', Division::class, ['code']);
        $this->importJson('offices.json', Office::class, ['code']);
        $this->importJson('gapok_master.json', GapokMaster::class, ['skg', 'grade']);
        $this->importJson('honorarium_master.json', HonorariumMaster::class, ['position_name', 'level']);
    }

    private function importJson($filename, $modelClass, $uniqueKeys)
    {
        $path = database_path('data/' . $filename);
        if (!File::exists($path)) {
            $this->command->warn("File $filename tidak ditemukan di database/data/. Lewati.");
            return;
        }

        $items = json_decode(File::get($path), true);
        foreach ($items as $item) {
            $match = [];
            foreach ($uniqueKeys as $key) {
                $match[$key] = $item[$key];
            }
            $modelClass::updateOrCreate($match, $item);
        }
        $this->command->info("Berhasil mengimpor " . count($items) . " data dari $filename.");
    }
}
