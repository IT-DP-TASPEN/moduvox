<?php

namespace App\Observers;

use App\Models\PermintaanEstimasi;
use Illuminate\Support\Facades\Auth;

class PermintaanEstimasiObserver
{
    public function creating(PermintaanEstimasi $estimasi)
    {
        $this->calculateEstimasiFields($estimasi, true);
    }

    public function updating(PermintaanEstimasi $estimasi)
    {
        if (auth()->id() !== $estimasi->created_by) {
            return;
        }
        $this->calculateEstimasiFields($estimasi, false);
    }

    private function calculateEstimasiFields(PermintaanEstimasi $estimasi, bool $isCreating)
    {
        $user = Auth::user();
        $mitra = $user?->mitraMaster;

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

        // ======== Set created_by & created_mitra jika creating ========
        if ($isCreating && $user) {
            $estimasi->created_by = $user->id;
            $estimasi->created_mitra = $mitra->nama_mitra ?? '-';
        }
    }
}
