<?php

namespace App\Models;

use App\Traits\RoundsDecimalsOnSave;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DepositAccount extends Model
{
    use RoundsDecimalsOnSave;
    protected $fillable = [
        'account_no',
        'cif_id',
        'deposit_product_id',
        'deposit_bilyet_id',
        'amount',
        'source_of_funds',
        'reason',
        'interest_rate',
        'tenor',
        'interest_calculation_type',
        'placement_date',
        'maturity_date',
        'rollover_type',
        'fund_channel',
        'saving_account_id',
        'branch_id',
        'marketing_id',
        'status',
        'closed_at',
        'created_by',
        'updated_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'interest_rate'  => 'decimal:2',
        'placement_date' => 'date',
        'maturity_date'  => 'date',
        'closed_at'      => 'datetime',
        'approved_at'    => 'datetime',
    ];

    public function cif(): BelongsTo
    {
        return $this->belongsTo(Cif::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(DepositProduct::class, 'deposit_product_id');
    }

    public function bilyet(): BelongsTo
    {
        return $this->belongsTo(DepositBilyet::class, 'deposit_bilyet_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function marketing(): BelongsTo
    {
        return $this->belongsTo(MarketingMaster::class, 'marketing_id');
    }

    public function savingAccount(): BelongsTo
    {
        return $this->belongsTo(SavingAccount::class, 'saving_account_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(DepositTransaction::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(DepositSchedule::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
