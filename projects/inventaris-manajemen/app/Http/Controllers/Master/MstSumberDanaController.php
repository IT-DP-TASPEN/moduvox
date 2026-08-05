<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MstSumberDana;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MstSumberDanaController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return view('master.sumber-dana.index');
    }

    public function data(Request $request)
    {
        $query = MstSumberDana::query();

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
            'kode' => 'required|string|max:10|unique:mst_sumber_dana,kode',
            'nama' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->error('Validasi gagal', 422, $validator->errors());
        }

        $sumber_dana = MstSumberDana::create($request->all());

        return $this->success($sumber_dana, 'Data Sumber Dana berhasil ditambahkan');
    }

    public function show(string $id)
    {
        $sumber_dana = MstSumberDana::findOrFail($id);
        return $this->success($sumber_dana);
    }

    public function update(Request $request, string $id)
    {
        $sumber_dana = MstSumberDana::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'kode' => 'required|string|max:10|unique:mst_sumber_dana,kode,' . $id,
            'nama' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->error('Validasi gagal', 422, $validator->errors());
        }

        $sumber_dana->update($request->all());

        return $this->success($sumber_dana, 'Data Sumber Dana berhasil diperbarui');
    }

    public function destroy(string $id)
    {
        $sumber_dana = MstSumberDana::findOrFail($id);
        
        if ($sumber_dana->inventaris()->count() > 0) {
            return $this->error('Tidak dapat menghapus data yang sedang digunakan oleh Aset', 400);
        }

        $sumber_dana->delete();

        return $this->success(null, 'Data Sumber Dana berhasil dihapus');
    }
}
