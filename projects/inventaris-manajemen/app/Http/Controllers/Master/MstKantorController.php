<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MstKantor;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MstKantorController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return view('master.kantor.index');
    }

    public function data(Request $request)
    {
        $query = MstKantor::query();

        // Handle Search
        if (!empty($request->input('search.value'))) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        // Handle Order
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
            'kode' => 'required|string|max:10|unique:mst_kantor,kode',
            'nama' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->error('Validasi gagal', 422, $validator->errors());
        }

        $kantor = MstKantor::create($request->all());

        return $this->success($kantor, 'Data Kantor Cabang berhasil ditambahkan');
    }

    public function show(string $id)
    {
        $kantor = MstKantor::findOrFail($id);
        return $this->success($kantor);
    }

    public function update(Request $request, string $id)
    {
        $kantor = MstKantor::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'kode' => 'required|string|max:10|unique:mst_kantor,kode,' . $id,
            'nama' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->error('Validasi gagal', 422, $validator->errors());
        }

        $kantor->update($request->all());

        return $this->success($kantor, 'Data Kantor Cabang berhasil diperbarui');
    }

    public function destroy(string $id)
    {
        $kantor = MstKantor::findOrFail($id);
        
        // Prevent deletion if in use
        if ($kantor->inventaris()->count() > 0) {
            return $this->error('Tidak dapat menghapus data yang sedang digunakan oleh Aset', 400);
        }

        $kantor->delete();

        return $this->success(null, 'Data Kantor Cabang berhasil dihapus');
    }
}
