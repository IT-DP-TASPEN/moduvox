<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SavingDistribution extends Model
{
    protected $fillable = [
        'distribution_no',
        'distribution_type',
        'channel',
        'saving_product_id',
        'counterpart_coa_id',
        'amount_per_account',
        'total_amount',
        'account_count',
        'description',
        'effective_date',
        'status',
        'journal_id',
        'executed_at',
        'executed_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'executed_at'    => 'datetime',
        'amount_per_account' => 'decimal:2',
        'total_amount'       => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(SavingProduct::class, 'saving_product_id');
    }

    public function counterpartCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'counterpart_coa_id');
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'journal_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(SavingDistributionDetail::class, 'saving_distribution_id');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'EXECUTED'  => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'PENDING'   => 'bg-amber-50 text-amber-700 border-amber-200',
            'CANCELLED' => 'bg-red-50 text-red-700 border-red-200',
            default     => 'bg-slate-50 text-slate-600 border-slate-200',
        };
    }
}
