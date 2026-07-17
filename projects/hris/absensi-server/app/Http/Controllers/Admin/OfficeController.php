<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Office;
use Illuminate\Http\Request;

class OfficeController extends Controller
{
    public function index()
    {
        $offices = Office::all();
        return view('admin.offices.index', compact('offices'));
    }

    public function create()
    {
        return view('admin.offices.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:offices',
            'name' => 'required',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'required|integer',
        ]);

        Office::create($request->all());
        return redirect()->route('admin.offices.index')->with('success', 'Kantor berhasil ditambahkan');
    }

    public function edit(Office $office)
    {
        return view('admin.offices.edit', compact('office'));
    }

    public function update(Request $request, Office $office)
    {
        $request->validate([
            'code' => 'required|unique:offices,code,' . $office->id,
            'name' => 'required',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'required|integer',
        ]);

        $office->update($request->all());
        return redirect()->route('admin.offices.index')->with('success', 'Kantor berhasil diupdate');
    }

    public function destroy(Office $office)
    {
        $office->delete();
        return redirect()->route('admin.offices.index')->with('success', 'Kantor berhasil dihapus');
    }
}
