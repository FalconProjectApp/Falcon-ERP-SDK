<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\People;

use Illuminate\Support\Str;
use FalconERP\Skeleton\Falcon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Attributes\Scope;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

class Address extends BaseModel
{
    use HasFactory;
    use SetSchemaTrait;
    use SoftDeletes;

    protected $fillable = [
        'people_id',
        'country',
        'district',
        'road',
        'number',
        'complement',
        'main',
        'cep',
        'city',
        'state',
        'city_ibge',
        'state_ibge',

    ];

    protected $attributes = [
        'main' => false,
    ];

    protected $casts = [
        'cep'        => 'string',
        'country'    => 'string',
        'district'   => 'string',
        'road'       => 'string',
        'number'     => 'string',
        'complement' => 'string',
        'main'       => 'boolean',
        'city'       => 'string',
        'state'      => 'string',
        'city_ibge'  => 'string',
        'state_ibge' => 'string',
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
     * city_slug.
     */
    protected function citySlug(): Attribute
    {
        return Attribute::make(
            get: fn () => Str::slug($this->city),
        );
    }

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

    protected function cityIbge(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (blank($value) && !blank($this->city)) {
                    $value = Falcon::bigDataService('city')
                        ->get($this->city)
                        ->data
                        ->where('slug', $this->city_slug)
                        ->first()
                        ?->ibge;

                    $this->city_ibge = $value;

                    if ($this->isDirty('city_ibge')) {
                        $this->save();
                    }
                }

                return $value;
            },
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

    #[Scope]
    public function byPeopleIds(Builder $query, string | array $params = []): Builder
    {
        return $query
            ->when($this->filtered($params, 'people_ids'), function ($query, $params) {
                $query->whereIn('people_id', $params);
            });
    }
}
