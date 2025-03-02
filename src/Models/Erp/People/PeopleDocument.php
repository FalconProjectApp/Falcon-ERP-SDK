<?php

namespace FalconERP\Skeleton\Models\Erp\People;

use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\SetSchemaTrait\SetSchemaTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class PeopleDocument extends BaseModel
{
    use HasFactory;
    use Notifiable;
    use SetSchemaTrait;

    protected $fillable = [
        'type',
        'value',
        'is_accessible',
    ];

    public $allowedIncludes = [];
}
