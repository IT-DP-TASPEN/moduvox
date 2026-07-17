<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutsideDutyRequest extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'start_at',
        'end_at',
        'overtime_minutes',
        'break_minutes',
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
        'start_at' => 'datetime',
        'end_at' => 'datetime',
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

