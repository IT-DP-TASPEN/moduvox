<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoaMovement extends Model
{
    protected $fillable = [
        'branch_id',
        'coa_id',
        'transaction_date',
        'starting_balance',
        'debit',
        'credit',
        'ending_balance',
    ];

    protected $casts = [
        'transaction_date' => 'date',
    ];

    public function coa(): BelongsTo
    {
        return $this->belongsTo(Coa::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
