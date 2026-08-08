<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subdistrict extends Model
{
    protected $table = 'subdistrict';

    protected $guarded = [];

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function city()
    {
        return $this->belongsTo(MasterDati2::class, 'regency_id');
    }

    public function province()
    {
        return $this->belongsTo(MasterProvince::class, 'province_id');
    }
}
