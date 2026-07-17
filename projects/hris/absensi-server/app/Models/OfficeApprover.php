<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficeApprover extends Model
{
    protected $fillable = ['office_id', 'division_id', 'approver_id', 'director_id'];

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function director()
    {
        return $this->belongsTo(User::class, 'director_id');
    }
}
