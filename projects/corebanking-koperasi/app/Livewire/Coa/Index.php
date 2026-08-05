<?php

namespace App\Livewire\Coa;

use App\Models\Coa;
use Livewire\Component;
use App\Traits\WithLogout;
use App\Traits\ApprovesActions;
use App\Traits\LogsActivity;

class Index extends Component
{
    use WithLogout, ApprovesActions, LogsActivity;

    public $viewMode = 'list';
    public $search = '';
    
    // Form fields
    public $editingId = null;
    public $coa_code, $name, $type = 'ASSET', $parent_id, $is_leaf = true, $is_cash = false, $is_active = true;

    public $user, $role;

    public function mount()
    {
        $this->user = auth()->user();
        $this->role = $this->user->getRoleNames()->first() ?? 'No Role';
        $this->logActivity('NAVIGATE', 'Chart of Accounts');
    }

    protected $rules = [
        'coa_code' => 'required|string|unique:coas,coa_code',
        'name' => 'required|string|max:255',
        'type' => 'required|in:ASSET,LIABILITY,EQUITY,REVENUE,EXPENSE',
        'parent_id' => 'nullable|exists:coas,id',
    ];

    public function create($parentId = null)
    {
        $this->resetForm();
        $this->parent_id = $parentId;
        if ($parentId) {
            $parent = Coa::find($parentId);
            $this->type = $parent->type;
        }
        $this->viewMode = 'form';
    }

    public function edit($id)
    {
        $coa = Coa::findOrFail($id);
        $this->editingId = $id;
        $this->fill($coa->toArray());
        $this->viewMode = 'form';
    }

    public function resetForm()
    {
        $this->reset(['editingId', 'coa_code', 'name', 'type', 'parent_id', 'is_leaf', 'is_cash', 'is_active']);
        $this->type = 'ASSET';
        $this->is_active = true;
        $this->is_leaf = true;
    }

    public function save()
    {
        $rules = $this->rules;
        if ($this->editingId) {
            $rules['coa_code'] = 'required|string|unique:coas,coa_code,' . $this->editingId;
        }

        $this->validate($rules);

        $data = [
            'coa_code' => $this->coa_code,
            'name' => $this->name,
            'type' => $this->type,
            'parent_id' => $this->parent_id ?: null,
            'is_leaf' => (bool) $this->is_leaf,
            'is_cash' => (bool) $this->is_cash,
            'is_active' => (bool) $this->is_active,
        ];

        $res = $this->interceptAction('coas', $this->editingId ? 'UPDATE' : 'CREATE', $data, $this->editingId, "COA: " . $this->name);

        if ($res === 'PENDING') {
            session()->flash('success', 'Perubahan COA dikirim ke antrean persetujuan.');
            $this->logActivity(($this->editingId ? 'UPDATE' : 'CREATE') . '_REQUEST', "Mengajukan " . ($this->editingId ? 'perubahan' : 'pembuatan') . " akun COA: " . $this->name, null, $data);
        } else {
            $coa = $this->editingId
                ? tap(Coa::findOrFail($this->editingId))->update($data)
                : Coa::create($data);

            if ($coa->parent_id) {
                Coa::whereKey($coa->parent_id)->update(['is_leaf' => false]);
            }

            session()->flash('success', 'COA berhasil disimpan.');
            $this->logActivity($this->editingId ? 'UPDATE' : 'CREATE', "Berhasil menyimpan akun COA: " . $this->name, null, $data);
        }

        $this->viewMode = 'list';
    }

    public function render()
    {
        // Get top level accounts
        $query = Coa::with('children')->whereNull('parent_id');
        
        if ($this->search) {
            $query = Coa::where('name', 'like', '%' . $this->search . '%')
                       ->orWhere('coa_code', 'like', '%' . $this->search . '%');
        }

        return view('livewire.coa.index', [
            'coas' => $query->orderBy('coa_code')->get(),
            'allParents' => Coa::where('is_leaf', false)->orWhereNull('parent_id')->orderBy('coa_code')->get(),
        ])->layout('layouts.app');
    }
}
