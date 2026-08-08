<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermintaanFlaggingTifInternal extends Model
{
    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function marketing()
    {
        return $this->belongsTo(MarketingMaster::class, 'marketing_id');
    }

    public function setNamaNasabahAttribute($value)
    {
        $this->attributes['nama_nasabah'] = strtoupper($value);
    }

    public function setTempatLahirAttribute($value)
    {
        $this->attributes['tempat_lahir'] = strtoupper($value);
    }

    public function setAlamatAttribute($value)
    {
        $this->attributes['alamat'] = strtoupper($value);
    }

    public function setWilayahAttribute($value)
    {
        $this->attributes['wilayah'] = strtoupper($value);
    }

    public function setKeteranganAttribute($value)
    {
        $this->attributes['keterangan'] = strtoupper($value);
    }

    public function mitraMaster()
    {
        return $this->belongsTo(MitraMaster::class, 'mitra_master_id');
    }
}
