<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class ApprovalFlowStep extends Model
{
    protected $fillable = ['approval_flow_id', 'step_no', 'role_id'];

    public function approvalFlow()
    {
        return $this->belongsTo(ApprovalFlow::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
