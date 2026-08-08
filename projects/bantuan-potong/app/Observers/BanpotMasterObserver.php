<?php

namespace App\Observers;

use App\Models\BanpotMaster;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BanpotMasterObserver
{
    public function creating(BanpotMaster $banpot)
    {
        $this->calculateBanpotFields($banpot, true);
    }

    public function updating(BanpotMaster $banpot)
    {
        // hanya creator yang boleh trigger kalkulasi ulang
        if (auth()->id() !== $banpot->created_by) {
            return;
        }

        // SELALU hitung ulang saat edit (lebih aman)
        $this->calculateBanpotFields($banpot, false);
    }

    private function calculateBanpotFields(BanpotMaster $banpot, bool $isCreating)
    {
        $user  = Auth::user();
        $mitra = $user?->mitraMaster;

        /**
         * ==========================
         * NEXT DUE DATE (AUTO)
         * ==========================
         * - jika kosong → set akhir bulan
         * - jika bulan_dapem diubah → recompute
         */
        // if (
        //     empty($banpot->next_due_date) ||
        //     $banpot->isDirty('bulan_dapem')
        // ) {
        //     $banpot->next_due_date = !empty($banpot->bulan_dapem)
        //         ? Carbon::parse($banpot->bulan_dapem)->endOfMonth()->format('Y-m-d')
        //         : now()->endOfMonth()->format('Y-m-d');
        // }

        $banpot->next_due_date = !empty($banpot->bulan_dapem)
            ? Carbon::createFromFormat('Ym01', $banpot->bulan_dapem)
            ->endOfMonth()
            ->format('Y-m-d')
            : now()->endOfMonth()->format('Y-m-d');


        // normalisasi bulan_dapem → tanggal 01
        // if (!empty($banpot->bulan_dapem)) {
        //     $banpot->bulan_dapem = Carbon::parse($banpot->bulan_dapem)
        //         ->startOfMonth()
        //         ->format('Y-m-d');
        // }

        if (!empty($banpot->bulan_dapem)) {
            $banpot->bulan_dapem = preg_match('/^\d{8}$/', $banpot->bulan_dapem)
                ? $banpot->bulan_dapem          // sudah Ym01 → biarkan
                : Carbon::parse($banpot->bulan_dapem)->format('Ym01');
        }


        /**
         * ==========================
         * RESET FIELD PERHITUNGAN
         * ==========================
         */
        $banpot->saldo_mengendap = null;
        $banpot->gaji_mengendap  = null;
        $banpot->jumlah_tertagih = null;
        $banpot->sisa_gaji       = null;
        $banpot->fee_banpot      = null;
        $banpot->rek_tabungan_valid = false;
        $banpot->notas_valid        = false;
        $banpot->dapem_valid        = false;
        $banpot->oten_valid         = false;
        $banpot->final_validasi_status = false;

        // uppercase nama
        if (!empty($banpot->nama_nasabah)) {
            $banpot->nama_nasabah = strtoupper(trim($banpot->nama_nasabah));
        }

        /**
         * ==========================
         * PARAMETER MITRA
         * ==========================
         */
        $saldoMengendapDefault = (float) ($mitra->saldo_mengendap ?? 0);
        $jenisFee              = $mitra->jenis_fee_banpot ?? 1; // 1=gaji, 2=potongan
        $feePersen             = (float) ($mitra->fee_banpot ?? 0);

        /**
         * ==========================
         * NILAI UTAMA
         * ==========================
         */
        $gajiPensiun     = (float) ($banpot->gaji_pensiun ?? 0);
        $nominalPotongan = (float) ($banpot->nominal_potongan ?? 0);

        /**
         * ==========================
         * LOGIC PERHITUNGAN
         * ==========================
         */
        $saldoMengendap = $saldoMengendapDefault;

        $gajiMengendap   = $gajiPensiun - $saldoMengendap;
        $jumlahTertagih  = $nominalPotongan;
        $sisaGaji        = $gajiMengendap - $jumlahTertagih;

        // fee banpot
        $feeBanpot = $jenisFee == 1
            ? ($gajiPensiun * $feePersen / 100)
            : ($jenisFee == 2
                ? ($nominalPotongan * $feePersen / 100)
                : ($gajiMengendap * $feePersen / 100));

        /**
         * ==========================
         * SET HASIL
         * ==========================
         */
        $banpot->saldo_mengendap = $saldoMengendap;
        $banpot->gaji_mengendap  = $gajiMengendap;
        $banpot->jumlah_tertagih = $jumlahTertagih;
        $banpot->sisa_gaji       = $sisaGaji;
        $banpot->fee_banpot      = $feeBanpot;

        /**
         * ==========================
         * METADATA SAAT CREATE
         * ==========================
         */
        if ($isCreating && $user) {
            $banpot->created_by   = $user->id;
            $banpot->created_mitra = $mitra->nama_mitra ?? '-';
            $banpot->jenis_pinbuk  = $mitra->jenis_pinbuk ?? null;
        }
    }
}
