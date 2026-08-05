<?php

namespace App\Models;

use App\Traits\RoundsDecimalsOnSave;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingBlock extends Model
{
    use RoundsDecimalsOnSave;
    protected $fillable = [
        'saving_account_id',
        'amount',
        'reference_no',
        'reason',
        'status',
        'created_by',
        'released_by',
        'released_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'released_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(SavingAccount::class, 'saving_account_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function releaser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }
}
