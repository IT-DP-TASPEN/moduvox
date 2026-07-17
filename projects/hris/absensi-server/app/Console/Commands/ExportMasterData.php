<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Division;
use App\Models\Office;
use App\Models\GapokMaster;
use App\Models\HonorariumMaster;
use Illuminate\Support\Facades\File;

class ExportMasterData extends Command
{
    protected $signature = 'app:export-master';
    protected $description = 'Export Master Data (Divisi, Kantor, Gapok, Honor) to JSON for seeding';

    public function handle()
    {
        $dataDir = database_path('data');
        if (!File::exists($dataDir)) {
            File::makeDirectory($dataDir);
        }

        // Export Divisions
        File::put($dataDir . '/divisions.json', json_encode(Division::all(['code', 'name']), JSON_PRETTY_PRINT));
        $this->info('Divisions exported.');

        // Export Offices
        File::put($dataDir . '/offices.json', json_encode(Office::all(['code', 'name', 'address', 'latitude', 'longitude', 'radius']), JSON_PRETTY_PRINT));
        $this->info('Offices exported.');

        // Export Gapok
        File::put($dataDir . '/gapok_master.json', json_encode(GapokMaster::all(['skg', 'grade', 'amount']), JSON_PRETTY_PRINT));
        $this->info('Gapok Master exported.');

        // Export Honorarium
        File::put($dataDir . '/honorarium_master.json', json_encode(HonorariumMaster::all(['position_name', 'level', 'amount']), JSON_PRETTY_PRINT));
        $this->info('Honorarium Master exported.');

        $this->info('All Master Data exported to database/data/');
    }
}
