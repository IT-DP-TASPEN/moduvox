<?php

namespace App\Livewire\Cities;

use App\Traits\WithLogout;
use App\Models\City;
use App\Models\Province;
use Livewire\Component;
use Livewire\WithPagination;

use App\Traits\ApprovesActions;
use App\Traits\LogsActivity;

class Index extends Component
{
    use WithPagination, WithLogout, ApprovesActions, LogsActivity;

    public $search = '';
    public $showModal = false;
    public $editingId = null;

    // Form fields
    public $dati2, $nama, $province_id;

    // For sidebar user info
    public $user, $role;

    public function mount()
    {
        $this->user = auth()->user();
        $this->role = $this->user->getRoleNames()->first() ?? 'No Role';
        $this->logActivity('NAVIGATE', 'Cities');
    }

    protected $rules = [
        'dati2' => 'required|string|unique:cities,dati2',
        'nama' => 'required|string',
        'province_id' => 'required|exists:provinces,id',
    ];

    public function render()
    {
        $query = City::query()->with('province');
        if ($this->search) {
            $query->where('nama', 'like', '%' . $this->search . '%')
                  ->orWhere('dati2', 'like', '%' . $this->search . '%');
        }

        return view('livewire.cities.index', [
            'items' => $query->paginate(10),
            'provinces' => $this->showModal ? Province::all() : collect()
        ])->layout('layouts.app');
    }

    public function create()
    {
        $this->reset(['dati2', 'nama', 'province_id', 'editingId']);
        $this->showModal = true;
    }

    public function edit($id)
    {
        $city = City::find($id);
        $this->editingId = $id;
        $this->dati2 = $city->dati2;
        $this->nama = $city->nama;
        $this->province_id = $city->province_id;
        $this->showModal = true;
    }

    public function save()
    {
        $rules = $this->rules;
        if ($this->editingId) {
            $rules['dati2'] = 'required|string|unique:cities,dati2,' . $this->editingId;
        }
        $this->validate($rules);

        $data = [
            'dati2' => $this->dati2,
            'nama' => $this->nama,
            'province_id' => $this->province_id,
        ];

        // Intercept Action Approval
        $status = $this->interceptAction(
            'cities', 
            $this->editingId ? 'UPDATE' : 'CREATE', 
            $data,
            $this->editingId ? City::find($this->editingId)->dati2 : null,
            $this->editingId ? City::find($this->editingId)->toArray() : null
        );

        if ($status == 'PENDING') {
            $this->showModal = false;
            session()->flash('success', 'Permintaan perubahan telah dikirim ke antrean persetujuan.');
            $this->logActivity(($this->editingId ? 'UPDATE' : 'CREATE') . '_REQUEST', "Mengajukan " . ($this->editingId ? 'perubahan' : 'pembuatan') . " kota/kabupaten: " . $this->nama, null, $data);
            return;
        }

        if ($this->editingId) {
            City::find($this->editingId)->update($data);
            session()->flash('success', 'Kota/Kabupaten berhasil diperbarui.');
            $this->logActivity('UPDATE', "Berhasil memperbarui kota/kabupaten: " . $this->nama, null, $data);
        } else {
            City::create($data);
            session()->flash('success', 'Kota/Kabupaten berhasil ditambahkan.');
            $this->logActivity('CREATE', "Berhasil menambahkan kota/kabupaten: " . $this->nama, null, $data);
        }

        $this->showModal = false;
    }

    public function delete($id)
    {
        $item = City::find($id);

        // Intercept Action Approval
        $status = $this->interceptAction(
            'cities', 
            'DELETE', 
            null,
            $item->dati2,
            $item->toArray()
        );

        if ($status == 'PENDING') {
            session()->flash('success', 'Permintaan penghapusan telah dikirim ke antrean persetujuan.');
            $this->logActivity('DELETE_REQUEST', "Mengajukan penghapusan kota/kabupaten: " . $item->nama, $item);
            return;
        }

        $item->delete();
        session()->flash('success', 'Kota/Kabupaten berhasil dihapus.');
        $this->logActivity('DELETE', "Berhasil menghapus kota/kabupaten: " . $item->nama, $item);
    }
}
