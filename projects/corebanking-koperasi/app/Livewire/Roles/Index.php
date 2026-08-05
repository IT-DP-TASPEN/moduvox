<?php

namespace App\Livewire\Roles;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Company;
use App\Models\Branch;
use App\Traits\WithLogout;
use Illuminate\Support\Facades\Auth;
use App\Traits\LogsActivity;

use App\Traits\ApprovesActions;

class Index extends Component
{
    use WithLogout, LogsActivity, ApprovesActions;

    // For CRUD & Search
    public $search = '';
    public $showCreateModal = false;
    public $showEditModal = false;
    public $editingRoleId = null;
    public $new_name = '';
    public $is_active = true;

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

    public function toggleStatus($id)
    {
        $role = Role::findById($id);
        $role->is_active = !$role->is_active;
        $role->save();

        $status = $role->is_active ? 'MENGAKTIFKAN' : 'MENONAKTIFKAN';
        $this->logActivity($status, 'Mengubah status role ' . $role->name . ' menjadi ' . ($role->is_active ? 'Aktif' : 'Nonaktif'), $role);

        session()->flash('success', 'Status role berhasil diperbarui.');
    }

    public function editRole($id)
    {
        $role = Role::findById($id);
        $this->editingRoleId = $id;
        $this->new_name = $role->name;
        $this->is_active = $role->is_active;
        $this->showEditModal = true;
    }

    public function updateRole()
    {
        $this->validate([
            'new_name' => 'required|string|max:255|unique:roles,name,' . $this->editingRoleId
        ]);

        $role = Role::findById($this->editingRoleId);
        $data = [
            'name' => $this->new_name,
            'is_active' => $this->is_active,
        ];

        // Intercept Action Approval
        $status = $this->interceptAction(
            'roles',
            'UPDATE',
            $data,
            $role->id,
            $role->toArray()
        );

        if ($status == 'PENDING') {
            $this->showEditModal = false;
            $this->editingRoleId = null;
            $this->new_name = '';
            $this->is_active = true;
            session()->flash('success', 'Permintaan perubahan role telah dikirim ke antrean persetujuan.');
            return;
        }

        $role->name = $this->new_name;
        $role->is_active = $this->is_active;
        $role->save();

        $this->logActivity('UPDATE', 'Memperbarui role: ' . $this->new_name, $role);

        $this->showEditModal = false;
        $this->editingRoleId = null;
        $this->new_name = '';
        $this->is_active = true;

        session()->flash('success', 'Role berhasil diperbarui.');
    }

    public function deleteRole()
    {
        $role = Role::findById($this->deletingId);
        $name = $role->name;

        // Prevent deleting core roles if needed
        if ($name === 'Admin') {
            session()->flash('error', 'Role Admin tidak dapat dihapus.');
            $this->confirmingDeletion = false;
            return;
        }

        // Intercept Action Approval
        $status = $this->interceptAction(
            'roles',
            'DELETE',
            null,
            $role->id,
            $role->toArray()
        );

        if ($status == 'PENDING') {
            $this->confirmingDeletion = false;
            session()->flash('success', 'Permintaan penghapusan role telah dikirim ke antrean persetujuan.');
            return;
        }

        $role->delete();
        $this->logActivity('DELETE', 'Menghapus role: ' . $name);

        $this->confirmingDeletion = false;
        session()->flash('success', 'Role ' . $name . ' berhasil dihapus.');
    }

    // For Permission Management
    public $showPermissionsModal = false;
    public $selectedRole;
    public $rolePermissions = [];

    public $categories = [];

