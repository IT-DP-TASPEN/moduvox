<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    protected $fillable = [
        'code',
        'name',
        'address',
        'latitude',
        'longitude',
        'radius'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function additionalUsers()
    {
        return $this->belongsToMany(User::class, 'office_user');
    }

    public function approverSetting()
    {
        return $this->hasOne(OfficeApprover::class);
    }
}
