<?php

namespace FalconERP\Skeleton\Models\Erp\People;

use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Type extends BaseModel implements AuditableContract
{
    use HasFactory;
    use SetSchemaTrait;
    use Auditable;
    use SoftDeletes;

    protected $fillable = [
        'description',
    ];
}
