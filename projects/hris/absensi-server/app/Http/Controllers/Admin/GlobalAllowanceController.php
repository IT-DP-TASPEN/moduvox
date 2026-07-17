<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GlobalAllowance;

class GlobalAllowanceController extends Controller
{
    public function index()
    {
        $allowances = GlobalAllowance::all();
        return view('admin.global_allowances.index', compact('allowances'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'type' => 'required|in:fixed,percentage_gapok',
            'category' => 'required|in:earning,deduction,company_paid',
            'target_status' => 'required|in:Tetap,Kontrak,OJT,PE,All',
        ]);

        GlobalAllowance::create($request->all());

        return redirect()->back()->with('success', 'Komponen global berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $allowance = GlobalAllowance::findOrFail($id);
        return view('admin.global_allowances.edit', compact('allowance'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'type' => 'required|in:fixed,percentage_gapok',
            'category' => 'required|in:earning,deduction,company_paid',
            'target_status' => 'required|in:Tetap,Kontrak,OJT,PE,All',
        ]);

        $allowance = GlobalAllowance::findOrFail($id);
        $allowance->update($request->all());

        return redirect()->route('admin.global-allowances.index')->with('success', 'Komponen global berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $allowance = GlobalAllowance::findOrFail($id);
        $allowance->delete();

        return redirect()->back()->with('success', 'Komponen global berhasil dihapus!');
    }
}
