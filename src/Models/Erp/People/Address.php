<?php

namespace FalconERP\Skeleton\Models\Erp\People;

use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;

    protected $fillable = [
        'cep',
        'country',
        'cities_id',
        'district',
        'road',
        'number',
        'complement',
        'main',
        'people_id',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'main' => false,
    ];
}
