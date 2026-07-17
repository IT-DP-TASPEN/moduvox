<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    protected $guarded = [];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'overtime_meal_pay' => 'decimal:2',
        'tax_allowance' => 'decimal:2',
        'position_allowance' => 'decimal:2',
        'performance_allowance' => 'decimal:2',
        'income_tax' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'total_non_thp' => 'decimal:2',
        'total_gross' => 'decimal:2',
        'base_allowance_snapshot' => 'decimal:2',
        'kpi_score_snapshot' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
