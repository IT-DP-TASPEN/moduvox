<?php

namespace App\Http\Controllers;

use App\Models\Inventaris;
use App\Models\InvMotor;
use App\Models\MstKantor;
use App\Models\MstGolongan;
use App\Models\MstJenis;
use App\Models\MstLokasi;
use App\Models\MstSumberDana;
use App\Models\MstRuangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class InventarisMotorController extends Controller
{
    use \App\Traits\ApiResponse;

    public function index()
    {
        $kantors = MstKantor::orderBy('kode')->get();
        return view('motor.index', compact('kantors'));
    }

    public function data(Request $request)
    {
        $query = Inventaris::with(['kantor', 'motor'])
            ->has('motor');

        if ($request->filled('kantor_id')) {
            $query->where('kantor_id', $request->kantor_id);
        }

        if (!empty($request->input('search.value'))) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('rekening', 'like', "%{$search}%")
                  ->orWhere('nama_aset', 'like', "%{$search}%")
                  ->orWhereHas('motor', function($qm) use ($search) {
                      $qm->where('no_polisi', 'like', "%{$search}%");
                  });
            });
        }

        $columns = ['rekening', 'nama_aset', 'kantor_id', 'tgl_perolehan', 'nilai_buku'];
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
            $motor = $item->motor;
            $item->no_polisi = $motor ? $motor->no_polisi : '-';
            $item->no_bpkb = $motor ? $motor->no_bpkb : '-';
            $item->no_mesin = $motor ? $motor->no_mesin : '-';
            $item->no_rangka = $motor ? $motor->no_rangka : '-';
            $item->tahun_pembuatan = $motor ? $motor->tahun_pembuatan : '-';
            $item->tahun_rakit = $motor ? $motor->tahun_rakit : '-';
            $item->warna_motor = $motor ? $motor->warna : '-';
            $item->atas_nama = $motor ? $motor->atas_nama : '-';
            $item->format_tgl_perolehan = $item->tgl_perolehan ? $item->tgl_perolehan->format('d/m/Y') : '-';
            $item->format_nilai_buku = \App\Helpers\FormatHelper::rupiah($item->nilai_buku);
            $item->status_label = '<span class="px-2 py-1 text-xs font-medium rounded-full bg-'.$item->status->color().'-100 text-'.$item->status->color().'-700">'.$item->status->label().'</span>';
            return $item;
        });
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls'
        ]);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\MotorImport, $request->file('file'));
            return redirect()->back()->with('success', 'Data Inventaris Motor berhasil di-import.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import data: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $kantors = MstKantor::orderBy('kode')->get();
        // Specifically look for Motor golongan/jenis if applicable, else load all
        $golongans = MstGolongan::orderBy('kode')->get();
        $jenises = MstJenis::orderBy('kode')->get();
        $lokasis = MstLokasi::orderBy('kode')->get();
        $sumberDanas = MstSumberDana::orderBy('kode')->get();

        return view('motor.create', compact('kantors', 'golongans', 'jenises', 'lokasis', 'sumberDanas'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kantor_id' => 'required|exists:mst_kantor,id',
            'golongan_id' => 'required|exists:mst_golongan,id',
            'jenis_id' => 'required|exists:mst_jenis,id',
            'lokasi_id' => 'required|exists:mst_lokasi,id',
            'ruangan_id' => 'required|exists:mst_ruangan,id',
            'sumber_dana_id' => 'required|exists:mst_sumber_dana,id',
            'nama_aset' => 'required|string|max:255',
            'tgl_perolehan' => 'required|date',
            'harga_perolehan' => 'required|numeric|min:0',
            // Motor fields
            'no_polisi' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $data = $request->except(['tahun_pembuatan', 'tahun_rakit', 'warna', 'no_rangka', 'no_mesin', 'no_bpkb', 'no_polisi', 'tgl_pajak', 'atas_nama']);
            $data['nilai_buku'] = $data['harga_perolehan'];
            
            $tglPerolehan = Carbon::parse($data['tgl_perolehan'])->startOfMonth();
            $now = Carbon::now()->startOfMonth();
            $data['umur_bulan'] = max(0, $tglPerolehan->diffInMonths($now));

            $inventaris = Inventaris::create($data);

            $inventaris->motor()->create($request->only([
                'tahun_pembuatan', 'tahun_rakit', 'warna', 'no_rangka', 'no_mesin', 'no_bpkb', 'no_polisi', 'tgl_pajak', 'atas_nama'
            ]));

            DB::commit();
            return redirect()->route('motor.index')->with('success', 'Data Kendaraan Motor berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(string $id)
    {
        $inventaris = Inventaris::with('motor')->findOrFail($id);
        $kantors = MstKantor::orderBy('kode')->get();
        $golongans = MstGolongan::orderBy('kode')->get();
        $jenises = MstJenis::orderBy('kode')->get();
        $lokasis = MstLokasi::orderBy('kode')->get();
        $ruangans = MstRuangan::where('kantor_id', $inventaris->kantor_id)->orderBy('kode')->get();
        $sumberDanas = MstSumberDana::orderBy('kode')->get();

        return view('motor.edit', compact('inventaris', 'kantors', 'golongans', 'jenises', 'lokasis', 'ruangans', 'sumberDanas'));
    }

    public function update(Request $request, string $id)
    {
        $inventaris = Inventaris::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'kantor_id' => 'required|exists:mst_kantor,id',
            'nama_aset' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $data = $request->except(['tahun_pembuatan', 'tahun_rakit', 'warna', 'no_rangka', 'no_mesin', 'no_bpkb', 'no_polisi', 'tgl_pajak', 'atas_nama']);
            
            if ($inventaris->akumulasi_penyusutan == 0) {
                $data['nilai_buku'] = $data['harga_perolehan'];
                $tglPerolehan = Carbon::parse($data['tgl_perolehan'])->startOfMonth();
                $now = Carbon::now()->startOfMonth();
                $data['umur_bulan'] = max(0, $tglPerolehan->diffInMonths($now));
            } else {
                unset($data['harga_perolehan']);
                unset($data['tgl_perolehan']);
            }

            $inventaris->update($data);

            if ($inventaris->motor) {
                $inventaris->motor->update($request->only([
                    'tahun_pembuatan', 'tahun_rakit', 'warna', 'no_rangka', 'no_mesin', 'no_bpkb', 'no_polisi', 'tgl_pajak', 'atas_nama'
                ]));
            } else {
                $inventaris->motor()->create($request->only([
                    'tahun_pembuatan', 'tahun_rakit', 'warna', 'no_rangka', 'no_mesin', 'no_bpkb', 'no_polisi', 'tgl_pajak', 'atas_nama'
                ]));
            }

            DB::commit();
            return redirect()->route('motor.index')->with('success', 'Data Kendaraan Motor berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }
}
