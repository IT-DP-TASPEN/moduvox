<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AssetCategory extends Model
{
    protected $fillable = [
        'code',
        'parent_id',
        'name',
        'description',
        'is_active',
        'coa_aset_id',
        'coa_akum_penyusutan_id',
        'coa_beban_penyusutan_id',
        'coa_kas_id',
        'depreciation_method',
        'depreciation_rate_annual',
        'useful_life_months',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active'               => 'boolean',
        'depreciation_rate_annual' => 'decimal:2',
        'useful_life_months'      => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(AssetCategory::class, 'parent_id');
    }

    protected static function booted(): void
    {
        static::creating(function (AssetCategory $category) {
            if (!$category->code) {
                $category->code = static::generateCode($category->name);
            }

            if (!$category->created_by && Auth::id()) {
                $category->created_by = Auth::id();
            }
        });
    }

    private static function generateCode(string $name): string
    {
        $base = Str::upper(Str::slug($name, '-')) ?: 'ASSET-CATEGORY';
        $code = $base;
        $sequence = 1;

        while (static::where('code', $code)->exists()) {
            $code = $base . '-' . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
            $sequence++;
        }

        return $code;
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function coaAset(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'coa_aset_id');
    }

    public function coaAkumPenyusutan(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'coa_akum_penyusutan_id');
    }

    public function coaBebanPenyusutan(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'coa_beban_penyusutan_id');
    }

    public function coaKas(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'coa_kas_id');
    }

    /**
     * Check apakah mapping COA sudah lengkap untuk posting jurnal (termasuk inheritance)
     */
    public function hasCompleteCOAMapping(): bool
    {
        return $this->getEffectiveRule('coa_aset_id')
            && $this->getEffectiveRule('coa_akum_penyusutan_id')
            && $this->getEffectiveRule('coa_beban_penyusutan_id')
            && $this->getEffectiveRule('coa_kas_id');
    }

    /**
     * Konversi tarif tahunan ke tarif bulanan (% per bulan)
     */
    public function getMonthlyRateAttribute(): float
    {
        return $this->depreciation_rate_annual
            ? round((float) $this->depreciation_rate_annual / 12, 6)
            : 0;
    }

    /**
     * Label metode penyusutan
     */
    public function getDepreciationMethodLabelAttribute(): string
    {
        return match ($this->depreciation_method) {
            'PERCENTAGE'   => 'Saldo Menurun',
            'STRAIGHT_LINE' => 'Garis Lurus',
            default        => '-',
        };
    }

    /**
     * Scope untuk kategori utama (top-level)
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Ambil rule penyusutan: jika sub-kategori kosong, ambil dari parent
     */
    public function getEffectiveRule($field)
    {
        if ($this->$field) {
            return $this->$field;
        }
        
        return $this->parent ? $this->parent->$field : null;
    }

    /**
     * Apakah konfigurasi penyusutan sudah lengkap (termasuk inheritance)
     */
    public function hasDepreciationConfig(): bool
    {
        return $this->getEffectiveRule('depreciation_method')
            && $this->getEffectiveRule('depreciation_rate_annual')
            && $this->getEffectiveRule('useful_life_months');
    }
}