    protected function loadCategories()
    {
        $permissions = Permission::all();

        // Complete mapping: permission prefix → human readable group name
        $mapping = [
            // ── System ──────────────────────────────────────
            'users'            => '🔑 Manajemen User',
            'roles'            => '🔑 Role & Permission',
            'dashboard'        => '🔑 Dashboard',
            'profile'          => '🔑 Profil Pengguna',
            'branches'         => '🔑 Manajemen Cabang',
            'companies'        => '🔑 Manajemen Perusahaan',
            'menus'            => '🔑 Manajemen Menu',
            'manage'           => '🔑 Pengaturan Persetujuan',
            'view'             => '🔑 Kotak Masuk Persetujuan',
            'approvals'        => '🔑 Kotak Masuk Persetujuan',
            'settings'         => '🔑 Pengaturan Persetujuan',
            'logs'             => '🔑 Audit Log',
            'audit-logs'       => '🔑 Audit Log',
            'auditlogs'        => '🔑 Audit Log',
            'reports'          => '🔑 Pusat Laporan',

            // ── Master Data ──────────────────────────────────
            'marketing-masters' => '📋 Data Marketing',
            'provinces'        => '🗺️ Provinsi',
            'cities'           => '🗺️ Kota/Kabupaten',
            'districts'        => '🗺️ Kecamatan',
            'subdistricts'     => '🗺️ Kelurahan',
            'postalcodes'      => '🗺️ Kode Pos',

            // ── Master Produk ────────────────────────────────
            'saving-products'  => '📦 Produk Simpanan',
            'savingproducts'   => '📦 Produk Simpanan',
            'deposit-products' => '📦 Produk Simpanan Berjangka',
            'depositproducts'  => '📦 Produk Simpanan Berjangka',
            'loan-products'    => '📦 Produk Kredit',
            'loanproducts'     => '📦 Produk Kredit',

            // ── CIF ──────────────────────────────────────────
            'cifs'             => '👤 Manajemen CIF',
            'mobile-access'    => '👤 Manajemen CIF',
            'mobile_access'    => '👤 Manajemen CIF',

            // ── Simpanan ─────────────────────────────────────
            'savings'          => '💰 Layanan Simpanan',

            // ── Deposito ─────────────────────────────────────
            'deposits'         => '🏦 Layanan Simpanan Berjangka',
            'deposit-bilyets'  => '🏦 Manajemen Bilyet Simpanan Berjangka',
            'depositbilyets'   => '🏦 Manajemen Bilyet Simpanan Berjangka',

            // ── Kredit ───────────────────────────────────────
            'loans'            => '💳 Layanan Pinjaman (Kredit)',
            'loan-accounts'    => '💳 Layanan Pinjaman (Kredit)',

            // ── Akuntansi ────────────────────────────────────
            'coa'              => '📒 Bagan Akun (CoA)',
            'journals'         => '📒 Jurnal Umum',
            'ledger'           => '📒 Buku Besar',
            'trial-balance'    => '📒 Neraca Saldo',
            'trialbalance'     => '📒 Neraca Saldo',

            // ── Aset & Sewa ──────────────────────────────────
            'assets'           => '🏢 Aset & Sewa',
            'asset-rentals'    => '🏢 Aset & Sewa',
            'rekanan'          => '🏢 Aset & Sewa',
        ];

        $categories = [];
        foreach ($permissions as $permission) {
            $parts  = explode('.', $permission->name);
            $prefix = $parts[0];

            $groupName = $mapping[$prefix] ?? '⚙️ ' . \Illuminate\Support\Str::title(str_replace(['-', '_'], ' ', $prefix));

            $categories[$groupName][] = $permission->name;
        }

        // Sort by group name so emojis bring consistent ordering
        ksort($categories);

        $this->categories = $categories;
    }

    // For Sidebar
    public $user;
    public $roleName;
    public $company;
    public $branch;

    public function mount()
    {
        $this->user = Auth::user();
        $this->roleName = $this->user->getRoleNames()->first() ?? 'No Role';
        $this->company = Company::find($this->user->company_id);
        $this->branch = Branch::find($this->user->branch_id);

        $this->loadCategories();
        $this->logActivity('NAVIGATE', 'Manajemen Role');
    }

    public function managePermissions($roleId)
    {
        $this->selectedRole = Role::findById($roleId);
        $this->rolePermissions = $this->selectedRole->permissions->pluck('name')->toArray();
        $this->showPermissionsModal = true;
    }

