<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvImprovement extends Model
{
    use HasAuditFields;

    protected $table = 'inv_improvement';

    protected $fillable = [
        'inventaris_id',
        'nilai_tambah',
        'tgl_efektif',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'nilai_tambah' => 'decimal:2',
            'tgl_efektif' => 'date',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function inventaris(): BelongsTo
    {
        return $this->belongsTo(Inventaris::class, 'inventaris_id');
    }
}
