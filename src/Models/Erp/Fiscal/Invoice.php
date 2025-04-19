<?php

namespace FalconERP\Skeleton\Models\Erp\Revenue;

use Illuminate\Database\Eloquent\SoftDeletes;
use FalconERP\Skeleton\Observers\CacheObserver;
use FalconERP\Skeleton\Models\Erp\People\People;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use FalconERP\Skeleton\Observers\NotificationObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

#[ObservedBy([
    CacheObserver::class,
    NotificationObserver::class,
])]
class Invoice extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;

    protected $fillable = [
        'batch_id',
        'nature_operation_id',
        'people_issuer_id',
        'people_recipient_id',
        'type_environment',
    ];
    protected $casts = [
        'batch_id'            => 'integer',
        'nature_operation_id' => 'integer',
        'people_issuer_id'    => 'integer',
        'people_recipient_id' => 'integer',
        'type_environment'    => 'string',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you may specify the relationships that the model should have with
    |
    */

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function natureOperation()
    {
        return $this->belongsTo(NatureOperation::class);
    }

    public function peopleIssuer()
    {
        return $this->belongsTo(People::class, 'people_issuer_id');
    }

    public function peopleRecipient()
    {
        return $this->belongsTo(People::class, 'people_recipient_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    | Here you may specify the scopes that the model should have with
    |
    */

    public function scopeByBatchId($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'batch_id'), fn ($query, $params) => $query->whereIn('batch_id', $params));
    }
    public function scopeByNatureOperationId($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'nature_operation_id'), fn ($query, $params) => $query->whereIn('nature_operation_id', $params));
    }
    public function scopeByPeopleIssuerId($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'people_issuer_id'), fn ($query, $params) => $query->whereIn('people_issuer_id', $params));
    }
    public function scopeByPeopleRecipientId($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'people_recipient_id'), fn ($query, $params) => $query->whereIn('people_recipient_id', $params));
    }
    public function scopeByTypeEnvironment($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'type_environment'), fn ($query, $params) => $query->whereIn('type_environment', $params));
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
