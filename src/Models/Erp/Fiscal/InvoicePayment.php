<?php

namespace FalconERP\Skeleton\Models\Erp\Fiscal;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Scope;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use FalconERP\Skeleton\Observers\NotificationObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use FalconERP\Skeleton\Models\Erp\Finance\PaymentMethod;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;
use QuantumTecnology\ModelBasicsExtension\Observers\CacheObserver;
use QuantumTecnology\ModelBasicsExtension\Traits\ActionTrait;

#[ObservedBy([
    CacheObserver::class,
    NotificationObserver::class,
])]
class InvoicePayment extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use ActionTrait;
    use SetSchemaTrait;

    protected $fillable = [
        'invoice_id',
        'payment_method_id',
        'value',
    ];
    protected $casts = [
        'invoice_id'        => 'integer',
        'payment_method_id' => 'integer',
        'value'             => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you may specify the relationships that the model should have with
    |
    */

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
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
    public function byInvoiceId($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'invoice_id'), fn ($query, $params) => $query->whereIn('invoice_id', $params));
    }

    #[Scope]
    public function byPaymentMethodId($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'payment_method_id'), fn ($query, $params) => $query->whereIn('payment_method_id', $params));
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
