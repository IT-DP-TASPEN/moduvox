<?php

namespace App\Http\Controllers;

use App\Enums\AssetStatus;
use App\Models\Inventaris;
use App\Models\MstGolongan;
use App\Models\MstJenis;
use App\Models\MstKantor;
use App\Models\MstLokasi;
use App\Models\MstRuangan;
use App\Models\MstSumberDana;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class InventarisController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $kantors = MstKantor::orderBy('kode')->get();
        $golongans = MstGolongan::orderBy('kode')->get();
        $jenises = MstJenis::orderBy('kode')->get();
        $statuses = AssetStatus::cases();

        return view('inventaris.index', compact('kantors', 'golongans', 'jenises', 'statuses'));
    }

    public function data(Request $request)
    {
        $user = auth()->user();
        
        $query = Inventaris::with(['kantor', 'golongan', 'jenis', 'ruangan']);

        // Filter based on Role (Branch isolation)
        if (!$user->isHeadOffice()) {
            $query->where('kantor_id', $user->kantor_id);
        }

        // Custom Filters
        if ($request->filled('kantor_id')) {
            $query->where('kantor_id', $request->kantor_id);
        }
        if ($request->filled('golongan_id')) {
            $query->where('golongan_id', $request->golongan_id);
        }
        if ($request->filled('jenis_id')) {
            $query->where('jenis_id', $request->jenis_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Global Search
        if (!empty($request->input('search.value'))) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('rekening', 'like', "%{$search}%")
                  ->orWhere('nama_aset', 'like', "%{$search}%")
                  ->orWhere('merk', 'like', "%{$search}%");
            });
        }

        // Order
        $columns = ['rekening', 'nama_aset', 'kantor_id', 'golongan_id', 'tgl_perolehan', 'nilai_buku', 'status'];
        if ($request->has('order')) {
            $order = $request->input('order.0');
            $columnIdx = intval($order['column']);
            if (isset($columns[$columnIdx])) {
                $query->orderBy($columns[$columnIdx], $order['dir']);
            }
        } else {
            $query->latest();
        }

        return $this->datatableResponse($query, $request, function($item) {
            $item->nama_kantor = $item->kantor ? $item->kantor->nama : '-';
            $item->nama_golongan = $item->golongan ? $item->golongan->short_label : '-';
            $item->nama_jenis = $item->jenis ? $item->jenis->nama : '-';
            $item->format_tgl_perolehan = $item->tgl_perolehan ? $item->tgl_perolehan->format('d/m/Y') : '-';
            $item->format_nilai_buku = \App\Helpers\FormatHelper::rupiah($item->nilai_buku);
            $item->status_label = '<span class="px-2 py-1 text-xs font-medium rounded-full bg-'.$item->status->color().'-100 text-'.$item->status->color().'-700">'.$item->status->label().'</span>';
            return $item;
        });
    }

    public function create()
    {
        $kantors = MstKantor::orderBy('kode')->get();
        $golongans = MstGolongan::orderBy('kode')->get();
        $jenises = MstJenis::orderBy('kode')->get();
        $lokasis = MstLokasi::orderBy('kode')->get();
        $sumberDanas = MstSumberDana::orderBy('kode')->get();

        return view('inventaris.create', compact('kantors', 'golongans', 'jenises', 'lokasis', 'sumberDanas'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_aset' => 'required|string|max:255',
            'kantor_id' => 'required|exists:mst_kantor,id',
            'golongan_id' => 'required|exists:mst_golongan,id',
            'jenis_id' => 'required|exists:mst_jenis,id',
            'ruangan_id' => 'required|exists:mst_ruangan,id',
            'lokasi_id' => 'required|exists:mst_lokasi,id',
            'sumber_id' => 'required|exists:mst_sumber_dana,id',
            'tgl_perolehan' => 'required|date',
            'harga_perolehan' => 'required|numeric|min:0',
            'merk' => 'nullable|string|max:100',
            'no_seri' => 'nullable|string|max:100',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->all();
        
        // Generate Nomor Rekening/Inventaris
        $data['rekening'] = Inventaris::generateNomorInventaris($data);
        
        // Setup initial financial values
        $data['nilai_buku'] = $data['harga_perolehan'];
        $data['akumulasi_penyusutan'] = 0;
        
        // Calculate umur_bulan from tgl_perolehan to now
        $tglPerolehan = Carbon::parse($data['tgl_perolehan'])->startOfMonth();
        $now = Carbon::now()->startOfMonth();
        // If obtained in the past, calculate how many months have passed. Usually, for new assets it's 0.
        $diff = $tglPerolehan->diffInMonths($now);
        $data['umur_bulan'] = max(0, $diff);
        
        $data['status'] = AssetStatus::AKTIF->value;

        $inventaris = Inventaris::create($data);

        // Handle Motor or Tanah specific data based on jenis_id
        $jenis = MstJenis::find($request->jenis_id);
        if ($jenis) {
            $jenisNama = strtolower($jenis->nama);
            if (str_contains($jenisNama, 'motor') || str_contains($jenisNama, 'kendaraan')) {
                $motorData = $request->only(['tahun_pembuatan', 'tahun_rakit', 'warna', 'no_rangka', 'no_mesin', 'no_bpkb', 'no_polisi', 'tgl_pajak']);
                $motorData['atas_nama'] = $request->atas_nama_motor;
                $inventaris->motor()->create($motorData);
            } elseif (str_contains($jenisNama, 'tanah') || str_contains($jenisNama, 'bangunan')) {
                $tanahData = $request->only(['no_shm', 'no_shgb', 'tanggal_shm', 'surat_ukur', 'luas_tanah', 'luas_bangunan']);
                $tanahData['atas_nama'] = $request->atas_nama_tanah;
                $inventaris->tanah()->create($tanahData);
            }
        }

        // Create initial mutation log for new asset
        \App\Models\InvMutasi::create([
            'inventaris_id' => $inventaris->id,
            'jenis_mutasi' => 'BARU',
            'tgl_mutasi' => $data['tgl_perolehan'],
            'kantor_asal_id' => null,
            'kantor_tujuan_id' => $data['kantor_id'],
            'keterangan' => 'Pencatatan aset baru',
            'status' => 'APPROVED',
            'user_id' => auth()->id()
        ]);

        return redirect()->route('inventaris.index')->with('success', 'Data Aset berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $inventaris = Inventaris::with([
            'kantor', 'golongan', 'jenis', 'ruangan', 'lokasi', 'sumberDana',
            'mutasi.kantorAsal', 'mutasi.kantorTujuan',
            'penyusutanDetail.batch'
        ])->findOrFail($id);

        return view('inventaris.show', compact('inventaris'));
    }

    public function publicScan(string $id)
    {
        $inventaris = Inventaris::with(['kantor', 'golongan', 'jenis', 'ruangan', 'lokasi'])->findOrFail($id);
        return view('inventaris.scan', compact('inventaris'));
    }

    public function edit(string $id)
    {
        $inventaris = Inventaris::with(['motor', 'tanah'])->findOrFail($id);
        
        // Prevent editing if asset has been depreciated (unless super admin)
        if ($inventaris->akumulasi_penyusutan > 0 && !auth()->user()->hasRole('Super Admin')) {
            return redirect()->route('inventaris.show', $id)->with('error', 'Aset sudah disusutkan, tidak dapat diubah.');
        }

        $kantors = MstKantor::orderBy('kode')->get();
        $golongans = MstGolongan::orderBy('kode')->get();
        $jenises = MstJenis::orderBy('kode')->get();
        $lokasis = MstLokasi::orderBy('kode')->get();
        $ruangans = MstRuangan::where('kantor_id', $inventaris->kantor_id)->orderBy('kode')->get();
        $sumberDanas = MstSumberDana::orderBy('kode')->get();

        return view('inventaris.edit', compact('inventaris', 'kantors', 'golongans', 'jenises', 'lokasis', 'ruangans', 'sumberDanas'));
    }

    public function update(Request $request, string $id)
    {
        $inventaris = Inventaris::findOrFail($id);

        if ($inventaris->akumulasi_penyusutan > 0 && !auth()->user()->hasRole('Super Admin')) {
            return redirect()->back()->with('error', 'Aset sudah disusutkan, tidak dapat diubah.');
        }

        $validator = Validator::make($request->all(), [
            'golongan_id' => 'required|exists:mst_golongan,id',
            'jenis_id' => 'required|exists:mst_jenis,id',
            'ruangan_id' => 'required|exists:mst_ruangan,id',
            'lokasi_id' => 'required|exists:mst_lokasi,id',
            'sumber_id' => 'required|exists:mst_sumber_dana,id',
            'tgl_perolehan' => 'required|date',
            'harga_perolehan' => 'required|numeric|min:0',
            'merk' => 'nullable|string|max:100',
            'no_seri' => 'nullable|string|max:100',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->all();
        
        // If master data affecting the code changed, we might need to regenerate the code.
        // For now, we will NOT regenerate the code to keep historical integrity, unless requested.
        // Only update harga_perolehan and nilai_buku if it's completely new.
        if ($inventaris->akumulasi_penyusutan == 0) {
            $data['nilai_buku'] = $data['harga_perolehan'];
            
            $tglPerolehan = Carbon::parse($data['tgl_perolehan'])->startOfMonth();
            $now = Carbon::now()->startOfMonth();
            $data['umur_bulan'] = max(0, $tglPerolehan->diffInMonths($now));
        } else {
            // Protect financial fields if already depreciated
            unset($data['harga_perolehan']);
            unset($data['tgl_perolehan']);
        }

        $inventaris->update($data);

        // Handle Motor or Tanah specific data based on jenis_id
        $jenis = MstJenis::find($request->jenis_id);
        if ($jenis) {
            $jenisNama = strtolower($jenis->nama);
            if (str_contains($jenisNama, 'motor') || str_contains($jenisNama, 'kendaraan')) {
                $motorData = $request->only(['tahun_pembuatan', 'tahun_rakit', 'warna', 'no_rangka', 'no_mesin', 'no_bpkb', 'no_polisi', 'tgl_pajak']);
                $motorData['atas_nama'] = $request->atas_nama_motor;
                if ($inventaris->motor) {
                    $inventaris->motor->update($motorData);
                } else {
                    $inventaris->motor()->create($motorData);
                }
            } elseif (str_contains($jenisNama, 'tanah') || str_contains($jenisNama, 'bangunan')) {
                $tanahData = $request->only(['no_shm', 'no_shgb', 'tanggal_shm', 'surat_ukur', 'luas_tanah', 'luas_bangunan']);
                $tanahData['atas_nama'] = $request->atas_nama_tanah;
                if ($inventaris->tanah) {
                    $inventaris->tanah->update($tanahData);
                } else {
                    $inventaris->tanah()->create($tanahData);
                }
            }
        }

        return redirect()->route('inventaris.show', $id)->with('success', 'Data Aset berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $inventaris = Inventaris::findOrFail($id);
        
        if ($inventaris->akumulasi_penyusutan > 0 && !auth()->user()->hasRole('Super Admin')) {
            return $this->error('Aset sudah disusutkan, tidak dapat dihapus', 400);
        }

        $inventaris->delete();

        return $this->success(null, 'Data Aset berhasil dihapus');
    }

    public function printLabel(string $id)
    {
        $inventaris = Inventaris::with(['kantor', 'golongan', 'ruangan'])->findOrFail($id);
        $assets = collect([$inventaris]);
        return view('inventaris.print-label', compact('assets'));
    }

    public function printLabelMassal(Request $request)
    {
        $ids = explode(',', $request->ids);
        $assets = Inventaris::with(['kantor', 'golongan', 'ruangan'])
                    ->whereIn('id', $ids)
                    ->get();
                    
        if ($assets->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada aset yang dipilih untuk dicetak.');
        }

        $templateKey = $request->template ?? 'A4_STANDARD';
        $templateConfig = config("label.templates.{$templateKey}") ?? config("label.templates.A4_STANDARD");

        return view('inventaris.print-label', compact('assets', 'templateConfig'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls'
        ]);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\InventarisImport, $request->file('file'));
            return redirect()->back()->with('success', 'Data Master Inventaris berhasil di-import.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import data: ' . $e->getMessage());
        }
    }

    // Helper for AJAX dropdown cascading
    public function getRuanganByKantor($kantor_id)
    {
        $ruangans = MstRuangan::where('kantor_id', $kantor_id)->orderBy('kode')->get();
        return response()->json($ruangans);
    }
}
