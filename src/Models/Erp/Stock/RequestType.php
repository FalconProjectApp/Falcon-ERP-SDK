<?php

namespace FalconERP\Skeleton\Models\Erp\Stock;

use FalconERP\Skeleton\Enums\RequestEnum;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestType extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;

    protected $fillable = [
        'description',
        'request_type',
        'type',
        'active',
    ];

    protected $attributes = [
        'active' => true,
        'type'   => RequestEnum::TYPE_CLIENT,
    ];
}
