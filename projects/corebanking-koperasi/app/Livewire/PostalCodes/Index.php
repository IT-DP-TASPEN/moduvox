<?php

namespace App\Livewire\PostalCodes;

use App\Models\PostalCode;
use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Models\Subdistrict;
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

    // Form fields
    public $subdistrict_id, $district_id, $city_id, $province_id, $code;

    // For sidebar user info
    public $user, $role;

    public function mount()
    {
        $this->user = auth()->user();
        $this->role = $this->user->getRoleNames()->first() ?? 'No Role';
        $this->logActivity('NAVIGATE', 'Postal Codes');
    }

    protected $rules = [
        'subdistrict_id' => 'required|exists:subdistricts,id',
        'district_id' => 'required|exists:districts,id',
        'city_id' => 'required|exists:cities,id',
        'province_id' => 'required|exists:provinces,id',
        'code' => 'required|string|max:10',
    ];

    public function render()
    {
        $query = PostalCode::query()->with(['subdistrict.district.city.province']);
        
        if ($this->search) {
            $query->where('code', 'like', '%' . $this->search . '%')
                  ->orWhereHas('subdistrict', function($q) {
                      $q->where('nama', 'like', '%' . $this->search . '%');
                  });
        }

        return view('livewire.postal-codes.index', [
            'items' => $query->paginate(10),
            'provinces' => $this->showModal ? Province::all() : collect(),
            'cities' => ($this->showModal && $this->province_id) ? City::where('province_id', $this->province_id)->get() : collect(),
            'districts' => ($this->showModal && $this->city_id) ? District::where('regency_id', $this->city_id)->get() : collect(),
            'subdistricts' => ($this->showModal && $this->district_id) ? Subdistrict::where('district_id', $this->district_id)->get() : collect(),
        ])->layout('layouts.app');
    }

    public function create()
    {
        $this->reset(['subdistrict_id', 'district_id', 'city_id', 'province_id', 'code', 'editingId']);
        $this->showModal = true;
    }

    public function edit($id)
    {
        $pc = PostalCode::find($id);
        $this->editingId = $id;
        $this->subdistrict_id = $pc->subdistrict_id;
        if ($pc->subdistrict) {
            $this->district_id = $pc->subdistrict->district_id;
            if ($pc->subdistrict->district) {
                $this->city_id = $pc->subdistrict->district->regency_id;
                if ($pc->subdistrict->district->city) {
                    $this->province_id = $pc->subdistrict->district->city->province_id;
                }
            }
        }
        $this->code = $pc->code;
        $this->showModal = true;
    }

    public function updatedProvinceId($value)
    {
        $this->city_id = '';
        $this->district_id = '';
        $this->subdistrict_id = '';
    }

    public function updatedCityId($value)
    {
        $this->district_id = '';
        $this->subdistrict_id = '';
    }

    public function updatedDistrictId($value)
    {
        $this->subdistrict_id = '';
    }

    public function save()
    {
        $rules = $this->rules;
        $this->validate($rules);

        $data = [
            'subdistrict_id' => $this->subdistrict_id,
            'code' => $this->code,
        ];

        // Intercept Action Approval
        $status = $this->interceptAction(
            'postal_codes', 
            $this->editingId ? 'UPDATE' : 'CREATE', 
            $data,
            $this->editingId ? $this->editingId : null,
            $this->editingId ? PostalCode::find($this->editingId)->toArray() : null
        );

        if ($status == 'PENDING') {
            $this->showModal = false;
            session()->flash('success', 'Permintaan perubahan telah dikirim ke antrean persetujuan.');
            $this->logActivity(($this->editingId ? 'UPDATE' : 'CREATE') . '_REQUEST', "Mengajukan " . ($this->editingId ? 'perubahan' : 'pembuatan') . " kode pos: " . $this->code, null, $data);
            return;
        }

        if ($this->editingId) {
            PostalCode::find($this->editingId)->update($data);
            session()->flash('success', 'Kode Pos berhasil diperbarui.');
            $this->logActivity('UPDATE', "Berhasil memperbarui kode pos: " . $this->code, null, $data);
        } else {
            PostalCode::create($data);
            session()->flash('success', 'Kode Pos berhasil ditambahkan.');
            $this->logActivity('CREATE', "Berhasil menambahkan kode pos: " . $this->code, null, $data);
        }

        $this->showModal = false;
    }

    public function delete($id)
    {
        $item = PostalCode::find($id);

        // Intercept Action Approval
        $status = $this->interceptAction(
            'postal_codes', 
            'DELETE', 
            null,
            $item->id,
            $item->toArray()
        );

        if ($status == 'PENDING') {
            session()->flash('success', 'Permintaan penghapusan telah dikirim ke antrean persetujuan.');
            $this->logActivity('DELETE_REQUEST', "Mengajukan penghapusan kode pos: " . $item->code, $item);
            return;
        }

        $item->delete();
        session()->flash('success', 'Kode Pos berhasil dihapus.');
        $this->logActivity('DELETE', "Berhasil menghapus kode pos: " . $item->code, $item);
    }
}
