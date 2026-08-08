<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavingAccount extends Model
{
    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function dati2()
    {
        return $this->belongsTo(MasterDati2::class, 'dati2_code', 'dati2');
    }

    public function notasOwnership()
    {
        return $this->hasOne(NotasOwnership::class, 'notas', 'notas');
    }

    public function province()
    {
        return $this->belongsTo(MasterProvince::class, 'province');
    }

    public function provinceMaster()
    {
        return $this->belongsTo(
            MasterProvince::class,
            'province'
        );
    }


    public function setCustomerNameAttribute($value)
    {
        $cleaned = preg_replace('/[^A-Za-z\s]/', ' ', $value);
        $cleaned = preg_replace('/\s+/', ' ', trim($cleaned));
        $this->attributes['customer_name'] = strtoupper($cleaned);
    }

    public function setMotherMaidenNameAttribute($value)
    {
        $cleaned = preg_replace('/[^A-Za-z\s]/', ' ', $value);
        $cleaned = preg_replace('/\s+/', ' ', trim($cleaned));
        $this->attributes['mother_maiden_name'] = strtoupper($cleaned);
    }

    public function setAddressAttribute($value)
    {
        $this->attributes['address'] = strtoupper($value);
    }

    public function setPlaceOfBirthAttribute($value)
    {
        $this->attributes['place_of_birth'] = strtoupper($value);
    }


    public function setUrbanVillageAttribute($value)
    {
        $this->attributes['urban_village'] = strtoupper($value);
    }

    public function setSubDistrictAttribute($value)
    {
        $this->attributes['sub_district'] = strtoupper($value);
    }

    public function setProvinceAttribute($value)
    {
        $this->attributes['province'] = strtoupper($value);
    }

    public function setWilayahAttribute($value)
    {
        $this->attributes['wilayah'] = strtoupper($value);
    }

    public function setCustomerAliasNameAttribute($value)
    {
        $cleaned = preg_replace('/[^A-Za-z\s]/', ' ', $value);
        $cleaned = preg_replace('/\s+/', ' ', trim($cleaned));
        $this->attributes['customer_alias_name'] = strtoupper($cleaned);
    }

    public function setNamaAhliWarisAttribute($value)
    {
        $cleaned = preg_replace('/[^A-Za-z\s]/', ' ', $value);
        $cleaned = preg_replace('/\s+/', ' ', trim($cleaned));
        $this->attributes['nama_ahli_waris'] = strtoupper($cleaned);
    }

    public function setKeteranganAttribute($value)
    {
        $this->attributes['keterangan'] = strtoupper($value);
    }
}
