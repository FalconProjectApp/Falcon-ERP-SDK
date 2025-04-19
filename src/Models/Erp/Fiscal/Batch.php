<?php

namespace FalconERP\Skeleton\Models\Erp\Revenue;

use Illuminate\Database\Eloquent\SoftDeletes;
use FalconERP\Skeleton\Observers\CacheObserver;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use FalconERP\Skeleton\Observers\NotificationObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

#[ObservedBy([
    CacheObserver::class,
    NotificationObserver::class,
])]
class Batch extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;

    protected $fillable = [
        'version_application',
        'type_environment',
        'code_status',
        'motive_status',
        'unit_federation_code',
        'information_receipt_number',
        'information_receipt_date',
        'average_processing_time',
    ];
    protected $casts = [
        'version_application'        => 'string',
        'type_environment'           => 'string',
        'code_status'                => 'string',
        'motive_status'              => 'string',
        'unit_federation_code'       => 'string',
        'information_receipt_number' => 'string',
        'information_receipt_date'   => 'datetime',
        'average_processing_time'    => 'string',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you may specify the relationships that the model should have with
    |
    */

    public function protocols()
    {
        return $this->hasMany(Protocol::class);
    }
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    | Here you may specify the scopes that the model should have with
    |
    */

    public function scopeBySerieId($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'serie_id'), fn ($query, $params) => $query->whereIn('serie_id', $params));
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
