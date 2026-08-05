<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coa extends Model
{
    protected $fillable = [
        'coa_code',
        'name',
        'type',
        'parent_id',
        'is_leaf',
        'is_cash',
        'is_active',
    ];

    protected $casts = [
        'is_leaf' => 'boolean',
        'is_cash' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Coa::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Coa::class, 'parent_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLeaf($query)
    {
        return $query->where('is_leaf', true);
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->type) {
            'ASSET' => 'Aset',
            'LIABILITY' => 'Kewajiban',
            'EQUITY' => 'Modal',
            'REVENUE' => 'Pendapatan',
            'EXPENSE' => 'Beban',
            default => 'Unknown',
        };
    }
}
