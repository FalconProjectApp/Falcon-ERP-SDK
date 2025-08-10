<?php

namespace FalconERP\Skeleton\Models\Erp\Fiscal;

use Illuminate\Database\Eloquent\SoftDeletes;
use FalconERP\Skeleton\Observers\CacheObserver;
use FalconERP\Skeleton\Models\Erp\People\People;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Attributes\Scope;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use FalconERP\Skeleton\Observers\NotificationObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use FalconERP\Skeleton\Enums\Fiscal\SerieEnvironmentEnum;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

#[ObservedBy([
    CacheObserver::class,
    NotificationObserver::class,
])]
class Serie extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;

    protected $fillable = [
        'description',
        'model',
        'sequence_number',
        'environment',
        'people_issuer_id',
    ];
    protected $casts = [
        'sequence_number' => 'integer',
        'environment'     => 'string',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you may specify the relationships that the model should have with
    |
    */

    public function people()
    {
        return $this->belongsTo(People::class, 'people_issuer_id');
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
    public function byPeopleIssuerIds($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'people_issuer_ids'), fn ($query, $params) => $query->whereIn('people_issuer_id', $params));
    }

    #[Scope]
    public function byModels($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'models'), fn ($query, $params) => $query->whereIn('model', $params));
    }

    #[Scope]
    public function byEnvironments($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'environments'), fn ($query, $params) => $query->whereIn('environment', $params));
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

    protected function environmentCode(): Attribute
    {
        return Attribute::make(
            get: fn () => collect(SerieEnvironmentEnum::cases())->firstWhere('value', $this->environment)->tpAmb(),
        );
    }
}
