<?php

namespace App\Models;

use App\Traits\RoundsDecimalsOnSave;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetRentalBilling extends Model
{
    use RoundsDecimalsOnSave;
    protected $fillable = [
        'asset_rental_id',
        'billing_period',
        'billing_date',
        'due_date',
        'amount',
        'status',
        'paid_at',
        'payment_reference',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'billing_date' => 'date',
        'due_date'     => 'date',
        'amount'       => 'decimal:2',
        'paid_at'      => 'datetime',
    ];

    public function rental(): BelongsTo
    {
        return $this->belongsTo(AssetRental::class, 'asset_rental_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
