<?php

namespace App\Models;

use App\Traits\RoundsDecimalsOnSave;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepositProduct extends Model
{
    use RoundsDecimalsOnSave;
    protected $fillable = [
        'product_code',
        'name',
        'is_active',
        'min_term',
        'max_term',
        'term_unit',
        'min_amount',
        'max_amount',
        'min_interest_rate',
        'max_interest_rate',
        'interest_period',
        'interest_calculation_type',
        'tax_rate',
        'liability_coa_id',
        'interest_expense_coa_id',
        'accrued_interest_payable_coa_id',
        'tax_liability_coa_id',
        'admin_fee_revenue_coa_id',
        'interest_payable_coa_id',
        'default_cash_coa_id',
        'default_bank_coa_id',
        'kas_coa_id',
        'aba_transit_coa_id',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'min_term' => 'integer',
        'max_term' => 'integer',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'min_interest_rate' => 'decimal:2',
        'max_interest_rate' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function liabilityCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'liability_coa_id');
    }

    public function interestExpenseCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'interest_expense_coa_id');
    }

    public function accruedInterestPayableCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'accrued_interest_payable_coa_id');
    }

    public function taxLiabilityCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'tax_liability_coa_id');
    }

    public function adminFeeRevenueCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'admin_fee_revenue_coa_id');
    }

    public function defaultCashCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'default_cash_coa_id');
    }

    public function defaultBankCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'default_bank_coa_id');
    }

    public function cashCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'kas_coa_id');
    }

    public function interestPayableCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'interest_payable_coa_id');
    }

    public function abaTransitCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'aba_transit_coa_id');
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
