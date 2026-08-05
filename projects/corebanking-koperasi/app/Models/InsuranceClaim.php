<?php

namespace App\Models;

use App\Traits\RoundsDecimalsOnSave;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsuranceClaim extends Model
{
    use RoundsDecimalsOnSave;
    protected $fillable = [
        'claim_no',
        'loan_account_id',
        'loan_insurance_policy_id',
        'incident_date',
        'submission_date',
        'approval_date',
        'payment_date',
        'claim_amount',
        'approved_amount',
        'paid_amount',
        'status',
        'remarks',
        'recognition_journal_id',
        'payment_journal_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'submission_date' => 'date',
        'approval_date' => 'date',
        'payment_date' => 'date',
        'claim_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function loanAccount(): BelongsTo
    {
        return $this->belongsTo(LoanAccount::class);
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(LoanInsurancePolicy::class, 'loan_insurance_policy_id');
    }

    public function recognitionJournal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'recognition_journal_id');
    }

    public function paymentJournal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'payment_journal_id');
    }
}
