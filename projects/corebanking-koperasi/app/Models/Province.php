<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $fillable = ['nama', 'name'];
    
    public function cities()
    {
        return $this->hasMany(City::class, 'province_id');
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
