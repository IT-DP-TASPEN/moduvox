<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterProvince extends Model
{
    protected $table = 'province';

    protected $guarded = [];

    public function dati2s()
    {
        return $this->hasMany(MasterDati2::class, 'province_id');
    }
}
