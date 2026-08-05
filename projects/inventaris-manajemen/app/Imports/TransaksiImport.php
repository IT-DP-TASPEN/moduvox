<?php

namespace App\Imports;

use App\Models\Inventaris;
use App\Models\InvMutasi;
use App\Models\MstKantor;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class TransaksiImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Find parent inventaris by rekening or id, fallback to legacy trans_rekening
            $rekening = $row['rekening'] ?? $row['trans_rekening'] ?? null;
            $inventaris = null;
            
            if (!empty($rekening)) {
                $inventaris = Inventaris::withTrashed()->where('rekening', $rekening)->first();
            } elseif (!empty($row['inventaris_id'])) {
                $inventaris = Inventaris::withTrashed()->find($row['inventaris_id']);
            }

            if (!$inventaris) {
                Log::warning('Import Mutasi: Parent Inventaris not found for rekening: ' . $rekening);
                continue;
            }

            // Fallbacks for legacy offices
            $kodeAsal = $row['kode_kantor_asal'] ?? null;
            $kodeTujuan = $row['kode_kantor_tujuan'] ?? $row['trans_kantor'] ?? null;

            $kantorAsalId = $row['kantor_asal_id'] ?? $this->lookupKantorId($kodeAsal);
            $kantorTujuanId = $row['kantor_tujuan_id'] ?? $this->lookupKantorId($kodeTujuan);

            // Handle date parsing (Excel numeric date vs string)
            $rawDate = $row['tanggal_mutasi'] ?? $row['trans_reg_date'] ?? null;
            $tglMutasi = null;
            if (!empty($rawDate)) {
                try {
                    if (is_numeric($rawDate)) {
                        $tglMutasi = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rawDate)->format('Y-m-d');
                    } else {
                        $tglMutasi = date('Y-m-d', strtotime($rawDate));
                    }
                } catch (\Exception $e) {
                    $tglMutasi = null;
                }
            }

            try {
                // Determine jenis mutasi based on trans_kode if available, else default to MUTASI
                $jenisMutasi = strtoupper($row['jenis_mutasi'] ?? 'MUTASI');
                if (isset($row['trans_kode'])) {
                    $kode = (string) $row['trans_kode'];
                    if (in_array($kode, ['80.1', '80.2', '80.3', '80.4'])) {
                        // 80.x = Angsuran / Koreksi OB
                        $jenisMutasi = 'ANGSURAN';
                    } elseif (in_array($kode, ['85', '86'])) {
                        // 85 = Pembelian Tunai, 86 = Pembelian OB dari Uang Muka
                        $jenisMutasi = 'PEMBELIAN';
                    } elseif (in_array($kode, ['86.1'])) {
                        // 86.1 = Hadiah
                        $jenisMutasi = 'HADIAH';
                    } elseif (in_array($kode, ['86.2', '86.3'])) {
                        // 86.2 = Revaluasi Aktiva (Tambah), 86.3 = Revaluasi Aktiva (Kurang)
                        $jenisMutasi = 'REVALUASI';
                    } elseif (in_array($kode, ['87', '88'])) {
                        // 87 = Penjualan Tunai, 88 = Penjualan ke COA
                        $jenisMutasi = 'PENJUALAN';
                    } elseif ($kode === '89.1') {
                        // 89.1 = Hapus Buku
                        $jenisMutasi = 'PENGHAPUSAN';
                    } elseif ($kode === '89.2') {
                        // 89.2 = Mutasi Kantor
                        $jenisMutasi = 'MUTASI';
                    } elseif (str_contains(strtolower($row['trans_uraian'] ?? ''), 'hapus')) {
                        $jenisMutasi = 'PENGHAPUSAN';
                    }
                }

                $keterangan = $row['keterangan'] ?? $row['trans_uraian'] ?? null;

                InvMutasi::create([
                    'inventaris_id' => $inventaris->id,
                    'jenis_mutasi' => $jenisMutasi,
                    'tgl_mutasi' => $tglMutasi,
                    'kantor_asal_id' => $kantorAsalId,
                    'kantor_tujuan_id' => $kantorTujuanId,
                    'keterangan' => $keterangan,
                    'status' => strtoupper($row['status'] ?? 'APPROVED'),
                    'user_id' => auth()->id() ?? 1,
                    'approval_user_id' => auth()->id() ?? 1,
                ]);

                // Update lokasi inventaris jika MUTASI dan APPROVED
                if (strtoupper($row['status'] ?? 'APPROVED') === 'APPROVED') {
                    if ($jenisMutasi === 'MUTASI' && $kantorTujuanId) {
                        $inventaris->update([
                            'kantor_id' => $kantorTujuanId
                        ]);
                    } elseif ($jenisMutasi === 'PENJUALAN' || $jenisMutasi === 'PENGHAPUSAN') {
                        $inventaris->update([
                            'status' => \App\Enums\AssetStatus::DIHAPUS->value
                        ]);
                    }
                }

            } catch (\Exception $e) {
                Log::error('Import Mutasi Failed Row: ' . json_encode($row) . ' Error: ' . $e->getMessage());
            }
        }
    }

    private function lookupKantorId($kode)
    {
        if (!$kode) return null;
        $record = MstKantor::where('kode', $kode)->first();
        return $record ? $record->id : null;
    }
}
