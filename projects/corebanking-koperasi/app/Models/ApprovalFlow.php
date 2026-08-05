<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalFlow extends Model
{
    protected $fillable = ['code', 'name'];

    public function steps()
    {
        return $this->hasMany(ApprovalFlowStep::class);
    }
}
