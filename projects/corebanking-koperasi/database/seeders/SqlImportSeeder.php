<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SqlImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $txtPath = database_path('txt');

        if (!File::exists($txtPath)) {
            $this->command->info("Directory database/txt does not exist.");
            return;
        }

        $files = File::files($txtPath);
        sort($files);

        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        foreach ($files as $file) {
            if ($file->getExtension() === 'txt') {
                $filename = $file->getFilename();
                $this->command->info("Processing: " . $filename);

                $tableName = $this->getTableName($filename);
                if (!$tableName) {
                    $this->command->warn("No table mapping found for $filename, skipping.");
                    continue;
                }

                $this->importTsv($file->getPathname(), $tableName);
            }
        }

        $this->syncGeographicParents();

        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=ON;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    protected function getTableName($filename): ?string
    {
        if (str_contains($filename, 'province')) return 'provinces';
        if (str_contains($filename, 'city') || str_contains($filename, 'cities')) return 'cities';
        if (str_contains($filename, 'subdistrict')) return 'subdistricts';
        if (str_contains($filename, 'district')) return 'districts';

        return null;
    }

    protected function mapHeaders(array $headers, string $table): array
    {
        $map = [
            'provinces' => ['prov_name' => 'nama'],
            'cities' => ['city_id' => 'dati2', 'city_name' => 'nama', 'prov_id' => 'province_id'],
            'districts' => ['dis_name' => 'nama', 'city_id' => 'regency_id'],
            'subdistricts' => ['subdis_name' => 'nama', 'dis_id' => 'district_id'],
        ];

        return array_map(function ($h) use ($map, $table) {
            return $map[$table][$h] ?? $h;
        }, $headers);
    }

    protected function importTsv($path, $table): void
    {
        $handle = fopen($path, 'r');
        if (!$handle) return;

        // Get headers
        $headers = fgetcsv($handle, 0, "\t");
        if (!$headers) {
            fclose($handle);
            return;
        }

        // Clean headers (remove quotes and whitespace)
        $rawHeaders = array_map(fn($h) => trim($h, '"' . " \t\n\r\0\x0B"), $headers);
        $headers = $this->mapHeaders($rawHeaders, $table);

        $batchSize = 1000;
        $batch = [];
        $count = 0;

        while (($row = fgetcsv($handle, 0, "\t")) !== false) {
            if (count($row) !== count($headers)) {
                // Pad or trim if mismatch
                if (count($row) < count($headers)) {
                    $row = array_pad($row, count($headers), null);
                } else {
                    $row = array_slice($row, 0, count($headers));
                }
            }

            $data = array_combine($headers, array_map(fn($v) => $v === '' ? null : trim($v, '"'), $row));

            // Only keep columns that actually exist in the table mapped
            $allowedColumns = [
                'provinces' => ['id', 'nama', 'created_at', 'updated_at'],
                'cities' => ['id', 'dati2', 'nama', 'province_id', 'created_at', 'updated_at'],
                'districts' => ['id', 'nama', 'regency_id', 'province_id', 'created_at', 'updated_at'],
                'subdistricts' => ['id', 'nama', 'district_id', 'regency_id', 'province_id', 'created_at', 'updated_at'],
            ];

            $filteredData = [];
            foreach ($data as $key => $value) {
                if (in_array($key, $allowedColumns[$table])) {
                    $filteredData[$key] = $value;
                }
            }

            // Kept ID because geographic tables use specific IDs for foreign keys

            // Add timestamps if missing
            if (!isset($filteredData['created_at'])) {
                $filteredData['created_at'] = now();
            }
            if (!isset($filteredData['updated_at'])) {
                $filteredData['updated_at'] = now();
            }

            // For geographic tables, populate missing parent IDs before insert
            if ($table === 'districts') {
                static $cityMap = null;
                if ($cityMap === null) {
                    $cityMap = DB::table('cities')->pluck('province_id', 'id')->toArray();
                }
                $filteredData['province_id'] = $cityMap[$filteredData['regency_id']] ?? 1;
            }
            if ($table === 'subdistricts') {
                static $districtMap = null;
                if ($districtMap === null) {
                    $districtMap = DB::table('districts')->get(['id', 'regency_id', 'province_id'])->keyBy('id')->toArray();
                }
                $districtId = $filteredData['district_id'];
                if (isset($districtMap[$districtId])) {
                    $filteredData['regency_id'] = $districtMap[$districtId]->regency_id;
                    $filteredData['province_id'] = $districtMap[$districtId]->province_id;
                } else {
                    $filteredData['regency_id'] = 1;
                    $filteredData['province_id'] = 1;
                }
            }

            $batch[] = $filteredData;
            $count++;

            if (count($batch) >= $batchSize) {
                DB::table($table)->insertOrIgnore($batch);
                $batch = [];
                $this->command->comment("Processed $count records for $table...");
            }
        }

        if (!empty($batch)) {
            DB::table($table)->insertOrIgnore($batch);
        }

        fclose($handle);
        $this->command->info("Finished importing $count records into $table.");
    }

    private function syncGeographicParents(): void
    {
        // Handled in PHP during insert, no SQL UPDATE needed for SQLite compatibility
    }
}
