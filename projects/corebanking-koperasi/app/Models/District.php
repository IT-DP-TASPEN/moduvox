<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $fillable = ['regency_id', 'city_id', 'province_id', 'nama', 'name'];

    public function city()
    {
        return $this->belongsTo(City::class, 'regency_id');
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function subdistricts()
    {
        return $this->hasMany(Subdistrict::class, 'district_id');
    }

    public function getNameAttribute()
    {
        return $this->nama;
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['nama'] = $value;
    }

    public function getCityIdAttribute()
    {
        return $this->regency_id;
    }

    public function setCityIdAttribute($value): void
    {
        $this->attributes['regency_id'] = $value;
    }
}
