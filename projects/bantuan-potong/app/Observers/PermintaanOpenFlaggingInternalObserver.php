<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use App\Models\PermintaanOpenFlaggingInternal;

class PermintaanOpenFlaggingInternalObserver
{
    public function creating(PermintaanOpenFlaggingInternal $openflagging)
    {
        $this->calculateOpenflaggingFields($openflagging, true);
    }

    public function updating(PermintaanOpenFlaggingInternal $openflagging)
    {
        if (auth()->id() !== $openflagging->created_by && !auth()->user()->hasRole('super_admin')) {
            return;
        }
        $this->calculateOpenflaggingFields($openflagging, false);
    }

    private function calculateOpenflaggingFields(PermintaanOpenFlaggingInternal $openflagging, bool $isCreating)
    {
        $user = Auth::user();
        $mitra = $openflagging->mitraMaster;
        $branch = $user?->branchMaster;

        // ======== Reset field sebelum kalkulasi ulang ========
        $openflagging->fee = null;

        // ======== Konversi nama nasabah jadi UPPERCASE ========
        if (!empty($openflagging->nama_nasabah)) {
            $openflagging->nama_nasabah = strtoupper(trim($openflagging->nama_nasabah));
        }

        if (!empty($openflagging->alamat)) {
            $openflagging->alamat = strtoupper(trim($openflagging->alamat));
        }

        if (!empty($openflagging->tempat_lahir)) {
            $openflagging->tempat_lahir = strtoupper(trim($openflagging->tempat_lahir));
        }

        // ======== Ambil parameter dari mitra (jika ada) ========
        if ($mitra && isset($mitra->biaya_checking)) {
            $openflagging->fee = $mitra->biaya_checking;
        }

        // ======== Set created_by & created_branch jika creating ========
        if ($isCreating && $user) {
            $openflagging->created_by = $user->id;
            $openflagging->created_branch = $branch->branch_name ?? '-';
        }
    }
}
