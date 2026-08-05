<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DepositBilyet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'bilyet_number',
        'kode_bilyet',
        'sequence',
        'status',
        'branch_id',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'AVAILABLE');
    }
}
