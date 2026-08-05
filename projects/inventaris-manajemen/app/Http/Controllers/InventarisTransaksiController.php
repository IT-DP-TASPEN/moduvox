<?php

namespace App\Http\Controllers;

use App\Models\InvMutasi;
use App\Models\MstKantor;
use Illuminate\Http\Request;

class InventarisTransaksiController extends Controller
{
    use \App\Traits\ApiResponse;

    public function index()
    {
        $kantors = MstKantor::orderBy('kode')->get();
        $golongans = \App\Models\MstGolongan::orderBy('kode')->get();
        $jenises = \App\Models\MstJenis::orderBy('kode')->get();
        $statuses = \App\Enums\AssetStatus::cases();
        return view('transaksi.index', compact('kantors', 'golongans', 'jenises', 'statuses'));
    }

    public function data(Request $request)
    {
        $query = InvMutasi::with(['inventaris.kantor', 'kantorAsal', 'kantorTujuan', 'user']);

        if ($request->filled('kantor_id')) {
            $query->where(function($q) use ($request) {
                $q->where('kantor_asal_id', $request->kantor_id)
                  ->orWhere('kantor_tujuan_id', $request->kantor_id);
            });
        }

        if (!empty($request->input('search.value'))) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('keterangan', 'like', "%{$search}%")
                  ->orWhereHas('inventaris', function($qi) use ($search) {
                      $qi->where('rekening', 'like', "%{$search}%")
                         ->orWhere('nama_aset', 'like', "%{$search}%");
                  });
            });
        }

        $columns = ['tgl_mutasi', 'inventaris_id', 'kantor_asal_id', 'kantor_tujuan_id', 'user_id'];
        if ($request->has('order')) {
            $order = $request->input('order.0');
            $columnIdx = intval($order['column']);
            if (isset($columns[$columnIdx])) {
                $query->orderBy($columns[$columnIdx], $order['dir']);
            }
        } else {
            $query->latest('tgl_mutasi');
        }

        return $this->datatableResponse($query, $request, function($item) {
            $item->rekening = $item->inventaris ? $item->inventaris->rekening : '-';
            $item->nama_aset = $item->inventaris ? $item->inventaris->nama_aset : '-';
            $item->nama_kantor_asal = $item->kantorAsal ? $item->kantorAsal->nama : '-';
            $item->nama_kantor_tujuan = $item->kantorTujuan ? $item->kantorTujuan->nama : '-';
            $item->nama_user = $item->user ? $item->user->name : '-';
            $item->format_tanggal = $item->tgl_mutasi ? $item->tgl_mutasi->format('d/m/Y') : '-';
            
            $jenisColor = 'blue';
            if ($item->jenis_mutasi === 'PEMBELIAN') $jenisColor = 'green';
            if ($item->jenis_mutasi === 'PENGHAPUSAN') $jenisColor = 'red';
            if ($item->jenis_mutasi === 'PENJUALAN') $jenisColor = 'orange';
            if ($item->jenis_mutasi === 'ANGSURAN') $jenisColor = 'indigo';
            if ($item->jenis_mutasi === 'HADIAH') $jenisColor = 'purple';
            if ($item->jenis_mutasi === 'REVALUASI') $jenisColor = 'teal';
            if ($item->jenis_mutasi === 'MUTASI') $jenisColor = 'blue';
            $item->jenis_label = '<span class="px-2 py-1 text-[10px] font-bold rounded bg-'.$jenisColor.'-100 text-'.$jenisColor.'-700">'.$item->jenis_mutasi.'</span>';
            
            $kantorText = $item->nama_kantor_tujuan;
            if ($item->jenis_mutasi === 'MUTASI' && $item->nama_kantor_asal !== '-') {
                $kantorText = $item->nama_kantor_asal . ' <i class="fa-solid fa-arrow-right text-gray-400 mx-1"></i> ' . $item->nama_kantor_tujuan;
            }
            $item->kantor_info = $kantorText;
            
            $item->keterangan_short = $item->keterangan ? \Illuminate\Support\Str::limit($item->keterangan, 50) : '-';
            
            $statusColor = 'gray';
            if ($item->status === 'APPROVED') $statusColor = 'emerald';
            if ($item->status === 'PENDING') $statusColor = 'amber';
            if ($item->status === 'REJECTED') $statusColor = 'red';
            
            $item->status_label = '<span class="px-2 py-1 text-[10px] font-bold rounded bg-'.$statusColor.'-100 text-'.$statusColor.'-700">'.$item->status.'</span>';
            return $item;
        });
    }

    public function show($id)
    {
        $mutasi = InvMutasi::with([
            'inventaris.kantor', 
            'inventaris.golongan', 
            'inventaris.jenis',
            'kantorAsal', 
            'kantorTujuan', 
            'ruanganAsal',
            'ruanganTujuan',
            'user', 
            'approvalUser'
        ])->findOrFail($id);

        return view('transaksi.show', compact('mutasi'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls'
        ]);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\TransaksiImport, $request->file('file'));
            return redirect()->back()->with('success', 'Data Transaksi Mutasi berhasil di-import.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import data: ' . $e->getMessage());
        }
    }
}
