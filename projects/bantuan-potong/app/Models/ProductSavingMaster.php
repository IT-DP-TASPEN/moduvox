<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSavingMaster extends Model
{
    protected $guarded = [];

    public function setProductNameAttribute($value)
    {
        $this->attributes['product_name'] = strtoupper($value);
    }
}
