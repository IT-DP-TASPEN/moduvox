<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CekNotasAll extends Model
{
    protected $table = 'v_cek_notas_all';
    protected $primaryKey = 'uid';
    public $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false; // view punya kolom created_at/updated_at tapi tidak di-manage Eloquent

    protected $guarded = [];

    // Relasi ke mitra
    public function mitraMaster()
    {
        return $this->belongsTo(MitraMaster::class, 'mitra_master_id');
    }

    // Relasi ke user pembuat
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
