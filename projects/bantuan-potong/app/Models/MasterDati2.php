<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterDati2 extends Model
{
    protected $table = 'city';

    protected $guarded = [];

    public function province()
    {
        return $this->belongsTo(MasterProvince::class, 'province_id');
    }
}
