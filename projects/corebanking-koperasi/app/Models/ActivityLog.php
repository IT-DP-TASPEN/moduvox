<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 'action', 'model_type', 'model_id', 
        'description', 'metadata', 'ip_address', 'user_agent'
    ];

    protected $casts = [
        'metadata' => 'json'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }}
