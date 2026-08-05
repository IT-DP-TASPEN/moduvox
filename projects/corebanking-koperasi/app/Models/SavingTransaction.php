<?php

namespace App\Models;

use App\Traits\RoundsDecimalsOnSave;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SavingTransaction extends Model
{
    use RoundsDecimalsOnSave;

    private const DEBIT_TYPES = ['WITHDRAWAL', 'TRANSFER_OUT', 'TAX', 'FEE', 'BLOCK'];
    private const CREDIT_TYPES = ['DEPOSIT', 'TRANSFER_IN', 'INTEREST', 'UNBLOCK'];
    protected $fillable = [
        'transaction_no',
        'saving_account_id',
        'transaction_date',
        'type',
        'channel',
        'amount',
        'balance_after',
        'journal_id',
        'reference_no',
        'description',
        'created_by',
        'updated_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(SavingAccount::class, 'saving_account_id');
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    public function originalTransaction(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reference_no', 'transaction_no');
    }

    public function reversalTransaction(): HasOne
    {
        return $this->hasOne(self::class, 'reference_no', 'transaction_no')->where('type', 'REVERSAL');
    }

    public function isDebitMutation(): bool
    {
        if ($this->type === 'REVERSAL') {
            return $this->originalTransaction?->isCreditMutation() ?? false;
        }

        return in_array($this->type, self::DEBIT_TYPES, true);
    }

    public function isCreditMutation(): bool
    {
        if ($this->type === 'REVERSAL') {
            return $this->originalTransaction?->isDebitMutation() ?? false;
        }

        return in_array($this->type, self::CREDIT_TYPES, true);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
