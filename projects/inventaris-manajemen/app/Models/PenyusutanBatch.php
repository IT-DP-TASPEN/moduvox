<?php

namespace App\Models;

use App\Enums\DepreciationBatchStatus;
use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenyusutanBatch extends Model
{
    use HasAuditFields;

    protected $table = 'penyusutan_batch';

    protected $fillable = [
        'periode_ym',
        'status',
        'approved_by',
        'approved_at',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'status' => DepreciationBatchStatus::class,
            'approved_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function details(): HasMany
    {
        return $this->hasMany(PenyusutanDetail::class, 'batch_id');
    }

    public function journals(): HasMany
    {
        return $this->hasMany(ApiJournal::class, 'batch_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isClosed(): bool
    {
        return $this->status === DepreciationBatchStatus::CLOSED;
    }

    public function isDraft(): bool
    {
        return $this->status === DepreciationBatchStatus::DRAFT;
    }

    /**
     * Format periode: "202605" → "Juni 2026"
     */
    public function getPeriodeLabelAttribute(): string
    {
        $year = substr($this->periode_ym, 0, 4);
        $month = (int) substr($this->periode_ym, 4, 2);

        $bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return ($bulan[$month] ?? '?') . ' ' . $year;
    }
}
