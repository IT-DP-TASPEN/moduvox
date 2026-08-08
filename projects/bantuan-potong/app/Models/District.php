<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $table = 'district';

    protected $guarded = [];

    public function city()
    {
        return $this->belongsTo(MasterDati2::class, 'regency_id');
    }

    public function province()
    {
        return $this->belongsTo(MasterProvince::class, 'province_id');
    }
}
