<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\Fiscal;

use FalconERP\Skeleton\Observers\NotificationObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Observers\CacheObserver;
use QuantumTecnology\ModelBasicsExtension\Traits\ActionTrait;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

#[ObservedBy([
    CacheObserver::class,
    NotificationObserver::class,
])]
class Protocol extends BaseModel
{
    use ActionTrait;
    use HasFactory;
    use SetSchemaTrait;
    use SoftDeletes;

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

    #[Scope]
    public function byBatchId($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'batch_id'), fn ($query, $params) => $query->whereIn('batch_id', $params));
    }

    #[Scope]
    public function byTypeEnvironment($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'type_environment'), fn ($query, $params) => $query->whereIn('type_environment', $params));
    }

    #[Scope]
    public function byCodeStatus($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'code_status'), fn ($query, $params) => $query->whereIn('code_status', $params));
    }

    #[Scope]
    public function byReceiptDate($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'receipt_date'), fn ($query, $params) => $query->whereIn('receipt_date', $params));
    }

    #[Scope]
    public function byMotiveStatus($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'motive_status'), fn ($query, $params) => $query->whereIn('motive_status', $params));
    }

    #[Scope]
    public function byNumberProtocol($query, array $params = [])
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

        /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    |
    | Here you may specify the actions that the model should have with
    |
    */

    protected function setActions(): array
    {
        return [
            'can_view'    => $this->canView(),
            'can_restore' => $this->canRestore(),
            'can_update'  => $this->canUpdate(),
            'can_delete'  => $this->canDelete(),
        ];
    }

    private function canView(): bool
    {
        return true;
    }

    private function canRestore(): bool
    {
        return $this->trashed() && false;
    }

    private function canUpdate(): bool
    {
        return !$this->trashed() && false;
    }

    private function canDelete(): bool
    {
        return !$this->trashed() && false;
    }
}
