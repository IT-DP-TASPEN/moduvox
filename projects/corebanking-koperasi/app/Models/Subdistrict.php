<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subdistrict extends Model
{
    protected $fillable = ['district_id', 'regency_id', 'province_id', 'nama', 'name'];

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'regency_id');
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function getNameAttribute()
    {
        return $this->nama;
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['nama'] = $value;
    }
}
