<?php

namespace FalconERP\Skeleton\Models\Erp\Fiscal;

use FalconERP\Skeleton\Observers\CacheObserver;
use FalconERP\Skeleton\Observers\NotificationObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
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
        'description' => 'string',
        'serie_id'    => 'integer',
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

    public function scopeBySerieIds($query, array $params = [])
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
