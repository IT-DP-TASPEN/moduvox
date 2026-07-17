<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    protected $fillable = ['code', 'name'];

    public function positions()
    {
        return $this->hasMany(Position::class);
    }

    public function approverSetting()
    {
        return $this->hasOne(DivisionApprover::class, 'division_name', 'name');
    }
}
