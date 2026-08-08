<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MitraMaster extends Model
{
    protected $guarded = [];

    public function setNamaMitraAttribute($value)
    {
        $this->attributes['nama_mitra'] = strtoupper($value);
    }
}