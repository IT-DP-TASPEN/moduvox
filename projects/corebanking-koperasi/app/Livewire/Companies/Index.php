<?php

namespace App\Livewire\Companies;

use Livewire\Component;
use App\Models\Company;
use App\Models\Branch;
use App\Traits\WithLogout;
use Illuminate\Support\Facades\Auth;

use App\Traits\LogsActivity;

use App\Traits\ApprovesActions;

class Index extends Component
{
    use WithLogout, LogsActivity, ApprovesActions;

    public $search = '';
    public $showCreateModal = false;
    public $showEditModal = false;
    public $companyId;

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

    public function deleteCompany()
    {
        $company = Company::findOrFail($this->deletingId);
        $name = $company->company_name;

        // Check for branches 
        if ($company->branches()->count() > 0) {
            session()->flash('error', 'Perusahaan tidak dapat dihapus karena masih memiliki cabang terdaftar.');
            $this->confirmingDeletion = false;
            return;
        }

        // Intercept Action Approval
        $status = $this->interceptAction(
            'companies', 
            'DELETE', 
            null,
            $company->id,
            $company->toArray()
        );

        if ($status == 'PENDING') {
            $this->confirmingDeletion = false;
            session()->flash('success', 'Permintaan penghapusan perusahaan telah dikirim ke antrean persetujuan.');
            return;
        }

        $company->delete();
        $this->logActivity('DELETE', 'Menghapus perusahaan: '.$name);
        
        $this->confirmingDeletion = false;
        session()->flash('success', 'Perusahaan berhasil dihapus.');
    }

    public $new_name;
    public $company_code;
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

        $this->logActivity('NAVIGATE', 'Manajemen Perusahaan');
    }

    public function toggleStatus($id)
    {
        $company = Company::findOrFail($id);
        
        // If trying to deactivate
        if ($company->is_active) {
            $activeBranchesCount = $company->branches()->where('is_active', true)->count();
            if ($activeBranchesCount > 0) {
                session()->flash('error', 'Perusahaan tidak dapat dinonaktifkan karena masih memiliki '.$activeBranchesCount.' cabang yang aktif.');
                return;
            }
        }

        $company->is_active = !$company->is_active;
        $company->save();

        $status = $company->is_active ? 'MENGAKTIFKAN' : 'MENONAKTIFKAN';
        $this->logActivity($status, 'Mengubah status perusahaan '.$company->company_name.' menjadi '.($company->is_active ? 'Aktif' : 'Nonaktif'), $company);
        
        session()->flash('success', 'Status perusahaan berhasil diperbarui.');
    }

    public function saveCompany()
    {
        $this->validate([
            'new_name' => 'required|min:3|unique:companies,company_name',
            'company_code' => 'required|unique:companies,company_code',
        ]);

        $data = [
            'company_name' => $this->new_name,
            'company_code' => $this->company_code,
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
        $status = $this->interceptAction('companies', 'CREATE', $data);

        if ($status == 'PENDING') {
            $this->reset(['new_name', 'company_code', 'is_active', 'address', 'phone_telp', 'phone_wa', 'social_twitter', 'social_ig', 'description', 'showCreateModal']);
            session()->flash('success', 'Permintaan pendaftaran perusahaan baru telah dikirim ke antrean persetujuan.');
            return;
        }

        $company = Company::create($data);

        $this->logActivity('CREATE', 'Menambahkan perusahaan baru: '.$this->new_name, $company);

        $this->reset(['new_name', 'company_code', 'is_active', 'address', 'phone_telp', 'phone_wa', 'social_twitter', 'social_ig', 'description', 'showCreateModal']);
        session()->flash('success', 'Perusahaan berhasil ditambahkan.');
    }

    public function editCompany($id)
    {
        $company = Company::findOrFail($id);
        $this->companyId = $company->id;
        $this->new_name = $company->company_name;
        $this->company_code = $company->company_code;
        $this->is_active = $company->is_active;
        $this->address = $company->address;
        $this->phone_telp = $company->phones['telepon'] ?? '';
        $this->phone_wa = $company->phones['whatsapp'] ?? '';
        $this->social_twitter = $company->social_media['twitter'] ?? '';
        $this->social_ig = $company->social_media['instagram'] ?? '';
        $this->description = $company->description;
        
        $this->showEditModal = true;
    }

    public function updateCompany()
    {
        $this->validate([
            'new_name' => 'required|min:3|unique:companies,company_name,'.$this->companyId,
            'company_code' => 'required|unique:companies,company_code,'.$this->companyId,
        ]);

        $company = Company::findOrFail($this->companyId);

        // Logic check for deactivation
        if ($company->is_active && !$this->is_active) {
            $activeBranchesCount = $company->branches()->where('is_active', true)->count();
            if ($activeBranchesCount > 0) {
                session()->flash('error', 'Perusahaan tidak dapat dinonaktifkan karena masih memiliki '.$activeBranchesCount.' cabang yang aktif.');
                return;
            }
        }

        $data = [
            'company_name' => $this->new_name,
            'company_code' => $this->company_code,
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
            'companies', 
            'UPDATE', 
            $data,
            $company->id,
            $company->toArray()
        );

        if ($status == 'PENDING') {
            $this->reset(['new_name', 'company_code', 'is_active', 'address', 'phone_telp', 'phone_wa', 'social_twitter', 'social_ig', 'description', 'showEditModal', 'companyId']);
            session()->flash('success', 'Permintaan pembaruan data perusahaan telah dikirim ke antrean persetujuan.');
            return;
        }

        $company->update($data);

        $this->logActivity('UPDATE', 'Memperbarui data perusahaan: '.$this->new_name, $company);

        $this->reset(['new_name', 'company_code', 'is_active', 'address', 'phone_telp', 'phone_wa', 'social_twitter', 'social_ig', 'description', 'showEditModal', 'companyId']);
        session()->flash('success', 'Perusahaan berhasil diperbarui.');
    }

    public function render()
    {
        $companies = Company::where('company_name', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);

        return view('livewire.companies.index', [
            'companiesList' => $companies,
            'stats' => [
                'total' => Company::count(),
            ]
        ])->layout('layouts.app');
    }
}
