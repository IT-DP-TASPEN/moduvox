<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use App\Models\PermintaanFlaggingMutasiTif;

class PermintaanFlaggingMutasiObserver
{
    public function creating(PermintaanFlaggingMutasiTif $flaggingmutasi)
    {
        $this->calculateFlaggingmutasiFields($flaggingmutasi, true);
    }

    public function updating(PermintaanFlaggingMutasiTif $flaggingmutasi)
    {
        if (auth()->id() !== $flaggingmutasi->created_by) {
            return;
        }
        $this->calculateFlaggingmutasiFields($flaggingmutasi, false);
    }

    private function calculateFlaggingmutasiFields(PermintaanFlaggingMutasiTif $flaggingmutasi, bool $isCreating)
    {
        $user = Auth::user();
        $mitra = $user?->mitraMaster;

        // ======== Reset field sebelum kalkulasi ulang ========
        $flaggingmutasi->fee = null;
        $flaggingmutasi->fee_checking = null;

        // ======== Konversi nama nasabah jadi UPPERCASE ========
        if (!empty($flaggingmutasi->nama_nasabah)) {
            $flaggingmutasi->nama_nasabah = strtoupper(trim($flaggingmutasi->nama_nasabah));
        }

        if (!empty($flaggingmutasi->alamat)) {
            $flaggingmutasi->alamat = strtoupper(trim($flaggingmutasi->alamat));
        }

        if (!empty($flaggingmutasi->tempat_lahir)) {
            $flaggingmutasi->tempat_lahir = strtoupper(trim($flaggingmutasi->tempat_lahir));
        }

        // ======== Ambil parameter dari mitra (jika ada) ========
        if ($mitra && isset($mitra->biaya_flagging_mutasi_tif)) {
            $flaggingmutasi->fee = $mitra->biaya_flagging_mutasi_tif;
        }

        if ($mitra && isset($mitra->biaya_checking)) {
            $flaggingmutasi->fee_checking = $mitra->biaya_checking;
        }

        // ======== Set created_by & created_mitra jika creating ========
        if ($isCreating && $user) {
            $flaggingmutasi->created_by = $user->id;
            $flaggingmutasi->created_mitra = $mitra->nama_mitra ?? '-';
        }
    }
}
