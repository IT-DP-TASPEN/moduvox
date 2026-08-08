<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use App\Models\PermintaanFlaggingMutasiTifInternal;

class PermintaanFlaggingMutasiInternalObserver
{
    public function creating(PermintaanFlaggingMutasiTifInternal $flaggingmutasi)
    {
        $this->calculateFlaggingmutasiFields($flaggingmutasi, true);
    }

    public function updating(PermintaanFlaggingMutasiTifInternal $flaggingmutasi)
    {
        if (auth()->id() !== $flaggingmutasi->created_by && !auth()->user()->hasRole('super_admin')) {
            return;
        }
        $this->calculateFlaggingmutasiFields($flaggingmutasi, false);
    }

    private function calculateFlaggingmutasiFields(PermintaanFlaggingMutasiTifInternal $flaggingmutasi, bool $isCreating)
    {
        $user = Auth::user();
        $mitra = $flaggingmutasi->mitraMaster;
        $branch = $user?->branchMaster;

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

        // ======== Set created_by & created_branch jika creating ========
        if ($isCreating && $user) {
            $flaggingmutasi->created_by = $user->id;
            $flaggingmutasi->created_branch = $branch->branch_name ?? '-';
        }
    }
}
