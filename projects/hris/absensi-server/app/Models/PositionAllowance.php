<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PositionAllowance extends Model
{
    protected $guarded = [];

    public function position()
    {
        return $this->belongsTo(Position::class);
    }
}
