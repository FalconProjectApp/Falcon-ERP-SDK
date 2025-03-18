<?php

namespace FalconERP\Skeleton\Models\Erp\People;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\ActionTrait;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

class Type extends BaseModel implements AuditableContract
{
    use HasFactory;
    use SetSchemaTrait;
    use Auditable;
    use SoftDeletes;
    use ActionTrait;

    protected $fillable = [
        'description',
    ];

    protected function setActions(): array
    {
        return [
            'can_view'    => true,
            'can_restore' => $this->trashed(),
            'can_update'  => !$this->trashed(),
            'can_delete'  => !$this->trashed(),
        ];
    }
}
