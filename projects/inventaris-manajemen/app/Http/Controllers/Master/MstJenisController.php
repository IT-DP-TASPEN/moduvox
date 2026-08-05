<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MstJenis;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MstJenisController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return view('master.jenis.index');
    }

    public function data(Request $request)
    {
        $query = MstJenis::query();

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
            'kode' => 'required|string|max:10|unique:mst_jenis,kode',
            'nama' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->error('Validasi gagal', 422, $validator->errors());
        }

        $jenis = MstJenis::create($request->all());

        return $this->success($jenis, 'Data Jenis Aset berhasil ditambahkan');
    }

    public function show(string $id)
    {
        $jenis = MstJenis::findOrFail($id);
        return $this->success($jenis);
    }

    public function update(Request $request, string $id)
    {
        $jenis = MstJenis::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'kode' => 'required|string|max:10|unique:mst_jenis,kode,' . $id,
            'nama' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->error('Validasi gagal', 422, $validator->errors());
        }

        $jenis->update($request->all());

        return $this->success($jenis, 'Data Jenis Aset berhasil diperbarui');
    }

    public function destroy(string $id)
    {
        $jenis = MstJenis::findOrFail($id);
        
        if ($jenis->inventaris()->count() > 0) {
            return $this->error('Tidak dapat menghapus data yang sedang digunakan oleh Aset', 400);
        }

        $jenis->delete();

        return $this->success(null, 'Data Jenis Aset berhasil dihapus');
    }
}
