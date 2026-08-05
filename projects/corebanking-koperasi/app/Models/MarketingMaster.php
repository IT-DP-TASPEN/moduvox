<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingMaster extends Model
{
    protected $fillable = [
        'marketing_code',
        'name',
        'phone',
        'is_active',
        'branch_master_id'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_master_id');
    }
}

