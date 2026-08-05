<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cif extends Model
{
    use HasFactory;

    protected $fillable = [
        'cif_no',
        'nik',
        'npwp',
        'name',
        'birth_place',
        'birth_date',
        'gender',
        'blood_type',
        'religion',
        'marital_status',
        'mother_maiden_name',
        'address',
        'rt',
        'rw',
        'province_id',
        'city_id',
        'district_id',
        'subdistrict_id',
        'postal_code',
        'domicile_address',
        'phone',
        'email',
        'occupation',
        'occupation_nip',
        'company_name',
        'income_range',
        'spouse_name',
        'spouse_nik',
        'emergency_name',
        'emergency_phone',
        'branch_id',
        'marketing_id',
        'status',
        'created_by',
        'updated_by',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'birth_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function province()
    {
        return $this->belongsTo(Province::class);
    }
    public function city()
    {
        return $this->belongsTo(City::class);
    }
    public function district()
    {
        return $this->belongsTo(District::class);
    }
    public function subdistrict()
    {
        return $this->belongsTo(Subdistrict::class);
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    public function marketing()
    {
        return $this->belongsTo(MarketingMaster::class, 'marketing_id');
    }

    // Cross-module relationships
    public function savingAccounts()
    {
        return $this->hasMany(SavingAccount::class, 'cif_id');
    }
    public function loanAccounts()
    {
        return $this->hasMany(LoanAccount::class, 'cif_id');
    }
    public function depositAccounts()
    {
        return $this->hasMany(DepositAccount::class, 'cif_id');
    }
    public function mobileAccess()
    {
        return $this->hasOne(MobileAccess::class, 'cif_id');
    }

    public function getAlamatLengkapAttribute()
    {
        $parts = [
            $this->address,
            ($this->rt || $this->rw) ? "RT/RW: {$this->rt}/{$this->rw}" : null,
            $this->subdistrict ? $this->subdistrict->nama : null,
            $this->district ? $this->district->nama : null,
            $this->city ? $this->city->nama : null,
            $this->province ? $this->province->nama : null,
            $this->postal_code,
        ];
        return implode(', ', array_filter($parts));
    }
}
