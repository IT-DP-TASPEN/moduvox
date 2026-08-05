<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MstKantor extends Model
{
    use HasAuditFields, SoftDeletes;

    protected $table = 'mst_kantor';

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
        return $this->hasMany(Inventaris::class, 'kantor_id');
    }

    public function ruangan(): HasMany
    {
        return $this->hasMany(MstRuangan::class, 'kantor_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'kantor_id');
    }
}
