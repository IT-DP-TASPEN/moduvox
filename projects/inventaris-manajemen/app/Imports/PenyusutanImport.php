<?php

namespace App\Imports;

use App\Models\Inventaris;
use App\Models\PenyusutanBatch;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;

class PenyusutanImport implements ToCollection, WithHeadingRow, WithChunkReading, ShouldQueue
{
    public function collection(Collection $rows)
    {
        $batches = [];

        foreach ($rows as $row) {
            $rekening = $row['susut_rekening'] ?? null;
            if (!$rekening) continue;

            $susutTanggal = $row['susut_tanggal'] ?? null;
            if (is_numeric($susutTanggal)) {
                $tanggal = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($susutTanggal)->format('Y-m-d');
            } elseif (!empty($susutTanggal)) {
                $tanggal = Carbon::parse($susutTanggal)->format('Y-m-d');
            } else {
                continue;
            }

            $periodeYm = Carbon::parse($tanggal)->format('Ym');
            
            $inventaris = DB::table('inventaris')->where('rekening', $rekening)->first();
            
            if (!$inventaris) {
                continue;
            }

            if (!isset($batches[$periodeYm])) {
                $batch = PenyusutanBatch::firstOrCreate(
                    ['periode_ym' => $periodeYm],
                    ['status' => 'CLOSED', 'created_by' => auth()->id() ?? 1]
                );
                $batches[$periodeYm] = $batch->id;
            }
            $batchId = $batches[$periodeYm];

            $beban = floatval($row['susut_nilai'] ?? 0);

            try {
                DB::table('penyusutan_detail')->insert([
                    'batch_id' => $batchId,
                    'inventaris_id' => $inventaris->id,
                    'kantor_id' => $inventaris->kantor_id,
                    'beban_bulan_ini' => $beban,
                    'nilai_buku_sebelum' => 0,
                    'nilai_buku_sesudah' => 0,
                    'akumulasi' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } catch (\Exception $e) {
                Log::error('Import Penyusutan Failed: ' . json_encode($row) . ' Error: ' . $e->getMessage());
            }
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
