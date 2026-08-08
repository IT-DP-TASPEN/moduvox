<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use App\Models\PermintaanCheckingInternal;

class PermintaanCheckingInternalObserver
{
    public function creating(PermintaanCheckingInternal $checking)
    {
        $this->calculateCheckingFields($checking, true);
    }

    public function updating(PermintaanCheckingInternal $checking)
    {
        if (auth()->id() !== $checking->created_by && !auth()->user()->hasRole('super_admin') ) {
            return;
        }
        $this->calculateCheckingFields($checking, false);
    }

    private function calculateCheckingFields(PermintaanCheckingInternal $checking, bool $isCreating)
    {
        $user = Auth::user();
        $mitra = $checking->mitraMaster;
        $branch = $user?->branchMaster;

        // ======== Reset field sebelum kalkulasi ulang ========
        $checking->fee = null;

        // ======== Konversi nama nasabah jadi UPPERCASE ========
        if (!empty($checking->nama_nasabah)) {
            $checking->nama_nasabah = strtoupper(trim($checking->nama_nasabah));
        }

        // ======== Ambil parameter dari mitra (jika ada) ========
        if ($mitra && isset($mitra->biaya_checking)) {
            $checking->fee = $mitra->biaya_checking;
        }

        // ======== Set created_by & created_branch jika creating ========
        if ($isCreating && $user) {
            $checking->created_by = $user->id;
            $checking->created_branch = $branch->branch_name ?? '-';
        }
    }
}
