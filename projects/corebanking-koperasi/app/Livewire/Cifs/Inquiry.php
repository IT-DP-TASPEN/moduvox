<?php

namespace App\Livewire\Cifs;

use App\Models\Cif;
use App\Models\Branch;
use App\Models\MarketingMaster;
use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Models\Subdistrict;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\ApprovesActions;
use App\Traits\LogsActivity;
use App\Traits\WithLogout;
use Illuminate\Validation\Rule;

class Inquiry extends Component
{
    use WithPagination, WithLogout, LogsActivity, ApprovesActions;

    public $search = '';
    public $filter_branch = '';
    
    public $user, $role;

    // View State Management
    public $viewMode = 'grid'; // grid, form (for viewing detail)
    public $selectedCif = null;
    public $isReadOnly = false;


    // Detailed Properties for View & Form
    public $cities = [], $districts = [], $subdistricts = [];

    // Form Fields
    public $cif_no, $nik, $npwp, $name;
    public $birth_place, $birth_date, $gender, $blood_type, $religion, $marital_status = 'SINGLE', $mother_maiden_name;
    public $address, $rt, $rw, $province_id, $city_id, $district_id, $subdistrict_id, $postal_code, $domicile_address;
    public $phone, $email;
    public $occupation, $occupation_nip, $company_name, $income_range;
    public $spouse_name, $spouse_nik, $emergency_name, $emergency_phone;
    public $branch_id, $marketing_id, $status = 'ACTIVE';

    protected $queryString = [
        'search' => ['except' => ''],
        'viewMode' => ['except' => 'grid']
    ];

    public function mount()
    {
        $this->user = auth()->user();
        $this->role = $this->user->getRoleNames()->first() ?? 'No Role';
        $this->branch_id = $this->user->branch_id;
        $this->logActivity('NAVIGATE', 'Inquiry CIF');
    }

    // Cascading GEO logic from Index.php
    public function updatedProvinceId($value)
    {
        $this->reset(['city_id', 'district_id', 'subdistrict_id', 'cities', 'districts', 'subdistricts']);
        if ($value) {
            $province = Province::find($value);
            if ($province) $this->cities = City::where('province_id', $province->id)->get();
        }
    }

    public function updatedCityId($value)
    {
        $this->reset(['district_id', 'subdistrict_id', 'districts', 'subdistricts']);
        if ($value) {
            $city = City::find($value);
            if ($city) $this->districts = District::where('regency_id', $city->id)->get();
        }
    }

    public function updatedDistrictId($value)
    {
        $this->reset(['subdistrict_id', 'subdistricts']);
        if ($value) {
            $district = District::find($value);
            if ($district) $this->subdistricts = Subdistrict::where('district_id', $district->id)->get();
        }
    }



    // Inquiry & View Logic
    public function viewCif($id)
    {
        $cif = Cif::with([
            'branch', 'marketing', 'province', 'city', 'district', 'subdistrict',
            'savingAccounts.product',
            'loanAccounts.product',
            'depositAccounts.product',
        ])->findOrFail($id);

        $this->selectedCif = $cif;
        $this->editingId = $cif->id;
        $this->fill($cif->toArray());
        
        $this->birth_date = is_object($cif->birth_date) ? $cif->birth_date->format('Y-m-d') : $cif->birth_date;

        // Fetch associative data for detail view
        if ($this->province_id) $this->cities = City::where('province_id', $this->province_id)->get();
        if ($this->city_id) $this->districts = District::where('regency_id', $this->city_id)->get();
        if ($this->district_id) $this->subdistricts = Subdistrict::where('district_id', $this->district_id)->get();

        $this->isReadOnly = true;
        $this->viewMode = 'form';
        $this->logActivity('VIEW', "Mengakses detil CIF (Form Read-only): " . ($cif->cif_no ?? 'N/A'), $cif);
    }

    public function closeView()
    {
        $this->viewMode = 'grid';
        $this->selectedCif = null;
    }



    public function render()
    {
        $items = collect(); 
        $query = Cif::with(['branch', 'marketing']);
        
        if (!empty(trim($this->search)) || !empty($this->filter_branch)) {
            if ($this->search) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('cif_no', 'like', '%' . $this->search . '%')
                      ->orWhere('nik', 'like', '%' . $this->search . '%');
                });
            }
            if ($this->filter_branch) {
                $query->where('branch_id', $this->filter_branch);
            }
            $items = $query->latest()->paginate(10);
        } else {
            $items = $query->whereRaw('1 = 0')->paginate(1);
        }

        return view('livewire.cifs.inquiry', [
            'items' => $items,
            'branches' => Branch::where('is_active', true)->get(),
            'marketings' => MarketingMaster::where('is_active', true)->get(),
            'provinces' => Province::all()
        ])->layout('layouts.app');
    }
}
