<?php

namespace App\Imports;

use App\Models\Inventaris;
use App\Models\InvTanah;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class TanahImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Find parent inventaris by rekening or id
            $inventaris = null;
            if (!empty($row['rekening'])) {
                $inventaris = Inventaris::where('rekening', $row['rekening'])->first();
            } elseif (!empty($row['inventaris_id'])) {
                $inventaris = Inventaris::find($row['inventaris_id']);
            }

            if (!$inventaris) {
                Log::warning('Import Tanah: Parent Inventaris not found for row: ' . json_encode($row));
                continue;
            }

            try {
                InvTanah::updateOrCreate(
                    ['inventaris_id' => $inventaris->id],
                    [
                        'no_shm' => $row['no_shm'] ?? null,
                        'no_shgb' => $row['no_shgb'] ?? null,
                        'tanggal_shm' => !empty($row['tanggal_shm']) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['tanggal_shm'])->format('Y-m-d') : null,
                        'surat_ukur' => $row['surat_ukur'] ?? null,
                        'luas_tanah' => $row['luas_tanah'] ?? null,
                        'luas_bangunan' => $row['luas_bangunan'] ?? null,
                        'atas_nama' => $row['atas_nama'] ?? null,
                    ]
                );
            } catch (\Exception $e) {
                Log::error('Import Tanah Failed Row: ' . json_encode($row) . ' Error: ' . $e->getMessage());
            }
        }
    }
}
