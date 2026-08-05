<?php

namespace App\Models;

use App\Traits\RoundsDecimalsOnSave;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InsuranceProduct extends Model
{
    use RoundsDecimalsOnSave;
    protected $fillable = [
        'insurance_provider_id',
        'product_code',
        'name',
        'type',
        'calculation_base',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(InsuranceProvider::class, 'insurance_provider_id');
    }

    public function policies(): HasMany
    {
        return $this->hasMany(LoanInsurancePolicy::class);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(InsuranceRate::class);
    }
}
