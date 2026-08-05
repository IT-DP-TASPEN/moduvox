<?php

namespace App\Models;

use App\Traits\RoundsDecimalsOnSave;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DepositTransaction extends Model
{
    use RoundsDecimalsOnSave;
    protected $fillable = [
        'transaction_no',
        'deposit_account_id',
        'transaction_date',
        'type',
        'channel',
        'amount',
        'journal_id',
        'reference_no',
        'description',
        'created_by',
        'updated_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(DepositAccount::class, 'deposit_account_id');
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    public function interestSchedule(): HasOne
    {
        return $this->hasOne(DepositSchedule::class, 'deposit_transaction_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
