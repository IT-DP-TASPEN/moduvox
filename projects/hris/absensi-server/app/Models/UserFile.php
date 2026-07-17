<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFile extends Model
{
    protected $fillable = ['user_id', 'file_type', 'name', 'file_path'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
