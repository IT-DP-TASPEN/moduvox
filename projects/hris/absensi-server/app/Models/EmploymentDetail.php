<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmploymentDetail extends Model
{
    protected $guarded = [];

    protected $fillable = [
        'user_id',
        'join_date',
        'contract_end_date',
        'employment_status',
        'department',
        'position',
        'position_id',
        'allowance_override',
        'company_account_number',
        'grade',
        'skg',
        'dplk_bni_account_number',
        'leave_quota',
        'remaining_leave',
        'bpjs_ketenagakerjaan_no',
        'bpjs_kesehatan_no',
        'npwp',
        'basic_salary',
        'position_allowance',
        'max_performance_allowance'
    ];

    protected $casts = [
        'leave_quota' => 'integer',
        'remaining_leave' => 'integer',
        'basic_salary' => 'decimal:2',
        'position_allowance' => 'decimal:2',
        'max_performance_allowance' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function positionMaster()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }
}
