<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\LogsActivity;
use App\Traits\RoundsDecimalsOnSave;

class LoanTransaction extends Model
{
    use LogsActivity, RoundsDecimalsOnSave;

    protected $fillable = [
        'loan_account_id',
        'reference_number',
        'transaction_type',
        'channel',
        'reversed_by_transaction_id',
        'amount_principal',
        'amount_interest',
        'amount_penalty',
        'amount_admin_fee',
        'amount_provision',
        'amount_insurance_fee',
        'total_amount',
        'journal_id',
        'description',
        'created_by',
        'updated_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'amount_principal' => 'decimal:2',
        'amount_interest' => 'decimal:2',
        'amount_penalty' => 'decimal:2',
        'amount_admin_fee' => 'decimal:2',
        'amount_provision' => 'decimal:2',
        'amount_insurance_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function getTransactionDateAttribute()
    {
        return $this->created_at;
    }

    public function loanAccount()
    {
        return $this->belongsTo(LoanAccount::class, 'loan_account_id');
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class, 'journal_id');
    }

    public function reversedBy()
    {
        return $this->belongsTo(LoanTransaction::class, 'reversed_by_transaction_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
