<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceSummary extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'is_working_day',
        'is_attendance',
        'check_in',
        'check_out',
        'duration_minutes',
        'late_minutes',
        'early_departure_minutes',
        'leave_type',
        'leave_days',
        'permit_count',
        'outside_duty_count',
        'overtime_minutes',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'is_working_day' => 'boolean',
        'is_attendance' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
