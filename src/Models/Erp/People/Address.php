<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\People;

use FalconERP\Skeleton\Falcon;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
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

    /**
     * TODO: esta quebrando esta função, e deveria ser um evento talvez. ou um observed.
     */
    /*     protected function cityIbge(): Attribute
        {
            return Attribute::make(
                get: function ($value) {

                    if (blank($value) && !blank($this->city)) {
                        dd($this->city);
                        $value = Falcon::bigDataService('city')
                            ->get($this->city)
                            ->data
                            ->where('slug', $this->city_slug)
                            ->first()
                            ?->ibge;
                        dd($value);
                        $this->city_ibge = $value;

                        if ($this->isDirty('city_ibge')) {
                            $this->save();
                        }
                    }

                    return $value;
                },
            );
        } */

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

    /**
     * end_nac.
     *
     * CEP - Código de Endereçamento Postal do tomador do serviço.
     * cMun - Código do município do tomador do serviço (IBGE).
     */
    protected function endNac(): Attribute
    {
        return new Attribute(
            get: fn (): object => (object) [
                'CEP'  => $this->cep,
                'cMun' => $this->city_ibge,
            ],
        );
    }

    /**
     * end_ext.
     *
     * cPais - Código do país do endereço do tomaador do tomaador do serviço. (Tabela de Países ISO)
     * cEndPost - Código alfanumérico do Endereçamento Postal no exterior do tomaador do serviço.
     * xCidade - Nome da cidade no exterior do tomaador do serviço.
     * xEstProvReg - Estado, província ou região da cidade no exterior do tomaador do serviço.
     */
    protected function endExt(): Attribute
    {
        return new Attribute(
            get: fn (): object => (object) [
                'cPais'       => '',
                'cEndPost'    => '',
                'xCidade'     => '',
                'xEstProvReg' => '',
            ],
        );
    }
}
