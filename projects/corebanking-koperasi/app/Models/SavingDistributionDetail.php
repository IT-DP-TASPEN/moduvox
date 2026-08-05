<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingDistributionDetail extends Model
{
    protected $fillable = [
        'saving_distribution_id',
        'saving_account_id',
        'amount',
        'balance_before',
        'balance_after',
        'status',
        'note',
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after'  => 'decimal:2',
    ];

    public function distribution(): BelongsTo
    {
        return $this->belongsTo(SavingDistribution::class, 'saving_distribution_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(SavingAccount::class, 'saving_account_id');
    }
}
