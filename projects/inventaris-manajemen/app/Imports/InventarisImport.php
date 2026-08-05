<?php

namespace App\Imports;

use App\Models\Inventaris;
use App\Models\MstKantor;
use App\Models\MstGolongan;
use App\Models\MstJenis;
use App\Models\MstRuangan;
use App\Models\MstLokasi;
use App\Models\MstSumberDana;
use App\Enums\AssetStatus;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class InventarisImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $namaAset = $row['nama_aset'] ?? $row['inv_nama'] ?? null;
            if (!$namaAset) {
                throw new \Exception("Kolom 'nama_aset' atau 'inv_nama' tidak ditemukan. Pastikan format Excel menggunakan template yang benar dengan header di baris pertama.");
            }
            
            // Abaikan baris "Total" atau "Subtotal" dari laporan
            if (stripos(trim($namaAset), 'total') === 0 || stripos(trim($namaAset), 'subtotal') === 0) {
                continue;
            }

            // Coba lookup berdasarkan kode jika ada, jika tidak pakai id langsung
            $kodeKantor = $row['kode_kantor'] ?? $row['inv_kantor'] ?? null;
            $kodeGolongan = $row['kode_golongan'] ?? $row['inv_golongan'] ?? null;
            $kodeJenis = $row['kode_jenis'] ?? $row['inv_jenis'] ?? null;
            $kodeRuangan = $row['kode_ruangan'] ?? $row['inv_ruang'] ?? null;
            $kodeSumber = $row['kode_sumber'] ?? $row['inv_sumber'] ?? null;
            $kodeLokasi = $row['kode_lokasi'] ?? null;

            $kantorId = $row['kantor_id'] ?? $this->lookupId(MstKantor::class, $kodeKantor);
            $golonganId = $row['golongan_id'] ?? $this->lookupId(MstGolongan::class, $kodeGolongan);
            $jenisId = $row['jenis_id'] ?? $this->lookupId(MstJenis::class, $kodeJenis);
            $ruanganId = $row['ruangan_id'] ?? $this->lookupId(MstRuangan::class, $kodeRuangan);
            $lokasiId = $row['lokasi_id'] ?? $this->lookupId(MstLokasi::class, $kodeLokasi);
            $sumberId = $row['sumber_id'] ?? $this->lookupId(MstSumberDana::class, $kodeSumber);

            $rawTgl = $row['tgl_perolehan'] ?? $row['inv_peroleh_tanggal'] ?? null;
            if (is_numeric($rawTgl)) {
                $tglPerolehan = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rawTgl)->format('Y-m-d');
            } elseif (!empty($rawTgl)) {
                $tglPerolehan = Carbon::parse($rawTgl)->format('Y-m-d');
            } else {
                $tglPerolehan = Carbon::now()->format('Y-m-d');
            }
            
            $hargaPerolehan = floatval($row['harga_perolehan'] ?? $row['inv_peroleh_nilai'] ?? 0);
            $nilaiBuku = isset($row['nilai_buku']) ? floatval($row['nilai_buku']) : (isset($row['inv_nilai_buku']) ? floatval($row['inv_nilai_buku']) : $hargaPerolehan);
            $akumulasiPenyusutan = isset($row['akumulasi_penyusutan']) ? floatval($row['akumulasi_penyusutan']) : (isset($row['inv_susut_akumulasi']) ? floatval($row['inv_susut_akumulasi']) : 0);

            // Generate nomor rekening
            $rawRekening = $row['rekening'] ?? $row['inv_rekening'] ?? null;
            $rekening = !empty($rawRekening) ? $rawRekening : Inventaris::generateNomorInventaris([
                'kantor_id' => $kantorId,
                'golongan_id' => $golonganId,
                'jenis_id' => $jenisId,
                'tgl_perolehan' => $tglPerolehan,
            ]);

            // Hitung umur bulan
            $tglPerolehanCarbon = Carbon::parse($tglPerolehan)->startOfMonth();
            $now = Carbon::now()->startOfMonth();
            $umurBulan = isset($row['umur_bulan']) ? intval($row['umur_bulan']) : (isset($row['inv_umur']) ? intval($row['inv_umur']) : max(0, $tglPerolehanCarbon->diffInMonths($now)));

            $statusValue = $row['status'] ?? $row['inv_status'] ?? AssetStatus::AKTIF->value;
            if ($statusValue === 1 || $statusValue === '1') $statusValue = AssetStatus::AKTIF->value;
            if (!in_array($statusValue, array_column(AssetStatus::cases(), 'value'))) {
                $statusValue = AssetStatus::AKTIF->value; 
            }


            try {
                $inventaris = Inventaris::updateOrCreate(
                    ['rekening' => $rekening],
                    [
                        'kantor_id' => $kantorId,
                        'golongan_id' => $golonganId,
                        'jenis_id' => $jenisId,
                        'ruangan_id' => $ruanganId,
                        'lokasi_id' => $lokasiId,
                        'sumber_id' => $sumberId,
                        'nama_aset' => $namaAset,
                        'merk' => $row['merk'] ?? null,
                        'no_seri' => $row['no_seri'] ?? null,
                        'tgl_perolehan' => $tglPerolehan,
                        'harga_perolehan' => $hargaPerolehan,
                        'nilai_buku' => $nilaiBuku,
                        'akumulasi_penyusutan' => $akumulasiPenyusutan,
                        'umur_bulan' => $umurBulan,
                        'status' => $statusValue,
                        'keterangan' => $row['keterangan'] ?? $row['inv_keterangan'] ?? null,
                    ]
                );

                if ($inventaris->wasRecentlyCreated) {
                    \App\Models\InvMutasi::create([
                        'inventaris_id' => $inventaris->id,
                        'jenis_mutasi' => 'BARU',
                        'tgl_mutasi' => $tglPerolehan,
                        'kantor_asal_id' => null,
                        'kantor_tujuan_id' => $kantorId,
                        'keterangan' => 'Pencatatan aset via Import Excel',
                        'status' => 'APPROVED',
                        'user_id' => auth()->id() ?? 1
                    ]);
                }

            } catch (\Exception $e) {
                Log::error('Import Inventaris Failed Row: ' . json_encode($row) . ' Error: ' . $e->getMessage());
                throw new \Exception("Gagal mengimpor baris data: " . $namaAset . " - " . $e->getMessage());
            }
        }
    }

    private function lookupId($model, $kode)
    {
        if ($kode === null || $kode === '') return null;
        
        $kodeStr = str_pad($kode, 2, '0', STR_PAD_LEFT);
        $record = $model::where('kode', $kode)->orWhere('kode', $kodeStr)->first();
        return $record ? $record->id : null;
    }
}
