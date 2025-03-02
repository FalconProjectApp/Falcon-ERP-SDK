<?php

namespace FalconERP\Skeleton\Models\Erp\People;

use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\SetSchemaTrait\SetSchemaTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class PeopleContact extends BaseModel
{
    use HasFactory;
    use Notifiable;
    use SetSchemaTrait;

    protected $fillable = [
        'type',
        'value',
        'main',
    ];

    public $allowedIncludes = [];

    public function people()
    {
        return $this->belongsTo(People::class);
    }
}
