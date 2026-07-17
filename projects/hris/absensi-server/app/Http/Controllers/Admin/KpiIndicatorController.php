<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KpiIndicator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KpiIndicatorController extends Controller
{
    public function index()
    {
        $indicators = KpiIndicator::orderBy('sort_order')->get();
        return view('admin.kpi.indicators.index', compact('indicators'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'sort_order' => 'required|integer',
        ]);

        KpiIndicator::create([
            'label' => $request->label,
            'slug' => Str::slug($request->label, '_'),
            'description' => $request->description,
            'sort_order' => $request->sort_order,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Indikator berhasil ditambahkan.');
    }

    public function update(Request $request, KpiIndicator $kpiIndicator)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'sort_order' => 'required|integer',
            'is_active' => 'boolean',
        ]);

        $kpiIndicator->update($request->all());

        return redirect()->back()->with('success', 'Indikator berhasil diperbarui.');
    }

    public function destroy(KpiIndicator $kpiIndicator)
    {
        $kpiIndicator->delete();
        return redirect()->back()->with('success', 'Indikator berhasil dihapus.');
    }
}
