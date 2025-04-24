<?php

namespace FalconERP\Skeleton\Models\Erp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use QuantumTecnology\ModelBasicsExtension\BaseModel;

class Setting extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'value',
        'description',
    ];
}
