<?php

namespace App\Livewire\Districts;

use App\Models\District;
use App\Models\City;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\WithLogout;

use App\Traits\ApprovesActions;
use App\Traits\LogsActivity;

class Index extends Component
{
    use WithPagination, WithLogout, ApprovesActions, LogsActivity;

    public $search = '';
    public $showModal = false;
    public $editingId = null;

    public $nama, $regency_id, $province_id;

    // For sidebar user info
    public $user, $role;

    public function mount()
    {
        $this->user = auth()->user();
        $this->role = $this->user->getRoleNames()->first() ?? 'No Role';
        $this->logActivity('NAVIGATE', 'Districts');
    }

    protected $rules = [
        'nama' => 'required|string',
        'regency_id' => 'required|exists:cities,id',
    ];

    public function render()
    {
        $query = District::query()->with(['city.province', 'province']);
        if ($this->search) {
            $query->where('nama', 'like', '%' . $this->search . '%');
        }

        return view('livewire.districts.index', [
            'items' => $query->paginate(10),
            'cities' => $this->showModal ? City::all() : collect()
        ])->layout('layouts.app');
    }

    public function create()
    {
        $this->reset(['nama', 'regency_id', 'province_id', 'editingId']);
        $this->showModal = true;
    }

    public function edit($id)
    {
        $district = District::find($id);
        $this->editingId = $id;
        $this->nama = $district->nama;
        $this->regency_id = $district->regency_id;
        $this->province_id = $district->province_id;
        $this->showModal = true;
    }

    public function save()
    {
        $rules = $this->rules;
        $this->validate($rules);

        $city = City::find($this->regency_id);
        $data = [
            'nama' => $this->nama,
            'regency_id' => $this->regency_id,
            'province_id' => $city?->province_id,
        ];

        // Intercept Action Approval
        $status = $this->interceptAction(
            'districts', 
            $this->editingId ? 'UPDATE' : 'CREATE', 
            $data,
            $this->editingId,
            $this->editingId ? District::find($this->editingId)->toArray() : null
        );

        if ($status == 'PENDING') {
            $this->showModal = false;
            session()->flash('success', 'Permintaan perubahan telah dikirim ke antrean persetujuan.');
            $this->logActivity(($this->editingId ? 'UPDATE' : 'CREATE') . '_REQUEST', "Mengajukan " . ($this->editingId ? 'perubahan' : 'pembuatan') . " kecamatan: " . $this->nama, null, $data);
            return;
        }

        if ($this->editingId) {
            District::find($this->editingId)->update($data);
            session()->flash('success', 'Kecamatan berhasil diperbarui.');
            $this->logActivity('UPDATE', "Berhasil memperbarui kecamatan: " . $this->nama, null, $data);
        } else {
            District::create($data);
            session()->flash('success', 'Kecamatan berhasil ditambahkan.');
            $this->logActivity('CREATE', "Berhasil menambahkan kecamatan: " . $this->nama, null, $data);
        }

        $this->showModal = false;
    }

    public function delete($id)
    {
        $item = District::find($id);

        // Intercept Action Approval
        $status = $this->interceptAction(
            'districts', 
            'DELETE', 
            null,
            $item->id,
            $item->toArray()
        );

        if ($status == 'PENDING') {
            session()->flash('success', 'Permintaan penghapusan telah dikirim ke antrean persetujuan.');
            $this->logActivity('DELETE_REQUEST', "Mengajukan penghapusan kecamatan: " . $item->nama, $item);
            return;
        }

        $item->delete();
        session()->flash('success', 'Kecamatan berhasil dihapus.');
        $this->logActivity('DELETE', "Berhasil menghapus kecamatan: " . $item->nama, $item);
    }
}
