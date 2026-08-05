<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rekanan extends Model
{
    protected $table = 'rekanan';

    protected $fillable = [
        'rekanan_code',
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'npwp',
        'bank_name',
        'bank_account_no',
        'bank_account_name',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function rentals(): HasMany
    {
        return $this->hasMany(AssetRental::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
