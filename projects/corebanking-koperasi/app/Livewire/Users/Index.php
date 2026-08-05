<?php

namespace App\Livewire\Users;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Company;
use App\Models\Branch;
use App\Traits\WithLogout;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Auth;

use App\Traits\ApprovesActions;

class Index extends Component
{
    use WithPagination, WithLogout, LogsActivity, ApprovesActions;

    public $search = '';
    
    // For Creating/Editing User
    public $showCreateModal = false;
    public $showEditModal = false;
    public $userId; // For editing

    // For Custom Confirmation
    public $confirmingDeletion = false;
    public $deletingId = null;
    public $deletingName = '';

    public function confirmDelete($id, $name)
    {
        $this->deletingId = $id;
        $this->deletingName = $name;
        $this->confirmingDeletion = true;
    }

    public function deleteUser()
    {
        $user = User::findOrFail($this->deletingId);
        $username = $user->username;

        // Intercept Action Approval
        $status = $this->interceptAction(
            'users', 
            'DELETE', 
            null,
            $user->id,
            $user->toArray()
        );

        if ($status == 'PENDING') {
            $this->confirmingDeletion = false;
            session()->flash('success', 'Permintaan penghapusan user telah dikirim ke antrean persetujuan.');
            return;
        }

        $user->delete();
        
        $this->logActivity('DELETE', 'Menghapus user: '.$username);
        
        $this->confirmingDeletion = false;
        session()->flash('success', 'User berhasil dihapus.');
    }
    
    public $new_name;
    public $new_email;
    public $new_username;
    public $new_password;
    public $new_role;
    public $company_id;
    public $branch_id;
    public $is_active = true;

    // For sidebar user info
    public $user;
    public $role;
    public $company;
    public $branch;

    public function mount()
    {
        $this->user = Auth::user();
        $this->role = $this->user->getRoleNames()->first() ?? 'No Role';
        $this->company = Company::find($this->user->company_id);
        $this->branch = Branch::find($this->user->branch_id);

        $this->logActivity('NAVIGATE', 'Manajemen User');
    }

    public function saveUser()
    {
        $this->validate([
            'new_name' => 'required|min:3',
            'new_email' => 'required|email|unique:users,email',
            'new_username' => 'required|unique:users,username',
            'new_password' => 'required|min:8',
            'new_role' => 'required',
            'company_id' => 'required',
            'branch_id' => 'required',
        ]);

        $data = [
            'name' => $this->new_name,
            'email' => $this->new_email,
            'username' => $this->new_username,
            'password' => bcrypt($this->new_password),
            'company_id' => $this->company_id,
            'branch_id' => $this->branch_id,
            'is_active' => $this->is_active,
        ];

        // Intercept Action Approval (Role assignment will be handled upon approval in the controller/action logic)
        $status = $this->interceptAction('users', 'CREATE', array_merge($data, ['role' => $this->new_role]));

        if ($status == 'PENDING') {
            $this->reset(['new_name', 'new_email', 'new_username', 'new_password', 'new_role', 'showCreateModal']);
            session()->flash('success', 'Permintaan pendaftaran user baru telah dikirim ke antrean persetujuan.');
            return;
        }

        $user = User::create($data);
        $user->assignRole($this->new_role);
        
        $this->logActivity('CREATE', 'Menambahkan user baru: '.$this->new_username, $user);

        $this->reset(['new_name', 'new_email', 'new_username', 'new_password', 'new_role', 'showCreateModal']);
        session()->flash('success', 'User berhasil ditambahkan.');
    }

    public function editUser($id)
    {
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->new_name = $user->name;
        $this->new_email = $user->email;
        $this->new_username = $user->username;
        $this->company_id = $user->company_id;
        $this->branch_id = $user->branch_id;
        $this->new_role = $user->getRoleNames()->first();
        $this->is_active = $user->is_active;
        
        $this->showEditModal = true;
    }

    public function updateUser()
    {
        $this->validate([
            'new_name' => 'required|min:3',
            'new_email' => 'required|email|unique:users,email,'.$this->userId,
            'new_username' => 'required|unique:users,username,'.$this->userId,
            'new_role' => 'required',
            'company_id' => 'required',
            'branch_id' => 'required',
        ]);

        $user = User::findOrFail($this->userId);
        $data = [
            'name' => $this->new_name,
            'email' => $this->new_email,
            'username' => $this->new_username,
            'company_id' => $this->company_id,
            'branch_id' => $this->branch_id,
            'is_active' => $this->is_active,
        ];

        if ($this->new_password) {
            $data['password'] = bcrypt($this->new_password);
        }

        // Intercept Action Approval
        $status = $this->interceptAction(
            'users', 
            'UPDATE', 
            array_merge($data, ['role' => $this->new_role]),
            $user->id,
            $user->toArray()
        );

        if ($status == 'PENDING') {
            $this->reset(['new_name', 'new_email', 'new_username', 'new_password', 'new_role', 'showEditModal', 'userId']);
            session()->flash('success', 'Permintaan pembaruan data user telah dikirim ke antrean persetujuan.');
            return;
        }

        $user->update($data);
        $user->syncRoles($this->new_role);
        
        $this->logActivity('UPDATE', 'Memperbarui data user: '.$user->username, $user);

        $this->reset(['new_name', 'new_email', 'new_username', 'new_password', 'new_role', 'showEditModal', 'userId']);
        session()->flash('success', 'User berhasil diperbarui.');
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $user->is_active = !$user->is_active;
        $user->save();
        
        $status = $user->is_active ? 'AKTIF' : 'NON-AKTIF';
        $this->logActivity('STATUS_CHANGE', "Mengubah status user $user->username menjadi $status", $user);
        
        session()->flash('success', 'Status user berhasil diperbarui.');
    }

    public function impersonateUser($id)
    {
        // Cegah impersonate diri sendiri
        if ($id == auth()->id()) {
            session()->flash('error', 'Anda tidak bisa menyamar menjadi diri sendiri.');
            return;
        }

        // Catat identitas asli Admin
        session(['original_impersonator_id' => auth()->id()]);
        
        $targetUser = User::findOrFail($id);
        
        // Login Paksa
        Auth::loginUsingId($id);
        
        $this->logActivity('IMPERSONATE', 'Mulai login menyamar sebagai user: ' . $targetUser->username);

        return redirect()->route('dashboard');
    }

    public function render()
    {
        $users = User::with(['roles', 'company', 'branch'])
            ->where(function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('username', 'like', '%' . $this->search . '%');
            })
            ->paginate(10);

        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
        ];

        return view('livewire.users.index', [
            'usersList' => $users,
            'stats' => $stats,
            'allRoles' => \Spatie\Permission\Models\Role::all(),
            'allCompanies' => Company::all(),
            'allBranches' => Branch::all()
        ])->layout('layouts.app');
    }
}
