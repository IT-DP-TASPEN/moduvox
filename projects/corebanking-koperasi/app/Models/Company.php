<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'company_name', 'company_code', 'is_active', 'address', 'phones', 'social_media', 'description'
    ];

    protected $casts = [
        'phones' => 'array',
        'social_media' => 'array',
    ];

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
