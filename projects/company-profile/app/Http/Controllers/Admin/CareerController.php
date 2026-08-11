<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Career;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CareerController extends Controller
{
    public function index()
    {
        $careers = Career::latest()->paginate(10);
        return view('admin.careers.index', compact('careers'));
    }

    public function create()
    {
        return view('admin.careers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'location'    => 'nullable|string|max:255',
            'type'        => 'required|string|max:255',
            'description' => 'required|string',
            'requirements'=> 'nullable|string',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->title) . '-' . time();
        $data['is_active'] = $request->has('is_active');

        Career::create($data);

        return redirect()->route('admin.careers.index')->with('success', 'Lowongan kerja berhasil ditambahkan.');
    }

    public function edit(Career $career)
    {
        return view('admin.careers.edit', compact('career'));
    }

    public function update(Request $request, Career $career)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'location'    => 'nullable|string|max:255',
            'type'        => 'required|string|max:255',
            'description' => 'required|string',
            'requirements'=> 'nullable|string',
        ]);

        $data = $request->all();
        if ($career->title !== $request->title) {
            $data['slug'] = Str::slug($request->title) . '-' . time();
        }
        $data['is_active'] = $request->has('is_active');

        $career->update($data);

        return redirect()->route('admin.careers.index')->with('success', 'Lowongan kerja berhasil diperbarui.');
    }

    public function destroy(Career $career)
    {
        $career->delete();
        return redirect()->route('admin.careers.index')->with('success', 'Lowongan kerja berhasil dihapus.');
    }

    public function toggle(Career $career)
    {
        $career->update(['is_active' => !$career->is_active]);
        return back()->with('success', 'Status lowongan berhasil diubah.');
    }
}