    public function toggleCategory($categoryName)
    {
        $perms = $this->categories[$categoryName];
        $intersect = array_intersect($perms, $this->rolePermissions);
        $allSelected = count($intersect) === count($perms);

        if ($allSelected) {
            $this->selectedRole->revokePermissionTo($perms);
            $this->rolePermissions = array_diff($this->rolePermissions, $perms);
        } else {
            foreach ($perms as $p) {
                \Spatie\Permission\Models\Permission::findOrCreate($p);
            }
            $this->selectedRole->givePermissionTo($perms);
            $this->rolePermissions = array_unique(array_merge($this->rolePermissions, $perms));
        }

        $this->logActivity('TOGGLE_CATEGORY', ($allSelected ? 'Mencabut' : 'Memberikan') . ' semua izin kategori ' . $categoryName . ' untuk role ' . $this->selectedRole->name, $this->selectedRole);
        session()->flash('success', 'Izin kategori ' . $categoryName . ' diperbarui.');
    }

    public function toggleAllPermissions()
    {
        $allPerms = [];
        foreach ($this->categories as $perms) {
            $allPerms = array_merge($allPerms, $perms);
        }
        $allPerms = array_unique($allPerms);

        $intersect = array_intersect($allPerms, $this->rolePermissions);
        $allSelected = count($intersect) === count($allPerms);

        if ($allSelected) {
            $this->selectedRole->revokePermissionTo($allPerms);
            $this->rolePermissions = array_diff($this->rolePermissions, $allPerms);
            $action = 'Mencabut';
        } else {
            foreach ($allPerms as $p) {
                \Spatie\Permission\Models\Permission::findOrCreate($p);
            }
            $this->selectedRole->givePermissionTo($allPerms);
            $this->rolePermissions = array_unique(array_merge($this->rolePermissions, $allPerms));
            $action = 'Memberikan';
        }

        $this->logActivity('TOGGLE_ALL_PERMISSIONS', $action . ' seluruh izin sistem untuk role ' . $this->selectedRole->name, $this->selectedRole);
        session()->flash('success', 'Seluruh izin sistem telah diperbarui.');
    }

    public function saveRole()
    {
        $this->validate([
            'new_name' => 'required|string|unique:roles,name|max:255'
        ]);

        $data = [
            'name' => $this->new_name,
            'is_active' => $this->is_active,
        ];

        // Intercept Action Approval
        $status = $this->interceptAction('roles', 'CREATE', $data);

        if ($status == 'PENDING') {
            $this->new_name = '';
            $this->is_active = true;
            $this->showCreateModal = false;
            session()->flash('success', 'Permintaan pendaftaran role baru telah dikirim ke antrean persetujuan.');
            return;
        }

        $role = Role::create($data);

        $this->logActivity('CREATE', 'Membuat role baru: ' . $this->new_name, $role);

        $this->new_name = '';
        $this->is_active = true;
        $this->showCreateModal = false;
        session()->flash('success', 'Role berhasil ditambahkan.');
    }

    public function togglePermission($permissionName)
    {
        if (in_array($permissionName, $this->rolePermissions)) {
            $this->selectedRole->revokePermissionTo($permissionName);
            $this->rolePermissions = array_diff($this->rolePermissions, [$permissionName]);
            $this->logActivity('REVOKE_PERMISSION', 'Mencabut izin ' . $permissionName . ' dari role ' . $this->selectedRole->name, $this->selectedRole);
        } else {
            $this->selectedRole->givePermissionTo($permissionName);
            $this->rolePermissions[] = $permissionName;
            $this->logActivity('GIVE_PERMISSION', 'Memberikan izin ' . $permissionName . ' ke role ' . $this->selectedRole->name, $this->selectedRole);
        }

        session()->flash('success', 'Izin berhasil diperbarui.');
    }

    public function render()
    {
        $roles = Role::with('permissions')
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->get();

        $allPermissions = \Spatie\Permission\Models\Permission::all();

        return view('livewire.roles.index', [
            'rolesList' => $roles,
            'allPermissions' => $allPermissions
        ])->layout('layouts.app');
    }
}
