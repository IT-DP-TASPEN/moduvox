<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermitRequest extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'requested_at',
        'notes',
        'latitude',
        'longitude',
        'photo_path',
        'status',
        'approved_by',
        'approved_at',
        'admin_note',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}

