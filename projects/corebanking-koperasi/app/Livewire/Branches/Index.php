<?php

namespace App\Livewire\Branches;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Branch;
use App\Models\Company;
use App\Traits\WithLogout;
use Illuminate\Support\Facades\Auth;

use App\Traits\LogsActivity;

use App\Traits\ApprovesActions;

class Index extends Component
{
    use WithPagination, WithLogout, LogsActivity, ApprovesActions;

    public $search = '';
    public $filter_company = '';
    public $showCreateModal = false;
    public $showEditModal = false;
    public $branchId;

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

    public function deleteBranch()
    {
        $branch = Branch::findOrFail($this->deletingId);
        $name = $branch->name;
        
        // Check if there are users in this branch
        if ($branch->users()->count() > 0) {
            session()->flash('error', 'Cabang tidak dapat dihapus karena masih memiliki user yang terdaftar.');
            $this->confirmingDeletion = false;
            return;
        }

        // Intercept Action Approval
        $status = $this->interceptAction(
            'branches', 
            'DELETE', 
            null,
            $branch->id,
            $branch->toArray()
        );

        if ($status == 'PENDING') {
            $this->confirmingDeletion = false;
            session()->flash('success', 'Permintaan penghapusan cabang telah dikirim ke antrean persetujuan.');
            return;
        }

        $branch->delete();
        $this->logActivity('DELETE', 'Menghapus cabang: '.$name);
        
        $this->confirmingDeletion = false;
        session()->flash('success', 'Cabang berhasil dihapus.');
    }

    public $new_name;
    public $branch_code;
    public $company_id;
    public $is_active = true;
    public $address;
    public $phone_telp;
    public $phone_wa;
    public $social_twitter;
    public $social_ig;
    public $description;

    // For Sidebar
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

        $this->logActivity('NAVIGATE', 'Manajemen Cabang');
    }

    public function toggleStatus($id)
    {
        $branch = Branch::findOrFail($id);
        $branch->is_active = !$branch->is_active;
        $branch->save();

        $status = $branch->is_active ? 'MENGAKTIFKAN' : 'MENONAKTIFKAN';
        $this->logActivity($status, 'Mengubah status cabang '.$branch->name.' menjadi '.($branch->is_active ? 'Aktif' : 'Nonaktif'), $branch);
        
        session()->flash('success', 'Status cabang berhasil diperbarui.');
    }

    public function saveBranch()
    {
        $this->validate([
            'new_name' => 'required|min:3',
            'branch_code' => 'required|unique:branches,branch_code',
            'company_id' => 'required|exists:companies,id',
        ]);

        $data = [
            'name' => $this->new_name,
            'branch_code' => $this->branch_code,
            'company_id' => $this->company_id,
            'is_active' => $this->is_active,
            'address' => $this->address,
            'phones' => [
                'telepon' => $this->phone_telp,
                'whatsapp' => $this->phone_wa,
            ],
            'social_media' => [
                'twitter' => $this->social_twitter,
                'instagram' => $this->social_ig,
            ],
            'description' => $this->description,
        ];

        // Intercept Action Approval
        $status = $this->interceptAction('branches', 'CREATE', $data);

        if ($status == 'PENDING') {
            $this->reset(['new_name', 'branch_code', 'company_id', 'is_active', 'address', 'phone_telp', 'phone_wa', 'social_twitter', 'social_ig', 'description', 'showCreateModal']);
            session()->flash('success', 'Permintaan pendaftaran cabang baru telah dikirim ke antrean persetujuan.');
            return;
        }

        $branch = Branch::create($data);

        $this->logActivity('CREATE', 'Menambahkan cabang baru: '.$this->new_name, $branch);

        $this->reset(['new_name', 'branch_code', 'company_id', 'is_active', 'address', 'phone_telp', 'phone_wa', 'social_twitter', 'social_ig', 'description', 'showCreateModal']);
        session()->flash('success', 'Cabang berhasil ditambahkan.');
    }

    public function editBranch($id)
    {
        $branch = Branch::findOrFail($id);
        $this->branchId = $branch->id;
        $this->new_name = $branch->name;
        $this->branch_code = $branch->branch_code;
        $this->company_id = $branch->company_id;
        $this->is_active = $branch->is_active;
        $this->address = $branch->address;
        $this->phone_telp = $branch->phones['telepon'] ?? '';
        $this->phone_wa = $branch->phones['whatsapp'] ?? '';
        $this->social_twitter = $branch->social_media['twitter'] ?? '';
        $this->social_ig = $branch->social_media['instagram'] ?? '';
        $this->description = $branch->description;
        
        $this->showEditModal = true;
    }

    public function updateBranch()
    {
        $this->validate([
            'new_name' => 'required|min:3',
            'branch_code' => 'required|unique:branches,branch_code,'.$this->branchId,
            'company_id' => 'required|exists:companies,id',
        ]);

        $branch = Branch::findOrFail($this->branchId);
        $data = [
            'name' => $this->new_name,
            'branch_code' => $this->branch_code,
            'company_id' => $this->company_id,
            'is_active' => $this->is_active,
            'address' => $this->address,
            'phones' => [
                'telepon' => $this->phone_telp,
                'whatsapp' => $this->phone_wa,
            ],
            'social_media' => [
                'twitter' => $this->social_twitter,
                'instagram' => $this->social_ig,
            ],
            'description' => $this->description,
        ];

        // Intercept Action Approval
        $status = $this->interceptAction(
            'branches', 
            'UPDATE', 
            $data,
            $branch->id,
            $branch->toArray()
        );

        if ($status == 'PENDING') {
            $this->reset(['new_name', 'branch_code', 'company_id', 'is_active', 'address', 'phone_telp', 'phone_wa', 'social_twitter', 'social_ig', 'description', 'showEditModal', 'branchId']);
            session()->flash('success', 'Permintaan pembaruan data cabang telah dikirim ke antrean persetujuan.');
            return;
        }

        $branch->update($data);

        $this->logActivity('UPDATE', 'Memperbarui data cabang: '.$this->new_name, $branch);

        $this->reset(['new_name', 'branch_code', 'company_id', 'is_active', 'address', 'phone_telp', 'phone_wa', 'social_twitter', 'social_ig', 'description', 'showEditModal', 'branchId']);
        session()->flash('success', 'Cabang berhasil diperbarui.');
    }

    public function render()
    {
        $branches = Branch::with('company')
            ->where(function($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->filter_company, function($query) {
                $query->where('company_id', $this->filter_company);
            })
            ->latest()
            ->paginate(10);

        return view('livewire.branches.index', [
            'branchesList' => $branches,
            'allCompanies' => Company::all(),
            'stats' => [
                'total' => Branch::count(),
            ]
        ])->layout('layouts.app');
    }
}
