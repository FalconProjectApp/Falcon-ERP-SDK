<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\Stock;

use FalconERP\Skeleton\Database\Factories\RequestTypeFactory;
use FalconERP\Skeleton\Enums\RequestEnum;
use FalconERP\Skeleton\Models\Erp\Fiscal\NatureOperation;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

class RequestType extends BaseModel
{
    use HasFactory;
    use SetSchemaTrait;
    use SoftDeletes;

    protected $fillable = [
        'description',
        'request_type',
        'type',
        'is_active',
    ];

    protected $attributes = [
        'is_active' => true,
        'type'      => RequestEnum::TYPE_CLIENT,
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
