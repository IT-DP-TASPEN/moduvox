<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountPerpouseMaster extends Model
{
    protected $guarded = [];

    public function accountPerpouseDetails()
    {
        return $this->hasMany(AccountPerpouseDetail::class, 'account_perpouse_master_id');
    }
}
