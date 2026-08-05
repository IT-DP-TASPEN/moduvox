<?php

namespace App\Livewire\MarketingMasters;

use App\Models\MarketingMaster;
use App\Models\Branch;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\ApprovesActions;
use App\Traits\LogsActivity;
use App\Traits\WithLogout;

class Index extends Component
{
    use WithPagination, ApprovesActions, LogsActivity, WithLogout;

    public $search = '';
    public $filter_branch = '';
    public $showModal = false;
    public $editingId = null;

    // Form fields
    public $marketing_code, $name, $phone, $is_active = true, $branch_master_id;

    // For sidebar user info
    public $user, $role;

    public function mount()
    {
        $this->user = auth()->user();
        $this->role = $this->user->getRoleNames()->first() ?? 'No Role';
        $this->logActivity('NAVIGATE', 'Marketing Master');
    }

    protected function rules()
    {
        $rules = [
            'marketing_code' => 'required|string|unique:marketing_masters,marketing_code',
            'name' => 'required|string',
            'phone' => 'nullable|string',
            'is_active' => 'boolean',
            'branch_master_id' => 'required|exists:branches,id'
        ];

        if ($this->editingId) {
            $rules['marketing_code'] = 'required|string|unique:marketing_masters,marketing_code,' . $this->editingId;
        }

        return $rules;
    }

    public function render()
    {
        $query = MarketingMaster::with('branch');
        
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('marketing_code', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filter_branch) {
            $query->where('branch_master_id', $this->filter_branch);
        }

        return view('livewire.marketing-masters.index', [
            'items' => $query->paginate(10),
            'branches' => Branch::where('is_active', true)->get()
        ])->layout('layouts.app');
    }

    public function create()
    {
        $this->reset(['marketing_code', 'name', 'phone', 'is_active', 'branch_master_id', 'editingId']);
        $this->is_active = true;
        // Don't auto-set marketing_code here, do it exactly at save() to avoid sequence collisions
        $this->showModal = true;
    }


    public function edit($id)
    {
        $marketing = MarketingMaster::findOrFail($id);
        $this->editingId = $id;
        $this->marketing_code = $marketing->marketing_code;
        $this->name = $marketing->name;
        $this->phone = $marketing->phone;
        $this->is_active = $marketing->is_active;
        $this->branch_master_id = $marketing->branch_master_id;
        
        $this->showModal = true;
    }

    public function save()
    {
        if (!$this->editingId && $this->branch_master_id) {
            $branch = Branch::find($this->branch_master_id);
            $branchCode = $branch->branch_code ?? 'BR';
            
            $count = MarketingMaster::where('branch_master_id', $this->branch_master_id)->count() + 1;
            $code = $branchCode . str_pad($count, 5, '0', STR_PAD_LEFT);
            
            while(MarketingMaster::where('marketing_code', $code)->exists()) {
                $count++;
                $code = $branchCode . str_pad($count, 5, '0', STR_PAD_LEFT);
            }
            $this->marketing_code = $code;
        }

        $this->validate();

        $data = [
            'marketing_code' => $this->marketing_code,
            'name' => $this->name,
            'phone' => $this->phone,
            'is_active' => $this->is_active,
            'branch_master_id' => $this->branch_master_id,
        ];

        // Intercept Action Approval
        $status = $this->interceptAction(
            'marketing-masters', 
            $this->editingId ? 'UPDATE' : 'CREATE', 
            $data,
            $this->editingId,
            $this->editingId ? MarketingMaster::find($this->editingId)->toArray() : null
        );

        if ($status == 'PENDING') {
            $this->showModal = false;
            session()->flash('success', 'Permintaan perubahan telah dikirim ke antrean persetujuan.');
            return;
        }

        if ($this->editingId) {
            MarketingMaster::find($this->editingId)->update($data);
            $this->logActivity('UPDATE', "Memperbarui Marketing Master {$this->name}");
            session()->flash('success', 'Master Marketing berhasil diperbarui.');
        } else {
            MarketingMaster::create($data);
            $this->logActivity('CREATE', "Membuat Marketing Master {$this->name}");
            session()->flash('success', 'Master Marketing berhasil ditambahkan.');
        }

        $this->showModal = false;
    }

    public function delete($id)
    {
        $item = MarketingMaster::findOrFail($id);
        
        // Intercept Action Approval
        $status = $this->interceptAction(
            'marketing-masters', 
            'DELETE', 
            null,
            $item->id,
            $item->toArray()
        );

        if ($status == 'PENDING') {
            session()->flash('success', 'Permintaan penghapusan telah dikirim ke antrean persetujuan.');
            return;
        }

        $item->delete();
        $this->logActivity('DELETE', "Menghapus Marketing Master {$item->name}");
        session()->flash('success', 'Master Marketing berhasil dihapus.');
    }
}
