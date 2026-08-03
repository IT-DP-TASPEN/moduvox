<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DivisionApprover extends Model
{
    protected $fillable = ['division_name', 'approver_id', 'director_id'];

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function director()
    {
        return $this->belongsTo(User::class, 'director_id');
    }
}
