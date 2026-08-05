<?php

namespace App\Models;

use App\Traits\RoundsDecimalsOnSave;
use Illuminate\Database\Eloquent\Model;

class LoanSchedule extends Model
{
    use RoundsDecimalsOnSave;
    protected $fillable = [
        'loan_account_id',
        'installment_number',
        'due_date',
        'principal_amount',
        'interest_amount',
        'penalty_amount',
        'principal_paid',
        'interest_paid',
        'penalty_paid',
        'status',
        'payment_date',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'payment_date' => 'datetime',
        'principal_amount' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
        'principal_paid' => 'decimal:2',
        'interest_paid' => 'decimal:2',
        'penalty_paid' => 'decimal:2',
        'installment_number' => 'integer',
    ];

    public function loanAccount()
    {
        return $this->belongsTo(LoanAccount::class, 'loan_account_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
