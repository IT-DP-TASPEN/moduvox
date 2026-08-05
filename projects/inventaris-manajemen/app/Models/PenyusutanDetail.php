<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenyusutanDetail extends Model
{
    use HasAuditFields;

    protected $table = 'penyusutan_detail';

    protected $fillable = [
        'batch_id',
        'inventaris_id',
        'kantor_id',
        'beban_bulan_ini',
        'nilai_buku_sebelum',
        'nilai_buku_sesudah',
        'akumulasi',
    ];

    protected function casts(): array
    {
        return [
            'beban_bulan_ini' => 'decimal:2',
            'nilai_buku_sebelum' => 'decimal:2',
            'nilai_buku_sesudah' => 'decimal:2',
            'akumulasi' => 'decimal:2',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function batch(): BelongsTo
    {
        return $this->belongsTo(PenyusutanBatch::class, 'batch_id');
    }

    public function inventaris(): BelongsTo
    {
        return $this->belongsTo(Inventaris::class, 'inventaris_id')->withTrashed();
    }

    public function kantor(): BelongsTo
    {
        return $this->belongsTo(MstKantor::class, 'kantor_id');
    }
}
