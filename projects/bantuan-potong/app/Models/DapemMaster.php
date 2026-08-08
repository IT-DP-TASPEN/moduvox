<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DapemMaster extends Model
{
    protected $connection = 'mysql_prod';
    protected $table = 'payroll_dapem_masters';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
}