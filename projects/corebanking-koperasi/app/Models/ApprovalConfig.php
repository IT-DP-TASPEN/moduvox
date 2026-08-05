<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalConfig extends Model
{
    protected $fillable = ['module_key', 'action', 'is_active', 'authorized_roles'];

    protected $casts = [
        'is_active' => 'boolean',
        'authorized_roles' => 'array'
    ];
}
