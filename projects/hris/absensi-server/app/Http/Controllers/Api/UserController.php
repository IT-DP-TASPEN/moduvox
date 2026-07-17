<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserFile;

class UserController extends Controller
{
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|max:10240',
        ]);

        $user = $request->user();
        $file = $request->file('photo');
        
        $path = $file->store('user_files', 'public');
        
        \App\Models\UserFile::updateOrCreate(
            ['user_id' => $user->id, 'file_type' => 'PHOTO'],
            ['name' => $file->getClientOriginalName(), 'file_path' => $path]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Foto profil berhasil diperbarui',
            'photo_url' => url('storage/' . $path)
        ]);
    }

    public function index(Request $request)
    {
        if (!$request->user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $users = \App\Models\User::orderBy('name')->get(['id', 'name', 'employee_id']);
        return response()->json($users);
    }
}
