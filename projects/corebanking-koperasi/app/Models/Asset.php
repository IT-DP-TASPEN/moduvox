<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

use App\Traits\LogsActivity;
use App\Traits\ApprovesActions;
use App\Traits\RoundsDecimalsOnSave;

class Asset extends Model
{
    use LogsActivity, ApprovesActions, RoundsDecimalsOnSave;

    protected $fillable = [
        'asset_code',
        'name',
        'asset_category_id',
        'branch_id',
        'purchase_date',
        'purchase_price',
        'current_value',
        'accumulated_depreciation',
        'salvage_value',
        'useful_life_years',
        'useful_life_months',
        'depreciation_method',
        'depreciation_rate',
        'depreciation_nominal',
        'current_book_value',
        'serial_number',
        'location',
        'vendor',
        'condition',
        'status',
        'description',
        'created_by',
        'updated_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'purchase_date'          => 'date',
        'purchase_price'         => 'decimal:2',
        'current_value'          => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'salvage_value'          => 'decimal:2',
        'current_book_value'     => 'decimal:2',
        'depreciation_rate'      => 'decimal:2',
        'depreciation_nominal'   => 'decimal:2',
        'useful_life_months'     => 'integer',
        'useful_life_years'      => 'integer',
        'approved_at'            => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Asset $asset) {
            $category = null;

            if ($asset->asset_category_id) {
                $category = AssetCategory::with('parent')->find($asset->asset_category_id);
            }

            if (!$asset->depreciation_method && $category) {
                $asset->depreciation_method = $category->getEffectiveRule('depreciation_method') ?: 'STRAIGHT_LINE';
            }

            if (!$asset->useful_life_months && $category) {
                $asset->useful_life_months = $category->getEffectiveRule('useful_life_months')
                    ?: ((int) ($category->getEffectiveRule('useful_life_years') ?: 0) * 12 ?: null);
            }

            if (!$asset->depreciation_rate && $asset->useful_life_months) {
                $asset->depreciation_rate = round(100 / (int) $asset->useful_life_months, 6);
            }

            if (!$asset->depreciation_nominal && $asset->depreciation_method === 'STRAIGHT_LINE' && $asset->useful_life_months) {
                $asset->depreciation_nominal = round(
                    ((float) $asset->purchase_price - (float) $asset->salvage_value) / (int) $asset->useful_life_months,
                    2
                );
            }

            if ($asset->current_value === null) {
                $asset->current_value = $asset->current_book_value ?? $asset->purchase_price;
            }

            if ($asset->useful_life_years === null && $asset->useful_life_months) {
                $asset->useful_life_years = max(1, (int) ceil((int) $asset->useful_life_months / 12));
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function depreciations(): HasMany
    {
        return $this->hasMany(AssetDepreciation::class)->orderBy('period_year_month', 'desc');
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(AssetRental::class);
    }

    public function activeRentalDepreciationMonths(): ?int
    {
        if ($this->status !== 'RENTED') {
            return null;
        }

        $rental = $this->rentals()
            ->where('status', 'ACTIVE')
            ->latest('rental_start_date')
            ->first();

        if (!$rental?->rental_start_date || !$rental?->rental_end_date) {
            return null;
        }

        $start = Carbon::parse($rental->rental_start_date);
        $end = Carbon::parse($rental->rental_end_date);

        return max(1, (int) ceil($start->diffInMonths($end)));
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Ambil metode penyusutan efektif: gunakan config aset sendiri, fallback ke kategori.
     */
    public function getEffectiveDepreciationMethod(): string
    {
        return $this->depreciation_method
            ?? $this->category?->depreciation_method
            ?? 'STRAIGHT_LINE';
    }

    /**
     * Ambil tarif penyusutan bulanan efektif (% per bulan).
     * Prioritas: depreciation_rate (asset) → category.monthly_rate
     */
    public function getEffectiveMonthlyRate(): float
    {
        if ($months = $this->activeRentalDepreciationMonths()) {
            return round(100 / $months, 6);
        }

        if ($this->depreciation_rate) {
            return (float) $this->depreciation_rate; // sudah dalam % per bulan
        }

        if ($this->useful_life_months) {
            return round(100 / (int) $this->useful_life_months, 6);
        }

        return $this->category?->monthly_rate ?? 0;
    }

    /**
     * Ambil nominal penyusutan bulanan efektif (untuk STRAIGHT_LINE).
     * Prioritas: depreciation_nominal (asset) → hitung dari category annual rate.
     */
    public function getEffectiveMonthlyNominal(): float
    {
        if ($months = $this->activeRentalDepreciationMonths()) {
            return round(
                ((float) $this->purchase_price - (float) $this->salvage_value) / $months,
                2
            );
        }

        if ($this->depreciation_nominal) {
            return (float) $this->depreciation_nominal;
        }

        if ($this->useful_life_months) {
            return round(
                ((float) $this->purchase_price - (float) $this->salvage_value) / (int) $this->useful_life_months,
                2
            );
        }

        // Hitung dari harga perolehan dan tarif tahunan kategori
        $annualRate = $this->category?->depreciation_rate_annual ?? 0;
        return $annualRate > 0
            ? round((float) $this->purchase_price * ($annualRate / 100) / 12, 2)
            : 0;
    }

    /**
     * Calculate the depreciation amount for this asset based on current book value.
     */
    public function calculateDepreciation(): float
    {
        if ((float) $this->current_book_value <= (float) $this->salvage_value || !in_array($this->status, ['ACTIVE', 'RENTED'], true)) {
            return 0;
        }

        $method = $this->getEffectiveDepreciationMethod();

        if ($method === 'PERCENTAGE') {
            $rate   = $this->getEffectiveMonthlyRate();
            $amount = (float) $this->current_book_value * ($rate / 100);
        } else {
            $amount = $this->getEffectiveMonthlyNominal();
        }

        $maxDepreciable = (float) $this->current_book_value - (float) $this->salvage_value;
        return min((float) $amount, $maxDepreciable);
    }
}
