<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'company_id', 'name', 'branch_code', 'is_active', 'address', 'phones', 'social_media', 'description'
    ];

    protected $casts = [
        'phones' => 'array',
        'social_media' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
