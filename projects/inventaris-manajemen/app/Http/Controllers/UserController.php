<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\MstKantor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    use \App\Traits\ApiResponse;

    public function index()
    {
        $kantors = MstKantor::orderBy('kode')->get();
        $roles = Role::all();
        return view('system.users.index', compact('kantors', 'roles'));
    }

    public function data(Request $request)
    {
        $query = User::with(['kantor', 'roles']);

        if (!empty($request->input('search.value'))) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $columns = ['name', 'email', 'kantor_id'];
        if ($request->has('order')) {
            $order = $request->input('order.0');
            $columnIdx = intval($order['column']);
            if (isset($columns[$columnIdx])) {
                $query->orderBy($columns[$columnIdx], $order['dir']);
            }
        } else {
            $query->latest();
        }

        return $this->datatableResponse($query, $request, function($item) {
            $item->nama_kantor = $item->kantor ? $item->kantor->nama : 'Pusat (Tanpa Cabang)';
            $item->role_names = $item->roles->pluck('name')->implode(', ');
            return $item;
        });
    }

    public function show(string $id)
    {
        $user = User::with('roles')->findOrFail($id);
        $user->role = $user->roles->first() ? $user->roles->first()->name : '';
        return $this->success($user);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'kantor_id' => 'nullable|exists:mst_kantor,id',
            'role' => 'required|exists:roles,name',
        ]);

        if ($validator->fails()) {
            return $this->error('Validasi gagal', 422, $validator->errors());
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'kantor_id' => $request->kantor_id,
        ]);

        $user->assignRole($request->role);

        return $this->success($user, 'User berhasil ditambahkan');
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
            'kantor_id' => 'nullable|exists:mst_kantor,id',
            'role' => 'required|exists:roles,name',
        ]);

        if ($validator->fails()) {
            return $this->error('Validasi gagal', 422, $validator->errors());
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'kantor_id' => $request->kantor_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        $user->syncRoles([$request->role]);

        return $this->success($user, 'User berhasil diperbarui');
    }

    public function destroy(string $id)
    {
        if (auth()->id() == $id) {
            return $this->error('Tidak bisa menghapus akun Anda sendiri', 400);
        }

        $user = User::findOrFail($id);
        $user->delete();

        return $this->success(null, 'User berhasil dihapus');
    }
}
