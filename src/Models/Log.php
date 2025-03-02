<?php

namespace FalconERP\Skeleton\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use QuantumTecnology\ModelBasicsExtension\BaseModel;

class Log extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';

    public $timestamps = false;
}
