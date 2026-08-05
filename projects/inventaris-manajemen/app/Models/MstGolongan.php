<?php

namespace App\Models;

use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MstGolongan extends Model
{
    use HasAuditFields, SoftDeletes;

    protected $table = 'mst_golongan';

    protected $fillable = [
        'kode',
        'nama',
        'umur_standar',
        'akun_debet',
        'akun_kredit',
    ];

    protected function casts(): array
    {
        return [
            'umur_standar' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function inventaris(): HasMany
    {
        return $this->hasMany(Inventaris::class, 'golongan_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Label singkat untuk tampilan tabel.
     * Contoh: "Gol I", "Gol II", "Bgn Perm", dsb.
     */
    public function getShortLabelAttribute(): string
    {
        return match ($this->kode) {
            '01'    => 'Tanah',
            '02'    => 'Gol I',
            '03'    => 'Gol II',
            '04'    => 'Gol III',
            '05'    => 'Gol IV',
            '06'    => 'Bgn Perm',
            '07'    => 'Bgn Non-P',
            default => $this->nama,
        };
    }

    /**
     * Cek apakah golongan ini adalah Tanah (tidak disusutkan).
     */
    public function isTanah(): bool
    {
        return in_array($this->kode, ['01', '1']);
    }
}
