<?php

namespace App\Http\Controllers;

use App\Models\Inventaris;
use App\Models\MstKantor;
use App\Models\PenyusutanBatch;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function nominatifIndex()
    {
        $kantors = MstKantor::orderBy('kode')->get();
        return view('reports.nominatif_filter', compact('kantors'));
    }

    public function penyusutanIndex()
    {
        $kantors = MstKantor::orderBy('kode')->get();
        // Untuk sementara arahkan ke filter yang ada atau view placeholder.
        // Kita bisa copy view nominatif_filter.blade.php menjadi penyusutan_filter.blade.php
        return view('reports.penyusutan_filter', compact('kantors'));
    }

    public function nominatifGenerate(Request $request)
    {
        $request->validate([
            'bulan' => 'required|numeric|min:1|max:12',
            'tahun' => 'required|numeric|min:2000|max:2100',
            'kantor_id' => 'nullable|exists:mst_kantor,id'
        ]);

        $bulan = str_pad($request->bulan, 2, '0', STR_PAD_LEFT);
        $periode_ym = $request->tahun . $bulan;
        $periodeDate = Carbon::createFromFormat('Ym', $periode_ym)->endOfMonth();

        $kantor = null;
        if ($request->kantor_id) {
            $kantor = MstKantor::find($request->kantor_id);
        }

        // Ambil semua aset yang tgl perolehannya sebelum atau sama dengan periode laporan
        $query = Inventaris::with(['golongan', 'kantor'])
        ->withSum(['penyusutanDetail as beban_setelah_periode' => function($q) use ($periode_ym) {
            $q->whereHas('batch', function($qb) use ($periode_ym) {
                $qb->where('periode_ym', '>', $periode_ym);
            });
        }], 'beban_bulan_ini')
        ->withSum(['penyusutanDetail as beban_bulan_ini_val' => function($q) use ($periode_ym) {
            $q->whereHas('batch', function($qb) use ($periode_ym) {
                $qb->where('periode_ym', '=', $periode_ym);
            });
        }], 'beban_bulan_ini')
        ->where('tgl_perolehan', '<=', $periodeDate);

        if ($kantor) {
            $query->where('kantor_id', $kantor->id);
        }

        // Role-based filtering if user is not super admin
        if (!auth()->user()->hasRole('Super Admin') && auth()->user()->kantor_id) {
            $query->where('kantor_id', auth()->user()->kantor_id);
            if (!$kantor) {
                $kantor = MstKantor::find(auth()->user()->kantor_id);
            }
        }

        $assets = $query->get()->sortBy(function($item) {
            return $item->golongan ? $item->golongan->kode : 'ZZZ';
        });

        // Kelompokkan berdasarkan golongan
        $groupedData = $assets->groupBy(function($item) {
            $golKode = $item->golongan ? $item->golongan->kode : 'Lainnya';
            $golNama = $item->golongan ? $item->golongan->nama : 'Tanpa Golongan';
            return $golKode . ' - ' . $golNama;
        });

        $is_excel = $request->has('export_excel');

        if ($is_excel) {
            $filename = "Laporan_Nominatif_" . ($kantor ? $kantor->kode : 'ALL') . "_{$periode_ym}.xls";
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=\"$filename\"");
        }

        $dateFormatted = \App\Helpers\FormatHelper::tanggalIndonesia($periodeDate);

        return view('reports.nominatif_print', compact('groupedData', 'kantor', 'dateFormatted', 'is_excel', 'periodeDate', 'periode_ym'));
    }

    public function penyusutanGenerate(Request $request)
    {
        $request->validate([
            'bulan' => 'required|numeric|min:1|max:12',
            'tahun' => 'required|numeric|min:2000|max:2100',
            'kantor_id' => 'nullable|exists:mst_kantor,id'
        ]);

        $bulan = str_pad($request->bulan, 2, '0', STR_PAD_LEFT);
        $periode_ym = $request->tahun . $bulan;
        $periodeDate = Carbon::createFromFormat('Ym', $periode_ym)->endOfMonth();

        $kantor = null;
        if ($request->kantor_id) {
            $kantor = MstKantor::find($request->kantor_id);
        }

        // Ambil semua aset yang disusutkan PADA periode ini (memiliki beban_bulan_ini > 0)
        $query = Inventaris::with(['golongan', 'kantor'])
        ->withSum(['penyusutanDetail as beban_setelah_periode' => function($q) use ($periode_ym) {
            $q->whereHas('batch', function($qb) use ($periode_ym) {
                $qb->where('periode_ym', '>', $periode_ym);
            });
        }], 'beban_bulan_ini')
        ->withSum(['penyusutanDetail as beban_bulan_ini_val' => function($q) use ($periode_ym) {
            $q->whereHas('batch', function($qb) use ($periode_ym) {
                $qb->where('periode_ym', '=', $periode_ym);
            });
        }], 'beban_bulan_ini')
        ->whereHas('penyusutanDetail', function($q) use ($periode_ym) {
            $q->where('beban_bulan_ini', '>', 0)
              ->whereHas('batch', function($qb) use ($periode_ym) {
                  $qb->where('periode_ym', $periode_ym);
              });
        })
        ->whereHas('golongan', function($q) {
            $q->where('kode', '!=', '01');
        })
        ->where('tgl_perolehan', '<=', $periodeDate);

        if ($kantor) {
            $query->where('kantor_id', $kantor->id);
        }

        // Role-based filtering if user is not super admin
        if (!auth()->user()->hasRole('Super Admin') && auth()->user()->kantor_id) {
            $query->where('kantor_id', auth()->user()->kantor_id);
            if (!$kantor) {
                $kantor = MstKantor::find(auth()->user()->kantor_id);
            }
        }

        $assets = $query->get()->sortBy(function($item) {
            return $item->golongan ? $item->golongan->kode : 'ZZZ';
        });

        // Kelompokkan berdasarkan kantor lalu golongan
        $groupedData = $assets->groupBy(function($item) {
            return $item->kantor ? $item->kantor->nama : 'Tanpa Cabang';
        })->map(function($items) {
            return $items->groupBy(function($item) {
                $golKode = $item->golongan ? $item->golongan->kode : 'Lainnya';
                $golNama = $item->golongan ? $item->golongan->nama : 'Tanpa Golongan';
                return $golKode . ' - ' . $golNama;
            });
        });

        $is_excel = $request->has('export_excel');

        if ($is_excel) {
            $filename = "Laporan_Penyusutan_" . ($kantor ? $kantor->kode : 'ALL') . "_{$periode_ym}.xls";
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=\"$filename\"");
        }

        $dateFormatted = \App\Helpers\FormatHelper::tanggalIndonesia($periodeDate);

        return view('reports.penyusutan_print', compact('groupedData', 'kantor', 'dateFormatted', 'is_excel', 'periodeDate', 'periode_ym'));
    }
}
