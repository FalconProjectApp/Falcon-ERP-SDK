<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Models\Erp\Stock;

use FalconERP\Skeleton\Database\Factories\Stock\StockSegmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

class StockSegment extends BaseModel
{
    use HasFactory;
    use SetSchemaTrait;

    protected $fillable = [
        'stock_id',
        'name',
        'value',
        'dun',
    ];

    protected static function newFactory()
    {
        return StockSegmentFactory::new();
    }

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
                if (in_array($key, ['id', 'stock_id'])) {
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
