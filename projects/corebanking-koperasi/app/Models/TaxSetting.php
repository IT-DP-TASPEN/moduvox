<?php

namespace App\Models;

use App\Traits\RoundsDecimalsOnSave;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxSetting extends Model
{
    use RoundsDecimalsOnSave;

    protected $fillable = [
        'name',
        'tax_rate',
        'calculation_base',
        'expense_coa_id',
        'payable_coa_id',
        'effective_from',
        'effective_to',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tax_rate' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
    ];

    public static function effectiveFor(string $date): ?self
    {
        return self::query()
            ->where('is_active', true)
            ->whereDate('effective_from', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $date);
            })
            ->orderByDesc('effective_from')
            ->first();
    }

    public function expenseCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'expense_coa_id');
    }

    public function payableCoa(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'payable_coa_id');
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
