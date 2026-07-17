<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiRecord extends Model
{
    protected $guarded = [];

    protected $casts = [
        'score' => 'decimal:2',
        'indicators' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
