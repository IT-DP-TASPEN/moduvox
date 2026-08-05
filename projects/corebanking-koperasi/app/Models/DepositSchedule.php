<?php

namespace App\Models;

use App\Traits\RoundsDecimalsOnSave;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepositSchedule extends Model
{
    use RoundsDecimalsOnSave;
    protected $fillable = [
        'deposit_account_id',
        'month_index',
        'schedule_date',
        'gross_interest',
        'tax_amount',
        'net_interest',
        'status', // PENDING, PAID
        'payment_date',
        'deposit_transaction_id',
    ];

    protected $casts = [
        'schedule_date'  => 'date',
        'payment_date'   => 'datetime',
        'gross_interest' => 'decimal:2',
        'tax_amount'     => 'decimal:2',
        'net_interest'   => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(DepositAccount::class, 'deposit_account_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(DepositTransaction::class, 'deposit_transaction_id');
    }
}
