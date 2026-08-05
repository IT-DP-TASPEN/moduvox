<?php

namespace App\Models;

use App\Traits\RoundsDecimalsOnSave;
use Illuminate\Database\Eloquent\Model;

class InsuranceRate extends Model
{
    use RoundsDecimalsOnSave;
    protected $fillable = [
        'insurance_product_id',
        'age',
        'tenor_months',
        'rate',
    ];

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(InsuranceProduct::class, 'insurance_product_id');
    }
}
