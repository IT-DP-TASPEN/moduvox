<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingMaster extends Model
{
    protected $guarded = [];

    public function branchMaster()
    {
        return $this->belongsTo(BranchMaster::class, 'branch_master_id');
    }

    public function marketingTarget()
    {
        return $this->belongsTo(MarketingTarget::class, 'marketing_target_id');
    }
}
