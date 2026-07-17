<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $guarded = [];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function allowances()
    {
        return $this->hasMany(PositionAllowance::class);
    }

    /**
     * Get the latest effective allowance for this position.
     */
    public function currentAllowance($date = null)
    {
        $date = $date ?: now()->toDateString();
        return $this->allowances()
            ->where('effective_date', '<=', $date)
            ->orderBy('effective_date', 'desc')
            ->first();
    }
}
