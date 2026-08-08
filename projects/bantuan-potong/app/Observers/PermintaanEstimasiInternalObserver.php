<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use App\Models\PermintaanEstimasiInternal;

class PermintaanEstimasiInternalObserver
{
    public function creating(PermintaanEstimasiInternal $estimasi)
    {
        $this->calculateEstimasiFields($estimasi, true);
    }

    public function updating(PermintaanEstimasiInternal $estimasi)
    {
        if (auth()->id() !== $estimasi->created_by && !auth()->user()->hasRole('super_admin')) {
            return;
        }
        $this->calculateEstimasiFields($estimasi, false);
    }

    private function calculateEstimasiFields(PermintaanEstimasiInternal $estimasi, bool $isCreating)
    {
        $user = Auth::user();
        $mitra = $estimasi->mitraMaster;
        $branch = $user?->branchMaster;

        // ======== Reset field sebelum kalkulasi ulang ========
        $estimasi->fee = null;

        // ======== Konversi nama nasabah jadi UPPERCASE ========
        if (!empty($estimasi->nama_nasabah)) {
            $estimasi->nama_nasabah = strtoupper(trim($estimasi->nama_nasabah));
        }

        // ======== Ambil parameter dari mitra (jika ada) ========
        if ($mitra && isset($mitra->biaya_check_estimasi)) {
            $estimasi->fee = $mitra->biaya_check_estimasi;
        }

        // ======== Set created_by & created_branch jika creating ========
        if ($isCreating && $user) {
            $estimasi->created_by = $user->id;
            $estimasi->created_branch = $branch->branch_name ?? '-';
        }
    }
}
