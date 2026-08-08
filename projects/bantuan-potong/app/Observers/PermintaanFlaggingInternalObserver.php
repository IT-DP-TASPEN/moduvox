<?php

namespace App\Observers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\PermintaanFlaggingTifInternal;

class PermintaanFlaggingInternalObserver
{
    public function creating(PermintaanFlaggingTifInternal $model)
    {
        $this->calc($model, true);
    }

    public function updating(PermintaanFlaggingTifInternal $model)
    {
        if (auth()->id() !== $model->created_by && !auth()->user()->hasRole('super_admin')) {
            return;
        }
        $this->calc($model, false);
    }

    private function calc(PermintaanFlaggingTifInternal $model, bool $creating)
    {
        $user = Auth::user();
        $mitra = $model->mitraMaster;
        $branch = $user?->branchMaster;

        // reset
        $model->fee = null;

        // uppercase nama nasabah
        if ($model->nama_nasabah) {
            $model->nama_nasabah = strtoupper(trim($model->nama_nasabah));
        }

        // set created_x
        if ($creating && $user) {
            $model->created_by = $user->id;
            $model->created_branch = $branch->branch_name ?? '-';
        }

        if (!$mitra)
            return; // safety

        // ==== hitung fee per type ====
        switch ($model->jenis_flagging) {

            case 'pensiun':
                $model->fee = $mitra->biaya_flagging_pensiun ?? 0;
                $model->fee_checking = $mitra->biaya_checking ?? 0;
                break;

            case 'tht':
                $model->fee = $mitra->biaya_flagging_tht ?? 0;
                $model->fee_checking = $mitra->biaya_checking ?? 0;
                break;

            case 'prapen':
                $tahunRounded = $this->hitungDurasi($model); // ini yg bulat 1.5 / 2.3 / etc
                $model->fee = ($mitra->biaya_flagging_prapen ?? 1) + ($tahunRounded * 60000);
                $model->fee_checking = $mitra->biaya_checking ?? 0;
                break;

            case 'prapen_tht':
                $tahunRounded = $this->hitungDurasi($model);
                $feeRumus = ($mitra->biaya_flagging_prapen ?? 1) + ($tahunRounded * 60000);
                $feeTht = $mitra->biaya_flagging_tht ?? 0;
                $model->fee = $feeRumus + $feeTht;
                $model->fee_checking = $mitra->biaya_checking ?? 0;
                break;
        }
    }

    private function hitungDurasi($model): int
    {
        if (!$model->tanggal_bup || !$model->tmt_kredit) {
            $model->selisih_prapen = null;
            return 0;
        }

        $bupMinus3 = Carbon::parse($model->tanggal_bup)->subYears(3);
        $selisih = $bupMinus3->diff(Carbon::parse($model->tmt_kredit));

        // Cek apakah hasilnya minus (TMT Kredit > BUP - 3 tahun)
        if ($bupMinus3->lt(Carbon::parse($model->tmt_kredit))) {
            // Selisih minus, tampilkan 0
            $model->selisih_prapen = '0';
            return 0;
        }

        $tahunFloat = $selisih->y + ($selisih->m / 12);

        // UI tampil seperti 1.5 / 2.3 / 8.0
        $model->selisih_prapen = number_format($tahunFloat, 1, '.', '');

        // perhitungan fee tetap sesuai bisnis (pembulatan ke atas)
        return ceil($tahunFloat);
    }


}
