<?php

namespace App\Models;

use App\Services\CoaMovementService;
use App\Traits\RoundsDecimalsOnSave;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntry extends Model
{
    use RoundsDecimalsOnSave;
    protected $fillable = [
        'journal_id',
        'coa_id',
        'reference_no',
        'description',
        'debit',
        'credit',
    ];

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    public function coa(): BelongsTo
    {
        return $this->belongsTo(Coa::class);
    }

    protected static function booted(): void
    {
        static::created(function (JournalEntry $entry) {
            if ($entry->journal?->status === 'APPROVED') {
                app(CoaMovementService::class)->syncForJournalEntry($entry);
            }
        });

        static::updated(function (JournalEntry $entry) {
            if ($entry->journal?->status === 'APPROVED') {
                app(CoaMovementService::class)->syncForJournalEntry($entry);
            }
        });

        static::deleted(function (JournalEntry $entry) {
            if ($entry->journal?->status === 'APPROVED') {
                app(CoaMovementService::class)->syncForJournalEntry($entry);
            }
        });
    }
}
