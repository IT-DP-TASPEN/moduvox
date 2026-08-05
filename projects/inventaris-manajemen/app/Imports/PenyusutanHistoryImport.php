<?php

namespace App\Imports;

use App\Models\Inventaris;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class PenyusutanHistoryImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        // To handle huge rows efficiently, we'll collect them by periode_ym and insert in batches.
        $batches = [];
        $assetMap = Inventaris::select('id', 'rekening', 'kantor_id')->get()->keyBy('rekening');
        
        foreach ($rows as $row) {
            $rawTanggal = $row['susut_tanggal'] ?? null;
            $rekening = $row['susut_rekening'] ?? null;
            $beban = floatval($row['susut_nilai'] ?? 0);
            
            if (!$rawTanggal || !$rekening || $beban <= 0) continue;
            
            // Handle date parsing (could be excel serial or d/m/Y string)
            try {
                if (is_numeric($rawTanggal)) {
                    $tanggal = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rawTanggal);
                } else {
                    $tanggal = Carbon::createFromFormat('d/m/Y', $rawTanggal);
                }
                $periodeYm = $tanggal->format('Ym');
            } catch (\Exception $e) {
                continue; // Skip invalid dates
            }

            if (!isset($assetMap[$rekening])) {
                continue; // Skip if asset not found in DB
            }
            
            $asset = $assetMap[$rekening];

            if (!isset($batches[$periodeYm])) {
                $batches[$periodeYm] = [];
            }
            
            $batches[$periodeYm][] = [
                'inventaris_id' => $asset->id,
                'kantor_id' => $asset->kantor_id,
                'beban_bulan_ini' => $beban,
                // Approximation for historical data
                'nilai_buku_sebelum' => 0, 
                'nilai_buku_sesudah' => 0,
                'akumulasi' => 0, 
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        DB::beginTransaction();
        try {
            foreach ($batches as $periodeYm => $details) {
                // Get or create batch
                $batch = DB::table('penyusutan_batch')->where('periode_ym', $periodeYm)->first();
                if (!$batch) {
                    $batchId = DB::table('penyusutan_batch')->insertGetId([
                        'periode_ym' => $periodeYm,
                        'status' => 'APPROVED',
                        'approved_by' => 1,
                        'approved_at' => now(),
                        'catatan' => 'Migrasi history penyusutan Excel',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } else {
                    $batchId = $batch->id;
                }

                // Add batch_id to details
                $detailsToInsert = array_map(function($item) use ($batchId) {
                    $item['batch_id'] = $batchId;
                    return $item;
                }, $details);
                
                // Chunk insert
                foreach (array_chunk($detailsToInsert, 1000) as $chunk) {
                    DB::table('penyusutan_detail')->insert($chunk);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to import penyusutan history: " . $e->getMessage());
            throw clone $e;
        }
    }
}
