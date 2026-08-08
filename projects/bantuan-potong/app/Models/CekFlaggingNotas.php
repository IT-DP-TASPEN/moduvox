<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CekFlaggingNotas extends Model
{
    protected $connection = 'mysql';
    protected $table = 'notas_ownerships';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    public function mitra()
    {
        return $this->belongsTo(MitraMaster::class, 'mitra_master_id');
    }
}
