<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MstGolongan;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MstGolonganController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return view('master.golongan.index');
    }

    public function data(Request $request)
    {
        $query = MstGolongan::query();

        // Handle Search
        if (!empty($request->input('search.value'))) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        // Handle Order
        $columns = ['kode', 'nama', 'umur_standar', 'akun_debet', 'akun_kredit'];
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
            'kode' => 'required|string|max:10|unique:mst_golongan,kode',
            'nama' => 'required|string|max:100',
            'umur_standar' => 'required|integer|min:0',
            'akun_debet' => 'nullable|string|max:20',
            'akun_kredit' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->error('Validasi gagal', 422, $validator->errors());
        }

        $golongan = MstGolongan::create($request->all());

        return $this->success($golongan, 'Data Golongan Aset berhasil ditambahkan');
    }

    public function show(string $id)
    {
        $golongan = MstGolongan::findOrFail($id);
        return $this->success($golongan);
    }

    public function update(Request $request, string $id)
    {
        $golongan = MstGolongan::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'kode' => 'required|string|max:10|unique:mst_golongan,kode,' . $id,
            'nama' => 'required|string|max:100',
            'umur_standar' => 'required|integer|min:0',
            'akun_debet' => 'nullable|string|max:20',
            'akun_kredit' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->error('Validasi gagal', 422, $validator->errors());
        }

        $golongan->update($request->all());

        return $this->success($golongan, 'Data Golongan Aset berhasil diperbarui');
    }

    public function destroy(string $id)
    {
        $golongan = MstGolongan::findOrFail($id);
        
        // Prevent deletion if in use
        if ($golongan->inventaris()->count() > 0) {
            return $this->error('Tidak dapat menghapus data yang sedang digunakan oleh Aset', 400);
        }

        $golongan->delete();

        return $this->success(null, 'Data Golongan Aset berhasil dihapus');
    }
}
