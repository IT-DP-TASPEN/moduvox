<?php

namespace App\Http\Controllers;

use App\Models\Inventaris;
use App\Models\InvMutasi;
use App\Models\MstKantor;
use App\Models\MstLokasi;
use App\Models\MstRuangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InvMutasiController extends Controller
{
    /**
     * Show the form for creating a new mutation.
     */
    public function create(string $inventaris_id)
    {
        $inventaris = Inventaris::with(['kantor', 'ruangan', 'lokasi'])->findOrFail($inventaris_id);
        
        // Cannot mutate depreciated assets easily if the rules forbid it, but usually relocation is fine.
        // We will allow relocation but keep the historical cost intact.

        $kantors = MstKantor::orderBy('kode')->get();
        $lokasis = MstLokasi::orderBy('kode')->get();
        // We don't load ruangans yet, it will be loaded via AJAX based on selected Kantor

        return view('inventaris.mutasi', compact('inventaris', 'kantors', 'lokasis'));
    }

    /**
     * Store a newly created mutation in storage.
     */
    public function store(Request $request, string $inventaris_id)
    {
        $inventaris = Inventaris::findOrFail($inventaris_id);

        $validator = Validator::make($request->all(), [
            'kantor_tujuan_id' => 'required|exists:mst_kantor,id|different:kantor_asal_id_hidden',
            'ruangan_tujuan_id' => 'required|exists:mst_ruangan,id',
            'lokasi_tujuan_id' => 'required|exists:mst_lokasi,id',
            'tgl_mutasi' => 'required|date|before_or_equal:today',
            'keterangan' => 'required|string|max:500',
        ], [
            'kantor_tujuan_id.different' => 'Kantor tujuan tidak boleh sama dengan kantor asal.',
        ]);

        // Add hidden field for validation
        $request->merge(['kantor_asal_id_hidden' => $inventaris->kantor_id]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // 1. Record Mutation History
            InvMutasi::create([
                'inventaris_id' => $inventaris->id,
                'kantor_asal_id' => $inventaris->kantor_id,
                'kantor_tujuan_id' => $request->kantor_tujuan_id,
                'tgl_mutasi' => $request->tgl_mutasi,
                'keterangan' => $request->keterangan,
                // Audit fields handled by trait
            ]);

            // 2. Update Asset Location
            $inventaris->update([
                'kantor_id' => $request->kantor_tujuan_id,
                'ruangan_id' => $request->ruangan_tujuan_id,
                'lokasi_id' => $request->lokasi_tujuan_id,
            ]);

            DB::commit();

            return redirect()->route('inventaris.show', $inventaris->id)
                ->with('success', 'Aset berhasil dimutasi ke lokasi baru.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage())
                ->withInput();
        }
    }
}
