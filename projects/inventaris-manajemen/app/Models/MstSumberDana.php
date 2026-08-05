<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MstSumberDana extends Model
{
    use HasAuditFields, SoftDeletes;

    protected $table = 'mst_sumber_dana';

    protected $fillable = [
        'kode',
        'nama',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function inventaris(): HasMany
    {
        return $this->hasMany(Inventaris::class, 'sumber_id');
    }
}
