<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventaris;
use App\Models\MstKantor;
use App\Models\MstGolongan;
use App\Enums\AssetStatus;

class DashboardController extends Controller
{
    public function index()
    {
        $kantor_id = auth()->user()->kantor_id;

        $query = Inventaris::where('status', AssetStatus::AKTIF->value);
        if ($kantor_id && !auth()->user()->hasRole('Super Admin')) {
            $query->where('kantor_id', $kantor_id);
        }

        $totalAset = $query->count();
        $totalPerolehan = $query->sum('harga_perolehan');
        $totalBuku = $query->sum('nilai_buku');
        $totalAkumulasi = $query->sum('akumulasi_penyusutan');

        // Data Chart Cabang
        $chartCabang = MstKantor::withCount(['inventaris' => function($q) use ($kantor_id) {
            $q->where('status', AssetStatus::AKTIF->value);
            if ($kantor_id && !auth()->user()->hasRole('Super Admin')) {
                $q->where('kantor_id', $kantor_id);
            }
        }])->having('inventaris_count', '>', 0)->get();

        // Data Chart Golongan
        $chartGolongan = MstGolongan::withCount(['inventaris' => function($q) use ($kantor_id) {
            $q->where('status', AssetStatus::AKTIF->value);
            if ($kantor_id && !auth()->user()->hasRole('Super Admin')) {
                $q->where('kantor_id', $kantor_id);
            }
        }])->having('inventaris_count', '>', 0)->get();

        return view('dashboard.index', compact(
            'totalAset', 'totalPerolehan', 'totalBuku', 'totalAkumulasi',
            'chartCabang', 'chartGolongan'
        ));
    }
}
