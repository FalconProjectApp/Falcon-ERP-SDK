<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ValidateTrait\Data;

class Setting extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'value',
        'description',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you may specify the relationships that the model should have with
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    | Here you may specify the scopes that the model should have with
    |
    */

    public function scopeByName($query, $name): Data
    {
        $setting = $query->where('name', $name)->first();

        return data($setting ? json_decode($setting?->value) : []);
    }

    /*
    |--------------------------------------------------------------------------
    | Others
    |--------------------------------------------------------------------------
    |
    | Here you may specify the others that the model should have with
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    |
    | Here you may specify the attributes that should be cast to native types.
    |
    */
}
