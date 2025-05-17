<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\Stock;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

class ProductSegment extends BaseModel
{
    use HasFactory;
    use SetSchemaTrait;

    protected $fillable = [
        'people_id',
        'name',
        'value',
        'ncm',
        'unit_abbreviation',
        'unit_description',
    ];

    /*
    |--------------------------------------------------------------------------
    | Others
    |--------------------------------------------------------------------------
    |
    | Here you may specify the others that the model should have with
    |
    */

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {

            foreach ($model->getAttributes() as $key => $value) {
                if (in_array($key, ['id', 'product_id'])) {
                    continue;
                }

                unset($model->attributes[$key]);
                $model->setAttribute('name', $key);
                $model->setAttribute('value', $value);
            }

        });
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    |
    | Here you may specify the attributes that should be cast to native types.
    |
    */

}
