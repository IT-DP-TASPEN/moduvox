<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialMedia;
use Illuminate\Http\Request;

class SocialMediaController extends Controller
{
    public function index()
    {
        $socials = SocialMedia::orderBy('order')->get();
        return view('admin.socials.index', compact('socials'));
    }

    public function create()
    {
        return view('admin.socials.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'icon'      => 'required|string|max:255',
            'url'       => 'required|url|max:255',
            'is_active' => 'boolean',
            'order'     => 'integer',
        ]);

        SocialMedia::create($data);

        return redirect()->route('admin.socials.index')->with('success', 'Data media sosial berhasil ditambahkan.');
    }

    public function edit(SocialMedia $social)
    {
        return view('admin.socials.edit', compact('social'));
    }

    public function update(Request $request, SocialMedia $social)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'icon'      => 'required|string|max:255',
            'url'       => 'required|url|max:255',
            'is_active' => 'boolean',
            'order'     => 'integer',
        ]);

        $data['is_active'] = $request->has('is_active');

        $social->update($data);

        return redirect()->route('admin.socials.index')->with('success', 'Data media sosial berhasil diperbarui.');
    }

    public function destroy(SocialMedia $social)
    {
        $social->delete();
        return redirect()->route('admin.socials.index')->with('success', 'Data media sosial berhasil dihapus.');
    }
}
