<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MstRuangan;
use App\Models\MstKantor;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MstRuanganController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $kantors = MstKantor::orderBy('kode')->get();
        return view('master.ruangan.index', compact('kantors'));
    }

    public function data(Request $request)
    {
        $query = MstRuangan::with('kantor');

        if (!empty($request->input('search.value'))) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%")
                  ->orWhereHas('kantor', function($qKantor) use ($search) {
                      $qKantor->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        $columns = ['kode', 'nama', 'kantor_id']; // Approximate sorting for relation
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
            return $item;
        });
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode' => 'required|string|max:10|unique:mst_ruangan,kode',
            'nama' => 'required|string|max:100',
            'kantor_id' => 'required|exists:mst_kantor,id',
        ]);

        if ($validator->fails()) {
            return $this->error('Validasi gagal', 422, $validator->errors());
        }

        $ruangan = MstRuangan::create($request->all());

        return $this->success($ruangan, 'Data Ruangan berhasil ditambahkan');
    }

    public function show(string $id)
    {
        $ruangan = MstRuangan::findOrFail($id);
        return $this->success($ruangan);
    }

    public function update(Request $request, string $id)
    {
        $ruangan = MstRuangan::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'kode' => 'required|string|max:10|unique:mst_ruangan,kode,' . $id,
            'nama' => 'required|string|max:100',
            'kantor_id' => 'required|exists:mst_kantor,id',
        ]);

        if ($validator->fails()) {
            return $this->error('Validasi gagal', 422, $validator->errors());
        }

        $ruangan->update($request->all());

        return $this->success($ruangan, 'Data Ruangan berhasil diperbarui');
    }

    public function destroy(string $id)
    {
        $ruangan = MstRuangan::findOrFail($id);
        
        if ($ruangan->inventaris()->count() > 0) {
            return $this->error('Tidak dapat menghapus data yang sedang digunakan oleh Aset', 400);
        }

        $ruangan->delete();

        return $this->success(null, 'Data Ruangan berhasil dihapus');
    }
}
