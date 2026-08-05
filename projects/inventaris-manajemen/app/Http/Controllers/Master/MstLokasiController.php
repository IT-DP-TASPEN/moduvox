<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MstLokasi;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MstLokasiController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return view('master.lokasi.index');
    }

    public function data(Request $request)
    {
        $query = MstLokasi::query();

        if (!empty($request->input('search.value'))) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        $columns = ['kode', 'nama'];
        if ($request->has('order')) {
            $order = $request->input('order.0');
            $columnIdx = intval($order['column']);
            if (isset($columns[$columnIdx])) {
                $query->orderBy($columns[$columnIdx], $order['dir']);
            }
        } else {
            $query->latest();
        }

        return $this->datatableResponse($query, $request);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode' => 'required|string|max:10|unique:mst_lokasi,kode',
            'nama' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->error('Validasi gagal', 422, $validator->errors());
        }

        $lokasi = MstLokasi::create($request->all());

        return $this->success($lokasi, 'Data Lokasi berhasil ditambahkan');
    }

    public function show(string $id)
    {
        $lokasi = MstLokasi::findOrFail($id);
        return $this->success($lokasi);
    }

    public function update(Request $request, string $id)
    {
        $lokasi = MstLokasi::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'kode' => 'required|string|max:10|unique:mst_lokasi,kode,' . $id,
            'nama' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->error('Validasi gagal', 422, $validator->errors());
        }

        $lokasi->update($request->all());

        return $this->success($lokasi, 'Data Lokasi berhasil diperbarui');
    }

    public function destroy(string $id)
    {
        $lokasi = MstLokasi::findOrFail($id);
        
        if ($lokasi->inventaris()->count() > 0) {
            return $this->error('Tidak dapat menghapus data yang sedang digunakan oleh Aset', 400);
        }

        $lokasi->delete();

        return $this->success(null, 'Data Lokasi berhasil dihapus');
    }
}
