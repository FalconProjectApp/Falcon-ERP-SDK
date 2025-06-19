<?php

namespace FalconERP\Skeleton\Models\Erp\Stock;

use FalconERP\Skeleton\Enums\RequestEnum;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use FalconERP\Skeleton\Models\Erp\Fiscal\NatureOperation;
use FalconERP\Skeleton\Database\Factories\RequestTypeFactory;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

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

    protected static function newFactory()
    {
        return RequestTypeFactory::new();
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    |
    | Here you may specify the attributes that should be cast to native types.
    |
    */

    protected function natureOperationDefault(): Attribute
    {
        return Attribute::make(
            get: fn () => NatureOperation::find(1),
        );
    }
}
