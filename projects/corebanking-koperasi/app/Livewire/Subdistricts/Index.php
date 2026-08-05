<?php

namespace App\Livewire\Subdistricts;

use App\Models\Subdistrict;
use App\Models\District;
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

    public $nama, $district_id;

    // For sidebar user info
    public $user, $role;

    public function mount()
    {
        $this->user = auth()->user();
        $this->role = $this->user->getRoleNames()->first() ?? 'No Role';
        $this->logActivity('NAVIGATE', 'Subdistricts');
    }

    protected $rules = [
        'nama' => 'required|string',
        'district_id' => 'required|exists:districts,id',
    ];

    public function render()
    {
        $query = Subdistrict::query()->with(['district.city.province', 'city', 'province']);
        if ($this->search) {
            $query->where('nama', 'like', '%' . $this->search . '%');
        }

        return view('livewire.subdistricts.index', [
            'items' => $query->paginate(10),
            'districts' => $this->showModal ? District::all() : collect()
        ])->layout('layouts.app');
    }

    public function create()
    {
        $this->reset(['nama', 'district_id', 'editingId']);
        $this->showModal = true;
    }

    public function edit($id)
    {
        $subdistrict = Subdistrict::find($id);
        $this->editingId = $id;
        $this->nama = $subdistrict->nama;
        $this->district_id = $subdistrict->district_id;
        $this->showModal = true;
    }

    public function save()
    {
        $rules = $this->rules;
        $this->validate($rules);

        $district = District::find($this->district_id);
        $data = [
            'nama' => $this->nama,
            'district_id' => $this->district_id,
            'regency_id' => $district?->regency_id,
            'province_id' => $district?->province_id,
        ];

        // Intercept Action Approval
        $status = $this->interceptAction(
            'subdistricts', 
            $this->editingId ? 'UPDATE' : 'CREATE', 
            $data,
            $this->editingId,
            $this->editingId ? Subdistrict::find($this->editingId)->toArray() : null
        );

        if ($status == 'PENDING') {
            $this->showModal = false;
            session()->flash('success', 'Permintaan perubahan telah dikirim ke antrean persetujuan.');
            $this->logActivity(($this->editingId ? 'UPDATE' : 'CREATE') . '_REQUEST', "Mengajukan " . ($this->editingId ? 'perubahan' : 'pembuatan') . " kelurahan: " . $this->nama, null, $data);
            return;
        }

        if ($this->editingId) {
            Subdistrict::find($this->editingId)->update($data);
            session()->flash('success', 'Kelurahan berhasil diperbarui.');
            $this->logActivity('UPDATE', "Berhasil memperbarui kelurahan: " . $this->nama, null, $data);
        } else {
            Subdistrict::create($data);
            session()->flash('success', 'Kelurahan berhasil ditambahkan.');
            $this->logActivity('CREATE', "Berhasil menambahkan kelurahan: " . $this->nama, null, $data);
        }

        $this->showModal = false;
    }

    public function delete($id)
    {
        $item = Subdistrict::find($id);

        // Intercept Action Approval
        $status = $this->interceptAction(
            'subdistricts', 
            'DELETE', 
            null,
            $item->id,
            $item->toArray()
        );

        if ($status == 'PENDING') {
            session()->flash('success', 'Permintaan penghapusan telah dikirim ke antrean persetujuan.');
            $this->logActivity('DELETE_REQUEST', "Mengajukan penghapusan kelurahan: " . $item->nama, $item);
            return;
        }

        $item->delete();
        session()->flash('success', 'Kelurahan berhasil dihapus.');
        $this->logActivity('DELETE', "Berhasil menghapus kelurahan: " . $item->nama, $item);
    }
}
