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
use App\Traits\ApprovesActions;
use App\Traits\LogsActivity;
use App\Traits\WithLogout;

class Create extends Component
{
    use ApprovesActions, LogsActivity, WithLogout;

    public $currentTab = 'personal'; 

    public $cities = [];
    public $districts = [];
    public $subdistricts = [];

    public $cif_no, $nik, $npwp, $name;
    public $birth_place, $birth_date, $gender, $blood_type, $religion, $religion_other, $marital_status = 'SINGLE', $mother_maiden_name;
    public $address, $rt, $rw, $province_id, $city_id, $district_id, $subdistrict_id, $postal_code, $domicile_address;
    public $phone, $email;
    public $occupation, $occupation_nip, $company_name, $income_range;
    public $spouse_name, $spouse_nik, $emergency_name, $emergency_phone;
    public $branch_id, $marketing_id, $status = 'ACTIVE';

    public $user, $role;

    public function mount()
    {
        $this->logActivity('NAVIGATE', 'Registrasi CIF');

        $this->user = auth()->user();
        $this->role = $this->user->getRoleNames()->first() ?? 'No Role';
        $this->branch_id = $this->user->branch_id;
    }

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

    protected function rules()
    {
        return [
            'nik' => 'required|string|max:20|unique:cifs',
            'npwp' => 'nullable|string|max:30',
            'name' => 'required|string|max:255',
            'birth_place' => 'required|string|max:255',
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

    public function render()
    {
        return view('livewire.cifs.create', [
            'branches' => Branch::where('is_active', true)->get(),
            'marketings' => MarketingMaster::where('is_active', true)->get(),
            'provinces' => Province::all()
        ])->layout('layouts.app');
    }

    public function save()
    {
        $this->validate();

        $branch = Branch::find($this->branch_id);
        $branchCode = $branch->branch_code ?? 'XX';
        
        $count = Cif::where('branch_id', $this->branch_id)->count() + 1;
        $code = $branchCode . str_pad($count, 7, '0', STR_PAD_LEFT);
        
        while(Cif::whereRaw('BINARY cif_no = BINARY ?', [$code])->exists()) {
            $count++;
            $code = $branchCode . str_pad($count, 7, '0', STR_PAD_LEFT);
        }

        $data = $this->only([
            'nik', 'npwp', 'name', 
            'birth_place', 'birth_date', 'gender', 'blood_type', 'religion', 'marital_status', 'mother_maiden_name',
            'address', 'rt', 'rw', 'province_id', 'city_id', 'district_id', 'subdistrict_id', 'postal_code', 'domicile_address',
            'phone', 'email', 
            'occupation', 'occupation_nip', 'company_name', 'income_range', 
            'spouse_name', 'spouse_nik', 'emergency_name', 'emergency_phone',
            'branch_id', 'marketing_id'
        ]);

        $data['cif_no'] = $code;
        $data['status'] = 'ACTIVE';
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        if ($this->religion === 'Lainnya') {
            $data['religion'] = $this->religion_other;
        }

        $statusResult = $this->interceptAction('cifs.create', 'CREATE', $data, null, null);

        if ($statusResult == 'PENDING') {
            session()->flash('success', 'Pengajuan Registrasi Data CIF Anggota baru telah masuk ke antrean persetujuan Maker-Checker.');
            return redirect()->route('cifs.inquiry');
        }

        $data['approved_by'] = auth()->id();
        $data['approved_at'] = now();
        $cif = Cif::create($data);
        $this->logActivity('CREATE', "Membuat Data CIF {$this->name} ({$cif->cif_no})");
        
        session()->flash('success', 'Data CIF Anggota berhasil dibuat.');
        return redirect()->route('cifs.inquiry');
    }
}
