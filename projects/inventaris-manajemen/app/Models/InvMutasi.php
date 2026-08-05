<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvMutasi extends Model
{
    use HasAuditFields;

    protected $table = 'inv_mutasi';

    protected $fillable = [
        'inventaris_id',
        'jenis_mutasi',
        'kantor_asal_id',
        'kantor_tujuan_id',
        'ruangan_asal_id',
        'ruangan_tujuan_id',
        'tgl_mutasi',
        'keterangan',
        'status',
        'user_id',
        'approval_user_id',
    ];

    protected function casts(): array
    {
        return [
            'tgl_mutasi' => 'date',
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

    public function kantorAsal(): BelongsTo
    {
        return $this->belongsTo(MstKantor::class, 'kantor_asal_id');
    }

    public function kantorTujuan(): BelongsTo
    {
        return $this->belongsTo(MstKantor::class, 'kantor_tujuan_id');
    }

    public function ruanganAsal(): BelongsTo
    {
        return $this->belongsTo(MstRuangan::class, 'ruangan_asal_id');
    }

    public function ruanganTujuan(): BelongsTo
    {
        return $this->belongsTo(MstRuangan::class, 'ruangan_tujuan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approvalUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approval_user_id');
    }
}
