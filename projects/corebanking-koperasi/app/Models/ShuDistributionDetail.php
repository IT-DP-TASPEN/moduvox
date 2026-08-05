<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShuDistributionDetail extends Model
{
    protected $fillable = [
        'shu_distribution_id', 'kriteria', 'persentase', 'total_shu', 'jumlah_orang', 'nominal_per_orang'
    ];

    public function distribution()
    {
        return $this->belongsTo(ShuDistribution::class, 'shu_distribution_id');
    }
}
