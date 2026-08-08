<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BanpotMaster extends Model
{
    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getFinalValidasiAttribute()
    {
        if ($this->notas_valid && $this->rek_tabungan_valid && $this->dapem_valid && $this->oten_valid) {
            return true;
        }

        return false;
    }

    public function setNamaNasabahAttribute($value)
    {
        $this->attributes['nama_nasabah'] = strtoupper($value);
    }

    public function setBankTransferAttribute($value)
    {
        $this->attributes['bank_transfer'] = strtoupper($value);
    }
}
