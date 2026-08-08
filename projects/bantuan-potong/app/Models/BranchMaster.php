<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchMaster extends Model
{
    protected $guarded = [];

    public function mitraMaster()
    {
        return $this->belongsTo(MitraMaster::class, 'mitra_master_id');
    }

    public function setBranchNameAttribute($value)
    {
        $this->attributes['branch_name'] = strtoupper($value);
    }
}
