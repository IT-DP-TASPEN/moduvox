<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountPerpouseDetail extends Model
{
    protected $guarded = [];

    public function accountPerpouseMaster()
    {
        return $this->belongsTo(AccountPerpouseMaster::class, 'account_perpouse_master_id');
    }
}
