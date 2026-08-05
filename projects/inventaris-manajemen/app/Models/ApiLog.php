<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiLog extends Model
{
    protected $table = 'api_logs';

    protected $fillable = [
        'journal_id',
        'endpoint',
        'method',
        'request_payload',
        'response_payload',
        'http_status',
        'duration_ms',
    ];

    protected function casts(): array
    {
        return [
            'request_payload' => 'array',
            'response_payload' => 'array',
            'http_status' => 'integer',
            'duration_ms' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function journal(): BelongsTo
    {
        return $this->belongsTo(ApiJournal::class, 'journal_id');
    }
}
