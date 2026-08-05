<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvTanah extends Model
{
    protected $guarded = [];

    public function inventaris()
    {
        return $this->belongsTo(Inventaris::class);
    }
}
