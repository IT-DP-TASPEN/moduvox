<?php

namespace App\Models;

use App\Traits\RoundsDecimalsOnSave;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetDepreciation extends Model
{
    use RoundsDecimalsOnSave;
    protected $fillable = [
        'asset_id',
        'period_year_month',
        'depreciation_date',
        'depreciation_amount',
        'accumulated_depreciation_after',
        'book_value_after',
        'journal_id',
        'created_by',
    ];

    protected $casts = [
        'depreciation_date' => 'date',
        'depreciation_amount' => 'decimal:2',
        'accumulated_depreciation_after' => 'decimal:2',
        'book_value_after' => 'decimal:2',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
