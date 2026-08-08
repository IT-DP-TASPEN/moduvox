<?php

namespace App\Observers;

use App\Models\PermintaanChecking;
use Illuminate\Support\Facades\Auth;

class PermintaanCheckingObserver
{
    public function creating(PermintaanChecking $checking)
    {
        $this->calculateCheckingFields($checking, true);
    }

    public function updating(PermintaanChecking $checking)
    {
        if (auth()->id() !== $checking->created_by) {
            return;
        }
        $this->calculateCheckingFields($checking, false);
    }

    private function calculateCheckingFields(PermintaanChecking $checking, bool $isCreating)
    {
        $user = Auth::user();
        $mitra = $user?->mitraMaster;

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

        // ======== Set created_by & created_mitra jika creating ========
        if ($isCreating && $user) {
            $checking->created_by = $user->id;
            $checking->created_mitra = $mitra->nama_mitra ?? '-';
        }
    }
}
