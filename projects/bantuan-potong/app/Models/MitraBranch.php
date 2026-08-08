<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MitraBranch extends Model
{
    protected $guarded = [];
    public function mitraMaster()
    {
        return $this->belongsTo(MitraMaster::class, 'mitra_master_id');
    }

    public function setNamaCabangAttribute($value)
    {
        $this->attributes['nama_cabang'] = strtoupper($value);
    }
}