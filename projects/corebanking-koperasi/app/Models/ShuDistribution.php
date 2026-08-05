<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShuDistribution extends Model
{
    protected $fillable = ['periode', 'total_laba', 'status'];

    public function details()
    {
        return $this->hasMany(ShuDistributionDetail::class);
    }
}
