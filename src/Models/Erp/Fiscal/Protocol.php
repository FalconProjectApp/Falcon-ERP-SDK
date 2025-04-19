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
class Protocol extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;

    protected $fillable = [
        'batch_id',
        'type_environment',
        'code_status',
        'receipt_date',
        'motive_status',
        'number_protocol',
        'xml',
    ];
    protected $casts = [
        'batch_id'         => 'integer',
        'type_environment' => 'string',
        'code_status'      => 'string',
        'receipt_date'     => 'datetime',
        'motive_status'    => 'string',
        'number_protocol'  => 'string',
        'xml'              => 'string',
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
    public function scopeByTypeEnvironment($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'type_environment'), fn ($query, $params) => $query->whereIn('type_environment', $params));
    }
    public function scopeByCodeStatus($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'code_status'), fn ($query, $params) => $query->whereIn('code_status', $params));
    }
    public function scopeByReceiptDate($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'receipt_date'), fn ($query, $params) => $query->whereIn('receipt_date', $params));
    }
    public function scopeByMotiveStatus($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'motive_status'), fn ($query, $params) => $query->whereIn('motive_status', $params));
    }
    public function scopeByNumberProtocol($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'number_protocol'), fn ($query, $params) => $query->whereIn('number_protocol', $params));
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
