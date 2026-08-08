<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotasOwnership extends Model
{
    protected $guarded = [];

    public function mitra()
    {
        return $this->belongsTo(MitraMaster::class, 'mitra_master_id');
    }

    protected static function booted()
    {
        static::saving(function ($model) {
            if (!empty($model->rek_tabungan)) {
                $model->rek_replace = preg_replace('/\D/', '', trim($model->rek_tabungan));
            }
        });
    }

    public function setNamaNasabahAttribute($value)
    {
        $this->attributes['nama_nasabah'] = strtoupper($value);
    }
}