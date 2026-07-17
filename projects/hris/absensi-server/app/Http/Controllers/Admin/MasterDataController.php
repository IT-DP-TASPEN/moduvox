<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GapokMaster;
use App\Models\HonorariumMaster;

class MasterDataController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'gapok');
        $filterGrade = $request->query('grade');

        $gapokQuery = GapokMaster::orderBy('grade')->orderBy('skg');
        if ($filterGrade) {
            $gapokQuery->where('grade', $filterGrade);
        }
        $gapokData = $gapokQuery->get();

        $honorData = HonorariumMaster::orderBy('position_name')->orderBy('level')->get();

        // Group gapok by grade for better display
        $gapokGrouped = $gapokData->groupBy('grade');

        return view('admin.master-data.index', compact('gapokData', 'honorData', 'gapokGrouped', 'tab', 'filterGrade'));
    }

    // ── GAPOK (Gaji Pokok) CRUD ──

    public function storeGapok(Request $request)
    {
        $request->validate([
            'skg' => 'required|integer|min:1|max:30',
            'grade' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        // Prevent duplicate
        $exists = GapokMaster::where('skg', $request->skg)->where('grade', $request->grade)->exists();
        if ($exists) {
            return redirect()->back()->with('error', 'Data SKG ' . $request->skg . ' Golongan ' . $request->grade . ' sudah ada.');
        }

        GapokMaster::create($request->only('skg', 'grade', 'amount'));
        return redirect()->route('admin.master-data.index', ['tab' => 'gapok'])->with('success', 'Data Gaji Pokok berhasil ditambahkan.');
    }

    public function updateGapok(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $gapok = GapokMaster::findOrFail($id);
        $gapok->update(['amount' => $request->amount]);

        return redirect()->route('admin.master-data.index', ['tab' => 'gapok'])->with('success', 'Data Gaji Pokok berhasil diperbarui.');
    }

    public function destroyGapok($id)
    {
        GapokMaster::findOrFail($id)->delete();
        return redirect()->route('admin.master-data.index', ['tab' => 'gapok'])->with('success', 'Data Gaji Pokok berhasil dihapus.');
    }

    // ── HONORARIUM CRUD ──

    public function storeHonorarium(Request $request)
    {
        $request->validate([
            'position_name' => 'required|string|max:255',
            'level' => 'required|in:MUDA,MADYA,UTAMA',
            'amount' => 'required|numeric|min:0',
        ]);

        $exists = HonorariumMaster::where('position_name', $request->position_name)->where('level', $request->level)->exists();
        if ($exists) {
            return redirect()->back()->with('error', 'Data Honorarium ' . $request->position_name . ' level ' . $request->level . ' sudah ada.');
        }

        HonorariumMaster::create($request->only('position_name', 'level', 'amount'));
        return redirect()->route('admin.master-data.index', ['tab' => 'honorarium'])->with('success', 'Data Honorarium berhasil ditambahkan.');
    }

    public function updateHonorarium(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $honor = HonorariumMaster::findOrFail($id);
        $honor->update(['amount' => $request->amount]);

        return redirect()->route('admin.master-data.index', ['tab' => 'honorarium'])->with('success', 'Data Honorarium berhasil diperbarui.');
    }

    public function destroyHonorarium($id)
    {
        HonorariumMaster::findOrFail($id)->delete();
        return redirect()->route('admin.master-data.index', ['tab' => 'honorarium'])->with('success', 'Data Honorarium berhasil dihapus.');
    }
}
