<?php

namespace App\Models;

use App\Traits\RoundsDecimalsOnSave;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AssetRental extends Model
{
    use RoundsDecimalsOnSave;
    protected $fillable = [
        'contract_no',
        'asset_id',
        'rekanan_id',
        'branch_id',
        'rental_start_date',
        'rental_end_date',
        'monthly_rate',
        'payment_due_day',
        'status',
        'notes',
        'created_by',
        'updated_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'rental_start_date' => 'date',
        'rental_end_date'   => 'date',
        'monthly_rate'      => 'decimal:2',
        'payment_due_day'   => 'integer',
        'approved_at'       => 'datetime',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function rekanan(): BelongsTo
    {
        return $this->belongsTo(Rekanan::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function billings(): HasMany
    {
        return $this->hasMany(AssetRentalBilling::class)->orderBy('billing_period');
    }

    public function latestPaidBilling(): HasOne
    {
        return $this->hasOne(AssetRentalBilling::class)->where('status', 'PAID')->latestOfMany('paid_at');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
