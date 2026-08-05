<?php

namespace App\Models;

use App\Traits\RoundsDecimalsOnSave;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingProduct extends Model
{
    use RoundsDecimalsOnSave;
    protected $fillable = [
        'product_code',
        'name',
        'is_active',
        'interest_calculation_type',
        'interest_rate',
        'interest_payment_period',
        'min_initial_deposit',
        'min_balance',
        'max_balance',
        'has_overdraft',
        'has_admin_fee',
        'admin_fee',
        'has_closing_fee',
        'closed_fee',
        'min_balance_penalty',
        'min_balance_penalty_period',
        'has_automatic_dormant',
        'no_transaction_monthly_terms',
        'no_transaction_penalty',
        'dormant_penalty_grace_period',
        'dormant_penalty_amount',
        'has_tax_on_interest',
        'tax_rate',
        'liability_coa_id',
        'interest_expense_coa_id',
        'admin_fee_revenue_coa_id',
        'tax_liability_coa_id',
        'accrued_interest_payable_coa_id',
        'interest_payable_coa_id',
        'default_cash_coa_id',
        'default_bank_coa_id',
        'penalty_revenue_coa_id',
        'aba_transit_coa_id',
        'fee_name',
        'fee_amount',
        'fee_type',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'has_overdraft' => 'boolean',
        'has_admin_fee' => 'boolean',
        'has_closing_fee' => 'boolean',
        'has_automatic_dormant' => 'boolean',
        'has_tax_on_interest' => 'boolean',
        'interest_rate' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'approved_at' => 'datetime',
        'min_initial_deposit' => 'integer',
        'min_balance' => 'integer',
        'max_balance' => 'integer',
        'admin_fee' => 'integer',
        'closed_fee' => 'integer',
        'min_balance_penalty' => 'integer',
        'no_transaction_penalty' => 'integer',
        'dormant_penalty_amount' => 'integer',
        'fee_amount' => 'integer',
    ];

    public function liabilityCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'liability_coa_id');
    }

    public function interestExpenseCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'interest_expense_coa_id');
    }

    public function adminFeeRevenueCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'admin_fee_revenue_coa_id');
    }

    public function taxLiabilityCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'tax_liability_coa_id');
    }

    public function accruedInterestPayableCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'accrued_interest_payable_coa_id');
    }

    public function defaultCashCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'default_cash_coa_id');
    }

    public function defaultBankCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'default_bank_coa_id');
    }

    public function penaltyRevenueCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'penalty_revenue_coa_id');
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
