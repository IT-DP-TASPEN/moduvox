<?php

namespace App\Console\Commands;

use App\Models\NotasOwnership;
use Illuminate\Console\Command;
use App\Models\BanpotMaster;
use App\Models\DapemMaster;
use Illuminate\Support\Facades\DB;

class ValidateBanpotMasters extends Command
{
    protected $signature = 'banpot:validate';
    protected $description = 'Step-by-step validation for Banpot Masters (notas, rek_tabungan, dapem, otentikasi) with boolean final_validasi_status';

    public function handle()
    {
        $this->info('Mulai validasi Banpot Masters...');

        $banpots = BanpotMaster::query()
            ->where('status_banpot', 'request')
            ->get();

        foreach ($banpots as $banpot) {
            // Reset field agar retry selalu fresh
            $banpot->update([
                'notas_valid' => false,
                'rek_tabungan_valid' => false,
                'dapem_valid' => false,
                'oten_valid' => false,
                'final_validasi_status' => false,
                'keterangan_2' => '',
            ]);

            $reasons = [];
            $isValid = true;
            $userMitraId = $banpot->created_by ? $banpot->creator->mitra_master_id : null;

            $notasExists = NotasOwnership::where('notas', $banpot->notas)->exists();

            if (!$notasExists) {
                $isValid = false;
                $banpot->notas_valid = false;
                $reasons[] = 'Notas belum terdaftar.';
                $banpot->final_validasi_status = false;
                $banpot->keterangan_2 = implode(' ', $reasons);
                $banpot->save();
                $this->info("Banpot ID {$banpot->id}: gagal di validasi karena Notas belum terdaftar");
                continue; // hentikan proses berikutnya
            }
            // =====================
            // STEP 1: Validasi NOTAS
            // =====================
            $ownership = DB::table('notas_ownerships')
                ->where('notas', $banpot->notas)
                ->where('mitra_master_id', $userMitraId)
                ->first();

            if (!$ownership) {
                $isValid = false;
                $banpot->notas_valid = false;
                $reasons[] = 'Notas tidak sesuai kepemilikan mitra.';
                // Berhenti di sini, tidak lanjut step berikut
                $banpot->final_validasi_status = false;
                $banpot->keterangan_2 = implode(' ', $reasons);
                $banpot->save();
                $this->info("Banpot ID {$banpot->id}: gagal di validasi Notas");
                continue;
            }

            $banpot->notas_valid = true;
            $reasons[] = 'Notas sesuai kepemilikan.';

            // =====================
            // STEP 2: Validasi Rekening Tabungan
            // =====================
            if ($banpot->rek_tabungan === $ownership->rek_tabungan) {
                $banpot->rek_tabungan_valid = true;
                $reasons[] = 'Rekening tabungan sesuai.';
            } else {
                $isValid = false;
                $banpot->rek_tabungan_valid = false;
                $reasons[] = 'Rekening tabungan tidak sesuai kepemilikan.';
                $banpot->final_validasi_status = false;
                $banpot->keterangan_2 = implode(' ', $reasons);
                $banpot->save();
                $this->info("Banpot ID {$banpot->id}: gagal di validasi Rekening Tabungan");
                continue;
            }

            // =====================
            // STEP 3: Validasi DAPEM
            // =====================
            $dapem = DapemMaster::on('mysql_prod')
                ->where('notas', $banpot->notas)
                ->where('bulan_dapem', $banpot->bulan_dapem)
                ->orderByDesc('id')
                ->first();

            if (!$dapem) {
                $isValid = false;
                $banpot->dapem_valid = false;
                $reasons[] = 'Data Dapem tidak ditemukan di sistem.';
                $banpot->final_validasi_status = false;
                $banpot->keterangan_2 = implode(' ', $reasons);
                $banpot->save();
                $this->info("Banpot ID {$banpot->id}: gagal di validasi Dapem");
                continue;
            }

            $banpot->dapem_valid = true;
            $reasons[] = 'Data Dapem ditemukan.';

            // =====================
            // STEP 4: Validasi Otentikasi
            // =====================
            $validOtentikasiValues = [13, 14];
            if (in_array($dapem->code2, $validOtentikasiValues)) {
                $banpot->oten_valid = true;
                $reasons[] = 'Otentikasi valid (Kode Otentifikasi: ' . $dapem->code2 . ').';
            } else {
                $isValid = false;
                $banpot->oten_valid = false;
                $reasons[] = 'Belum otentifikasi (Kode Otentifikasi: ' . $dapem->code2 . ').';
            }

            // =====================
            // FINAL
            // =====================
            $banpot->final_validasi_status = $isValid;
            $banpot->keterangan_2 = implode(' ', $reasons);
            $banpot->save();

            $statusLabel = $isValid ? 'VALID' : 'INVALID';
            $this->info("Banpot ID {$banpot->id}: {$statusLabel}");
        }

        $this->info('Validasi Banpot Masters selesai.');
        return 0;
    }
}
