<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingTarget extends Model
{
    protected $guarded = [];

    public function getLabelAttribute(): string
    {
        return "{$this->bulan} {$this->tahun}";
    }

    public function marketingMasters()
    {
        return $this->hasMany(MarketingMaster::class, 'marketing_target_id');
    }
}
