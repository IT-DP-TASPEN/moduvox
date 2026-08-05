<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = ['province_id', 'nama', 'name', 'dati2', 'city_code'];

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function districts()
    {
        return $this->hasMany(District::class, 'regency_id');
    }

    public function getNameAttribute()
    {
        return $this->nama;
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['nama'] = $value;
    }

    public function getCityCodeAttribute()
    {
        return $this->dati2;
    }

    public function setCityCodeAttribute($value): void
    {
        $this->attributes['dati2'] = $value;
    }
}
