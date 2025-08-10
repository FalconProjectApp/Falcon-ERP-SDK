<?php

namespace FalconERP\Skeleton\Models\Erp\Fiscal;

use Illuminate\Database\Eloquent\SoftDeletes;
use FalconERP\Skeleton\Observers\CacheObserver;
use Illuminate\Database\Eloquent\Attributes\Scope;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use FalconERP\Skeleton\Observers\NotificationObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use FalconERP\Skeleton\Enums\Fiscal\NatureOperationTypeEnum;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

#[ObservedBy([
    CacheObserver::class,
    NotificationObserver::class,
])]
class NatureOperation extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;

    protected $fillable = [
        'description',
        'people_issuer_id',
    ];
    protected $casts = [
        'description'    => 'string',
        'serie_id'       => 'integer',
        'operation_type' => NatureOperationTypeEnum::class,
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you may specify the relationships that the model should have with
    |
    */

    public function serie(): BelongsTo
    {
        return $this->belongsTo(Serie::class);
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
    public function bySerieIds($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'serie_ids'), fn ($query, $params) => $query->whereIn('serie_id', $params));
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
