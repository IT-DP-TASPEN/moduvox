<?php

namespace App\Http\Controllers;

use App\Models\Inventaris;
use App\Models\MstKantor;
use App\Models\PenyusutanDetail;
use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PenyusutanImport;

class InventarisPenyusutanController extends Controller
{
    use \App\Traits\ApiResponse;

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240', // max 10MB
        ]);

        try {
            set_time_limit(0); // Prevent timeout for large files
            ini_set('memory_limit', '-1');
            
            Excel::import(new PenyusutanImport, $request->file('file'));
            
            return redirect()->back()->with('success', 'File sedang diproses di background. Silakan tunggu beberapa saat atau refresh halaman nanti.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error importing penyusutan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function index()
    {
        $kantors = MstKantor::orderBy('kode')->get();
        return view('penyusutan_list.index', compact('kantors'));
    }

    public function data(Request $request)
    {
        $query = PenyusutanDetail::with(['inventaris.golongan', 'kantor', 'batch'])
            ->select('penyusutan_detail.*');

        if ($request->filled('kantor_id')) {
            $query->where('penyusutan_detail.kantor_id', $request->kantor_id);
        }

        if (!empty($request->input('search.value'))) {
            $search = $request->input('search.value');
            $query->whereHas('inventaris', function ($q) use ($search) {
                $q->where('rekening', 'like', "%{$search}%")
                  ->orWhere('nama_aset', 'like', "%{$search}%");
            });
        }

        $columns = [
            'batch.periode_ym', 
            'inventaris.rekening', 
            'inventaris.nama_aset', 
            'kantor.nama', 
            'inventaris.golongan_id', 
            'penyusutan_detail.beban_bulan_ini', 
            'penyusutan_detail.akumulasi', 
            'penyusutan_detail.nilai_buku_sesudah'
        ];
        
        if ($request->has('order')) {
            $order = $request->input('order.0');
            $columnIdx = intval($order['column']);
            // Join might be required for complex sorting, but for simplicity we rely on latest if we can't sort relations easily
            if ($columnIdx == 5) {
                $query->orderBy('penyusutan_detail.beban_bulan_ini', $order['dir']);
            } elseif ($columnIdx == 6) {
                $query->orderBy('penyusutan_detail.akumulasi', $order['dir']);
            } elseif ($columnIdx == 7) {
                $query->orderBy('penyusutan_detail.nilai_buku_sesudah', $order['dir']);
            } else {
                $query->latest('penyusutan_detail.created_at');
            }
        } else {
            $query->latest('penyusutan_detail.created_at');
        }

        return $this->datatableResponse($query, $request, function($item) {
            $item->periode = $item->batch ? $item->batch->periode_ym : '-';
            $item->rekening = $item->inventaris ? $item->inventaris->rekening : '-';
            $item->nama_aset = $item->inventaris ? $item->inventaris->nama_aset : '-';
            $item->merk = $item->inventaris ? $item->inventaris->merk : '';
            $item->nama_kantor = $item->kantor ? $item->kantor->nama : '-';
            $item->nama_golongan = ($item->inventaris && $item->inventaris->golongan) ? $item->inventaris->golongan->short_label : '-';
            
            $item->format_beban = \App\Helpers\FormatHelper::rupiah($item->beban_bulan_ini);
            $item->format_akumulasi = \App\Helpers\FormatHelper::rupiah($item->akumulasi);
            $item->format_nilai_buku = \App\Helpers\FormatHelper::rupiah($item->nilai_buku_sesudah);
            
            if ($item->inventaris) {
                $item->status_label = '<span class="px-2 py-1 text-xs font-medium rounded-full bg-'.$item->inventaris->status->color().'-100 text-'.$item->inventaris->status->color().'-700">'.$item->inventaris->status->label().'</span>';
            } else {
                $item->status_label = '-';
            }
            
            return $item;
        });
    }
}
