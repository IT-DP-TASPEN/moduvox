<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('office')->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function edit(User $user)
    {
        $offices = \App\Models\Office::all();
        return view('admin.users.edit', compact('user', 'offices'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'office_id' => 'nullable|exists:offices,id',
        ]);

        $user->update([
            'office_id' => $request->office_id,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Lokasi kantor karyawan berhasil diupdate');
    }
}
