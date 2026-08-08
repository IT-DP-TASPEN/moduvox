<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\BanpotMaster;
use App\Models\MitraMaster;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class BanpotMasterImport implements ToCollection, WithBatchInserts, WithChunkReading
{
    protected string $bulanDapem;
    protected string $nextDueDate;

    public function __construct(string $bulanDapem, ?string $nextDueDate = null)
    {
        $this->bulanDapem = $bulanDapem;
        $this->nextDueDate = $nextDueDate
            ? Carbon::parse($nextDueDate)->endOfMonth()->format('Y-m-d')
            : Carbon::parse($bulanDapem)->endOfMonth()->format('Y-m-d');
    }

    /**
     * Convert Excel date to MySQL date (Y-m-d)
     */
    private function excelDateToMysqlDate($excelDate): ?string
    {
        if (is_string($excelDate) && strtotime($excelDate)) {
            return Carbon::parse($excelDate)->format('Y-m-d');
        }

        if (is_numeric($excelDate)) {
            // Excel numeric date to timestamp
            return Carbon::createFromTimestamp(($excelDate - 25569) * 86400)->format('Y-m-d');
        }

        return null;
    }

    public function collection(Collection $collection)
    {
        // Skip header row
        $rows = array_slice($collection->toArray(), 1);

        $user = Auth::user();
        $userMitraId = $user->mitra_master_id ?? null;
        $mitra = MitraMaster::find($userMitraId);

        $saldoMengendapDefault = $mitra->saldo_mengendap ?? 0;
        $jenisFee = $mitra->jenis_fee_banpot ?? 1;
        $feePersen = $mitra->fee_banpot ?? 0;

        $dataToInsert = [];

        foreach ($rows as $row) {
            if (!isset($row[0])) {
                continue; // skip empty rows
            }

            $gajiPensiun = (float) ($row[8] ?? 0);
            $nominalPotongan = (float) ($row[9] ?? 0);

            // ===== LOGIC BARU SESUAI OBSERVER =====

            // 1. Saldo mengendap (pakai default mitra)
            $saldoMengendap = $saldoMengendapDefault;

            // 2. Gaji mengendap
            $gajiMengendap = $gajiPensiun - $saldoMengendap;

            // 3. Jumlah tertagih = nominal potongan
            $jumlahTertagih = $nominalPotongan;

            // 4. Sisa gaji
            $sisaGaji = $gajiMengendap - $jumlahTertagih;

            // ===== Hitung fee banpot =====
            if ($jenisFee == 1) {
                $feeBanpot = $gajiPensiun * $feePersen / 100;
            } elseif ($jenisFee == 2) {
                $feeBanpot = $nominalPotongan * $feePersen / 100;
            } elseif ($jenisFee == 3) {
                $feeBanpot = $gajiMengendap * $feePersen / 100;
            } else {
                $feeBanpot = $gajiPensiun * $feePersen / 100;
            }


            $namaNasabah = isset($row[1]) ? strtoupper(trim($row[1])) : null;
            $bankTransfer = isset($row[10]) ? strtoupper(trim($row[10])) : null;

            $dataToInsert[] = [
                'rek_tabungan' => $row[0] ?? null,
                'nama_nasabah' => $namaNasabah,
                'notas' => $row[2] ?? null,
                'rek_kredit' => $row[3] ?? null,
                'tenor' => $row[4] ?? null,
                'angsuran_ke' => $row[5] ?? null,
                'tmt_kredit' => $this->excelDateToMysqlDate($row[6] ?? null),
                'tat_kredit' => $this->excelDateToMysqlDate($row[7] ?? null),

                'gaji_pensiun' => $gajiPensiun,
                'nominal_potongan' => $nominalPotongan,

                'saldo_mengendap' => $saldoMengendap,
                'gaji_mengendap' => $gajiMengendap,
                'jumlah_tertagih' => $jumlahTertagih,
                'sisa_gaji' => $sisaGaji,

                'bank_transfer' => $bankTransfer,
                'rek_transfer' => $row[11] ?? null,
                'keterangan' => $row[12] ?? null,
                'bulan_dapem' => $this->bulanDapem,
                'next_due_date' => $this->nextDueDate,

                'fee_banpot' => $feeBanpot,

                'rek_tabungan_valid' => false,
                'notas_valid' => false,
                'dapem_valid' => false,
                'oten_valid' => false,
                'final_validasi_status' => false,

                'status_banpot' => 'request',
                'created_by' => $user->id,
                'created_mitra' => $user->mitraMaster?->nama_mitra ?? '-',
                'jenis_pinbuk' => $user->mitraMaster?->jenis_pinbuk ?? null,

                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // ===== Simpan ke database tanpa trigger event =====
        if (!empty($dataToInsert)) {
            Model::withoutEvents(function () use ($dataToInsert) {
                BanpotMaster::insert($dataToInsert);
            });
        }
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
