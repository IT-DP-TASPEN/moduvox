<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MstRuangan extends Model
{
    use HasAuditFields, SoftDeletes;

    protected $table = 'mst_ruangan';

    protected $fillable = [
        'kode',
        'nama',
        'kantor_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function kantor(): BelongsTo
    {
        return $this->belongsTo(MstKantor::class, 'kantor_id');
    }
}
