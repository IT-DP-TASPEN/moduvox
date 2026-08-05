<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterShu extends Model
{
    protected $fillable = ['cif_id', 'kriteria', 'saving_account_id'];

    public function cif()
    {
        return $this->belongsTo(Cif::class);
    }

    public function savingAccount()
    {
        return $this->belongsTo(SavingAccount::class, 'saving_account_id');
    }
}
