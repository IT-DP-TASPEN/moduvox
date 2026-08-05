<?php

namespace App\Models;

use App\Enums\JournalState;
use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApiJournal extends Model
{
    use HasAuditFields;

    protected $table = 'api_journals';

    protected $fillable = [
        'batch_id',
        'reff_id',
        'payload',
        'state',
        'core_reff',
        'response_body',
        'retry_count',
        'last_attempt_at',
    ];

    protected function casts(): array
    {
        return [
            'state' => JournalState::class,
            'payload' => 'array',
            'retry_count' => 'integer',
            'last_attempt_at' => 'datetime',
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

    public function logs(): HasMany
    {
        return $this->hasMany(ApiLog::class, 'journal_id');
    }

    /*
    |--------------------------------------------------------------------------
    | State Machine
    |--------------------------------------------------------------------------
    */

    /**
     * Transition ke state baru jika diizinkan.
     */
    public function transitionTo(JournalState $newState): bool
    {
        if (!$this->state->canTransitionTo($newState)) {
            return false;
        }

        $this->state = $newState;

        if ($newState === JournalState::SENDING) {
            $this->last_attempt_at = now();
        }

        if (in_array($newState, [JournalState::RETRY, JournalState::SENDING])) {
            $this->retry_count = $this->retry_count + 1;
        }

        return $this->save();
    }
}
