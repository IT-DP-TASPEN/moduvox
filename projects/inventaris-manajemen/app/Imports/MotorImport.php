<?php

namespace App\Imports;

use App\Models\Inventaris;
use App\Models\InvMotor;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class MotorImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Find parent inventaris by rekening (support legacy column name)
            $rekening = $row['rekening'] ?? $row['rinci_rekening'] ?? null;
            $inventaris = null;

            if (!empty($rekening)) {
                $inventaris = Inventaris::where('rekening', $rekening)->first();
            } elseif (!empty($row['inventaris_id'])) {
                $inventaris = Inventaris::find($row['inventaris_id']);
            }

            if (!$inventaris) {
                Log::warning('Import Motor: Parent Inventaris not found for rekening: ' . $rekening);
                continue;
            }

            // Parse tgl_pajak / nopol_expire — handle legacy dates like 0000-00-00
            $tglPajak = null;
            $rawTglPajak = $row['tgl_pajak'] ?? $row['rinci_nopol_expire'] ?? null;
            if (!empty($rawTglPajak) && $rawTglPajak !== '0000-00-00') {
                try {
                    if (is_numeric($rawTglPajak)) {
                        $tglPajak = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rawTglPajak)->format('Y-m-d');
                    } else {
                        $tglPajak = date('Y-m-d', strtotime($rawTglPajak));
                        if ($tglPajak === '1970-01-01') $tglPajak = null;
                    }
                } catch (\Exception $e) {
                    $tglPajak = null;
                }
            }

            try {
                InvMotor::updateOrCreate(
                    ['inventaris_id' => $inventaris->id],
                    [
                        'no_polisi'        => $row['no_polisi'] ?? $row['rinci_no_polisi'] ?? null,
                        'no_bpkb'          => $row['no_bpkb'] ?? $row['rinci_no_bpkb'] ?? null,
                        'no_mesin'         => $row['no_mesin'] ?? $row['rinci_no_mesin'] ?? null,
                        'no_rangka'        => $row['no_rangka'] ?? $row['rinci_no_rangka'] ?? null,
                        'tahun_pembuatan'  => $row['tahun_pembuatan'] ?? $row['rinci_thn_buat'] ?? null,
                        'tahun_rakit'      => $row['tahun_rakit'] ?? $row['rinci_thn_rakit'] ?? null,
                        'warna'            => $row['warna'] ?? $row['rinci_warna'] ?? null,
                        'tgl_pajak'        => $tglPajak,
                        'atas_nama'        => $row['atas_nama'] ?? $row['rinci_atasnama'] ?? null,
                    ]
                );
            } catch (\Exception $e) {
                Log::error('Import Motor Failed Row: ' . json_encode($row) . ' Error: ' . $e->getMessage());
            }
        }
    }
}
