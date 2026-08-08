<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermintaanChecking extends Model
{
    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function setNamaNasabahAttribute($value)
    {
        $this->attributes['nama_nasabah'] = strtoupper($value);
    }

    public function setWilayahAttribute($value)
    {
        $this->attributes['wilayah'] = strtoupper($value);
    }

    public function setKeteranganAttribute($value)
    {
        $this->attributes['keterangan'] = strtoupper($value);
    }
}
