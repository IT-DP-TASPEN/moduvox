<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Division;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    public function index()
    {
        $items = Division::orderBy('name')->paginate(20);
        return view('admin.divisions.index', compact('items'));
    }

    public function create()
    {
        return view('admin.divisions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:divisions,code',
            'name' => 'required|string|max:255',
        ]);

        Division::create($validated);

        return redirect()->route('admin.divisions.index')->with('success', 'Master divisi berhasil ditambahkan');
    }

    public function edit(Division $division)
    {
        return view('admin.divisions.edit', compact('division'));
    }

    public function update(Request $request, Division $division)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:divisions,code,' . $division->id,
            'name' => 'required|string|max:255',
        ]);

        $division->update($validated);

        return redirect()->route('admin.divisions.index')->with('success', 'Master divisi berhasil diupdate');
    }

    public function destroy(Division $division)
    {
        $division->delete();
        return redirect()->route('admin.divisions.index')->with('success', 'Master divisi berhasil dihapus');
    }
}
