<?php

namespace FalconERP\Skeleton\Models\Erp\People;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

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

    protected $attributes = [
        'main' => false,
    ];

    protected $casts = [
        'cep'        => 'string',
        'country'    => 'string',
        'cities_id'  => 'integer',
        'district'   => 'string',
        'road'       => 'string',
        'number'     => 'string',
        'complement' => 'string',
        'main'       => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    |
    | Here you may specify the attributes that should be cast to native types.
    |
    */

    /**
     * neighborhood.
     */
    protected function neighborhood(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->district,
        );
    }

    /**
     * zip_code.
     */
    protected function zipCode(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->cep,
        );
    }

    /**
     * street.
     */
    protected function street(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->road,
        );
    }

    /**
     * city_code.
     */
    protected function cityCode(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->road,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    | Here you may specify the scopes that the model should have with
    |
    */
}
