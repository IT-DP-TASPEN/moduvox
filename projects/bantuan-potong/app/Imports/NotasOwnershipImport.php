<?php

namespace App\Imports;

use App\Models\NotasOwnership;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class NotasOwnershipImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $rekTabungan = $row['rek_tabungan'] ?? null;

            if (NotasOwnership::where('notas', $row['notas'])->exists()) {
                continue;
            }

            NotasOwnership::create([
                'mitra_master_id' => $row['mitra_master_id'] ?? null,
                'notas' => $row['notas'] ?? null,
                'nama_nasabah' => $row['nama_nasabah'] ?? null,
                'rek_tabungan' => $rekTabungan, // rek_replace otomatis diisi oleh model
            ]);
        }
    }
}
