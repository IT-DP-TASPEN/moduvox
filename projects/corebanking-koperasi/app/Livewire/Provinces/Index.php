<?php

namespace App\Livewire\Provinces;

use App\Models\Province;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Traits\ApprovesActions;
use App\Traits\WithLogout;

use App\Traits\LogsActivity;

class Index extends Component
{
    use WithPagination, ApprovesActions, WithLogout, LogsActivity;

    public $search = '';
    public $showModal = false;
    public $editingId = null;

    // Form fields
    public $nama;

    // For sidebar user info
    public $user, $role;

    public function mount()
    {
        $this->user = auth()->user();
        $this->role = $this->user->getRoleNames()->first() ?? 'No Role';
        $this->logActivity('NAVIGATE', 'Provinces');
    }

    protected $rules = [
        'nama' => 'required|string',
    ];

    public function render()
    {
        $query = Province::query();
        if ($this->search) {
            $query->where('nama', 'like', '%' . $this->search . '%');
        }

        return view('livewire.provinces.index', [
            'items' => $query->paginate(10)
        ])->layout('layouts.app');
    }

    public function create()
    {
        $this->reset(['nama', 'editingId']);
        $this->showModal = true;
    }

    public function edit($id)
    {
        $province = Province::find($id);
        $this->editingId = $id;
        $this->nama = $province->nama;
        $this->showModal = true;
    }

    public function save()
    {
        $rules = $this->rules;
        $this->validate($rules);

        $data = [
            'nama' => $this->nama,
        ];

        // Intercept Action Approval
        $status = $this->interceptAction(
            'provinces', 
            $this->editingId ? 'UPDATE' : 'CREATE', 
            $data,
            $this->editingId,
            $this->editingId ? Province::find($this->editingId)->toArray() : null
        );

        if ($status == 'PENDING') {
            $this->showModal = false;
            session()->flash('success', 'Permintaan perubahan telah dikirim ke antrean persetujuan.');
            $this->logActivity(($this->editingId ? 'UPDATE' : 'CREATE') . '_REQUEST', "Mengajukan " . ($this->editingId ? 'perubahan' : 'pembuatan') . " provinsi: " . $this->nama, null, $data);
            return;
        }

        if ($this->editingId) {
            Province::find($this->editingId)->update($data);
            session()->flash('success', 'Provinsi berhasil diperbarui.');
            $this->logActivity('UPDATE', "Berhasil memperbarui provinsi: " . $this->nama, null, $data);
        } else {
            Province::create($data);
            session()->flash('success', 'Provinsi berhasil ditambahkan.');
            $this->logActivity('CREATE', "Berhasil menambahkan provinsi: " . $this->nama, null, $data);
        }

        $this->showModal = false;
    }

    public function delete($id)
    {
        $item = Province::find($id);
        
        // Intercept Action Approval
        $status = $this->interceptAction(
            'provinces', 
            'DELETE', 
            null,
            $item->id,
            $item->toArray()
        );

        if ($status == 'PENDING') {
            session()->flash('success', 'Permintaan penghapusan telah dikirim ke antrean persetujuan.');
            $this->logActivity('DELETE_REQUEST', "Mengajukan penghapusan provinsi: " . $item->nama, $item);
            return;
        }

        $item->delete();
        session()->flash('success', 'Provinsi berhasil dihapus.');
        $this->logActivity('DELETE', "Berhasil menghapus provinsi: " . $item->nama, $item);
    }
}
