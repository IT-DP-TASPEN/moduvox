<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Traits\LogsActivity;
use App\Traits\RoundsDecimalsOnSave;

class LoanProduct extends Model
{
    use LogsActivity, RoundsDecimalsOnSave;

    protected $fillable = [
        'product_code',
        'name',
        'is_active',
        'is_diskonto',
        'calculation_method',
        'interest_rate_min',
        'interest_rate_max',
        'provision_rate',
        'admin_rate',
        'penalty_rate',
        'tenor_min',
        'tenor_max',
        'tenor_type',
        'principal_coa_id',
        'accrued_interest_coa_id',
        'accrued_interest_receivable_coa_id',
        'interest_revenue_coa_id',
        'deferred_interest_coa_id',
        'provision_revenue_coa_id',
        'admin_fee_revenue_coa_id',
        'insurance_revenue_coa_id',
        'flagging_revenue_coa_id',
        'stamp_duty_payable_coa_id',
        'penalty_revenue_coa_id',
        'default_cash_coa_id',
        'default_bank_coa_id',
        'ckpn_coa_id',
        'suspense_coa_id',
        'aba_transit_coa_id',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'is_diskonto'  => 'boolean',
        'interest_rate_min' => 'decimal:2',
        'interest_rate_max' => 'decimal:2',
        'provision_rate' => 'decimal:2',
        'admin_rate'   => 'decimal:2',
        'penalty_rate' => 'decimal:2',
        'tenor_min'    => 'integer',
        'tenor_max'    => 'integer',
        'approved_at'  => 'datetime',
    ];

    public function principalCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'principal_coa_id');
    }

    public function accruedInterestCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'accrued_interest_coa_id');
    }

    public function interestRevenueCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'interest_revenue_coa_id');
    }

    public function deferredInterestCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'deferred_interest_coa_id');
    }

    public function provisionRevenueCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'provision_revenue_coa_id');
    }

    public function adminFeeRevenueCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'admin_fee_revenue_coa_id');
    }

    public function penaltyRevenueCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'penalty_revenue_coa_id');
    }

    public function insuranceRevenueCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'insurance_revenue_coa_id');
    }

    public function flaggingRevenueCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'flagging_revenue_coa_id');
    }

    public function stampDutyPayableCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'stamp_duty_payable_coa_id');
    }

    public function defaultCashCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'default_cash_coa_id');
    }

    public function defaultBankCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'default_bank_coa_id');
    }

    public function ckpnCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'ckpn_coa_id');
    }

    public function suspenseCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'suspense_coa_id');
    }

    public function abaTransitCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'aba_transit_coa_id');
    }

    public function accruedInterestReceivableCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'accrued_interest_receivable_coa_id');
    }


    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
