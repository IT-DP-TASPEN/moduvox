<?php

namespace App\Models;

use App\Services\CoaMovementService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Journal extends Model
{
    protected $fillable = [
        'branch_id',
        'transaction_date',
        'reference_no',
        'description',
        'revision_notes',
        'journal_type',
        'original_journal_id',
        'is_revision',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'approved_at'      => 'datetime',
        'is_revision'      => 'boolean',
    ];

    // Jurnal tipe: SYSTEM (otomatis), MANUAL (input user), REVERSAL
    const TYPE_SYSTEM   = 'SYSTEM';
    const TYPE_MANUAL   = 'MANUAL';
    const TYPE_REVERSAL = 'REVERSAL';

    public function entries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /** Jurnal asal yang di-reverse oleh jurnal ini */
    public function originalJournal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'original_journal_id');
    }

    /** Jurnal reversal yang membalik jurnal ini */
    public function reversalJournal(): HasMany
    {
        return $this->hasMany(Journal::class, 'original_journal_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getTotalDebitAttribute()
    {
        return $this->entries()->sum('debit');
    }

    public function getTotalCreditAttribute()
    {
        return $this->entries()->sum('credit');
    }

    public function isBalanced(): bool
    {
        return bccomp($this->total_debit, $this->total_credit, 2) === 0;
    }

    protected static function booted(): void
    {
        static::updated(function (Journal $journal) {
            if (
                $journal->wasChanged(['status', 'transaction_date', 'branch_id']) &&
                in_array($journal->status, ['APPROVED', 'REJECTED'], true)
            ) {
                app(CoaMovementService::class)->syncForJournal($journal);
            }
        });
    }
}
