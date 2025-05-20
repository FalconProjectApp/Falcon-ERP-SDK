<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\Shop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

class ShopSegment extends BaseModel
{
    use HasFactory;
    use SetSchemaTrait;

    protected $fillable = [
        'shop_id',
        'name',
        'value',
        'printer_name',
        'printer_ip',
        'printer_port',
        'printer_model',
        'main_color',
        'whatsapp_number',
        'instagram',
        'hasAutomaticallyFinish',
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
                if (in_array($key, ['id', 'shop_id'])) {
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
