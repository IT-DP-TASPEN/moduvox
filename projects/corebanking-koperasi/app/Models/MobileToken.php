<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobileToken extends Model
{
    protected $fillable = [
        'mobile_access_id',
        'token',
        'device_id',
        'device_name',
        'expires_at',
        'last_used_at',
    ];

    protected $hidden = [
        'token',
    ];

    protected $casts = [
        'expires_at'   => 'datetime',
        'last_used_at' => 'datetime',
    ];

    public function mobileAccess(): BelongsTo
    {
        return $this->belongsTo(MobileAccess::class);
    }

    /**
     * Cek apakah token masih valid (belum kedaluwarsa).
     */
    public function isValid(): bool
    {
        if ($this->expires_at === null) {
            return true;
        }

        return $this->expires_at->isFuture();
    }

    /**
     * Perbarui timestamp last_used_at.
     */
    public function updateLastUsed(): bool
    {
        $this->last_used_at = now();
        return $this->save();
    }
}
