<?php

namespace App\Models;

use App\Traits\RoundsDecimalsOnSave;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SavingAccount extends Model
{
    use RoundsDecimalsOnSave;
    protected $fillable = [
        'account_no',
        'cif_id',
        'saving_product_id',
        'branch_id',
        'balance',
        'blocked_balance',
        'status',
        'opened_at',
        'closed_at',
        'created_by',
        'updated_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'balance' => 'decimal:2',
        'blocked_balance' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function getEffectiveBalanceAttribute()
    {
        $minBalance = $this->product->min_balance ?? 0;
        return $this->balance - $this->blocked_balance - $minBalance;
    }

    public function cif(): BelongsTo
    {
        return $this->belongsTo(Cif::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(SavingProduct::class, 'saving_product_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(SavingTransaction::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(SavingBlock::class);
    }

    public function activeBlocks(): HasMany
    {
        return $this->hasMany(SavingBlock::class)->where('status', 'ACTIVE');
    }

    // Loan accounts that use this saving account as auto-debit collateral
    public function loanAccounts()
    {
        return $this->hasMany(LoanAccount::class, 'saving_account_id');
    }

    // Deposit accounts that use this saving account for interest/principal payout
    public function depositAccounts()
    {
        return $this->hasMany(DepositAccount::class, 'saving_account_id');
    }

}
