<?php

namespace FalconERP\Skeleton\Models\Erp\Stock;

use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\SetSchemaTrait\SetSchemaTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class VolumeType extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;

    protected $fillable = [
        'description',
        'initials',
    ];
}
