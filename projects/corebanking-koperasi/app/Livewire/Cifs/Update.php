<?php

namespace App\Livewire\Cifs;

use App\Models\Cif;
use App\Models\City;
use App\Models\District;
use App\Models\Subdistrict;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\LogsActivity;
use App\Traits\ApprovesActions;
use App\Traits\WithLogout;

class Update extends Component
{
    use WithPagination, ApprovesActions, WithLogout, LogsActivity;

    public $search = '';
    
    // View State
    public $viewMode = 'grid'; 
    public $selectedCif = null;

    // Geographic Master Arrays
    public $cities = [], $districts = [], $subdistricts = [];

    // All form props
    public $cif_no, $nik, $npwp, $name;
    public $birth_place, $birth_date, $gender, $blood_type, $religion, $religion_other, $marital_status = 'SINGLE', $mother_maiden_name;
    public $address, $rt, $rw, $province_id, $city_id, $district_id, $subdistrict_id, $postal_code, $domicile_address;
    public $phone, $email;
    public $occupation, $occupation_nip, $company_name, $income_range;
    public $spouse_name, $spouse_nik, $emergency_name, $emergency_phone;
    public $branch_id, $marketing_id, $status;

    public function viewCif($id)
    {
        $this->selectedCif = Cif::findOrFail($id);
        
        $this->cif_no = $this->selectedCif->cif_no;
        $this->nik = $this->selectedCif->nik;
        $this->npwp = $this->selectedCif->npwp;
        $this->name = $this->selectedCif->name;
        $this->birth_place = $this->selectedCif->birth_place;
        $this->birth_date = is_object($this->selectedCif->birth_date) 
            ? $this->selectedCif->birth_date->format('Y-m-d') 
            : $this->selectedCif->birth_date;
        $this->gender = $this->selectedCif->gender;
        $this->blood_type = $this->selectedCif->blood_type;
        $this->religion = $this->selectedCif->religion;
        if (!in_array($this->religion, ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Khonghucu'])) {
            $this->religion_other = $this->religion;
            $this->religion = 'Lainnya';
        }
        $this->marital_status = $this->selectedCif->marital_status;
        $this->mother_maiden_name = $this->selectedCif->mother_maiden_name;

        $this->address = $this->selectedCif->address;
        $this->rt = $this->selectedCif->rt;
        $this->rw = $this->selectedCif->rw;
        $this->province_id = $this->selectedCif->province_id;
        $this->updatedProvinceId(); // Load cities
        $this->city_id = $this->selectedCif->city_id;
        $this->updatedCityId(); // Load dist
        $this->district_id = $this->selectedCif->district_id;
        $this->updatedDistrictId();
        $this->subdistrict_id = $this->selectedCif->subdistrict_id;
        $this->postal_code = $this->selectedCif->postal_code;
        
        $this->domicile_address = $this->selectedCif->domicile_address;
        $this->phone = $this->selectedCif->phone;
        $this->email = $this->selectedCif->email;
        $this->occupation = $this->selectedCif->occupation;
        $this->occupation_nip = $this->selectedCif->occupation_nip;
        $this->company_name = $this->selectedCif->company_name;
        $this->income_range = $this->selectedCif->income_range;
        $this->spouse_name = $this->selectedCif->spouse_name;
        $this->spouse_nik = $this->selectedCif->spouse_nik;
        $this->emergency_name = $this->selectedCif->emergency_name;
        $this->emergency_phone = $this->selectedCif->emergency_phone;
        $this->branch_id = $this->selectedCif->branch_id;
        $this->marketing_id = $this->selectedCif->marketing_id;
        $this->status = $this->selectedCif->status;

        $this->viewMode = 'form';
    }

    public function closeView()
    {
        $this->viewMode = 'grid';
        $this->selectedCif = null;
    }

    public function updatedProvinceId()
    {
        $this->cities = City::where('province_id', $this->province_id)->get();
        $this->districts = []; $this->subdistricts = [];
        $this->city_id = null; $this->district_id = null; $this->subdistrict_id = null;
    }

    public function updatedCityId()
    {
        $this->districts = District::where('regency_id', $this->city_id)->get();
        $this->subdistricts = [];
        $this->district_id = null; $this->subdistrict_id = null;
    }

    public function updatedDistrictId()
    {
        $this->subdistricts = Subdistrict::where('district_id', $this->district_id)->get();
        $this->subdistrict_id = null;
    }

    protected function rules()
    {
        return [
            'nik' => 'required|string|max:20',
            'npwp' => 'nullable|string|max:30',
            'name' => 'required|string|max:255',
            'birth_place' => 'required|string|max:100',
            'birth_date' => 'required|date',
            'gender' => 'required|in:MALE,FEMALE',
            'blood_type' => 'nullable|in:A,B,AB,O',
            'religion' => 'required|in:Islam,Kristen,Katolik,Hindu,Buddha,Khonghucu,Lainnya',
            'marital_status' => 'required|in:SINGLE,MARRIED,WIDOWED,DIVORCED',
            'mother_maiden_name' => 'required|string|max:255',
            
            'address' => 'required|string',
            'rt' => 'nullable|string|max:5',
            'rw' => 'nullable|string|max:5',
            'province_id' => 'required|exists:provinces,id',
            'city_id' => 'required|exists:cities,id',
            'district_id' => 'required|exists:districts,id',
            'subdistrict_id' => 'required|exists:subdistricts,id',
            'postal_code' => 'nullable|string|max:20',
            'domicile_address' => 'nullable|string',
            
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:255',
            
            'occupation' => 'required|in:PNS,Pegawai Swasta,Wiraswasta,BUMN',
            'occupation_nip' => 'nullable|string|max:100',
            'company_name' => 'nullable|string|max:255',
            'income_range' => 'nullable|string|max:255',
            
            'spouse_name' => 'nullable|string|max:255',
            'spouse_nik' => 'nullable|string|max:20',
            'emergency_name' => 'nullable|string|max:255',
            'emergency_phone' => 'nullable|string|max:30',
            
            'branch_id' => 'required|exists:branches,id',
            'marketing_id' => 'nullable|exists:marketing_masters,id',
        ];
    }

    public function submitUpdate()
    {
        $this->validate();

        $data = $this->validate();
        $data['cif_no'] = $this->cif_no;
        $data['status'] = $this->status;
        $data['updated_by'] = auth()->id();

        if ($this->religion === 'Lainnya') {
            $data['religion'] = $this->religion_other;
        }

        $res = $this->interceptAction('cifs.update', 'UPDATE', $data, $this->selectedCif->id, 'UPDATE DATA CIF NASABAH');

        if ($res == 'PENDING') {
            session()->flash('success', 'Update profil diproses dan dikirim ke daftar tunggu persetujuan / Checker.');
        } else {
            session()->flash('success', 'Data Registrasi CIF berhasil diperbarui.');
        }

        $this->closeView();
    }

    public function mount()
    {
        $this->logActivity('NAVIGATE', 'Ubah Data CIF');
    }

    public function render()
    {
        $items = collect();
        $query = Cif::with(['branch', 'marketing'])->where('status', 'ACTIVE');
        
        if (!empty(trim($this->search))) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('cif_no', 'like', '%' . $this->search . '%')
                  ->orWhere('nik', 'like', '%' . $this->search . '%');
            });
            $items = $query->latest()->paginate(10);
        } else {
            $items = $query->whereRaw('1 = 0')->paginate(1);
        }

        return view('livewire.cifs.update', [
            'items' => $items,
            'branches' => \App\Models\Branch::where('is_active', true)->get(),
            'marketings' => \App\Models\MarketingMaster::where('is_active', true)->get(),
            'provinces' => \App\Models\Province::all(),
        ])->layout('layouts.app');
    }
}
