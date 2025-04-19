<?php

namespace FalconERP\Skeleton\Models\Erp\Revenue;

use App\Models\Erp\Finance\PaymentMethod;
use FalconERP\Skeleton\Observers\CacheObserver;
use FalconERP\Skeleton\Observers\NotificationObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

#[ObservedBy([
    CacheObserver::class,
    NotificationObserver::class,
])]
class InvoicePayment extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
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

    public function scopeByInvoiceId($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'invoice_id'), fn ($query, $params) => $query->whereIn('invoice_id', $params));
    }

    public function scopeByPaymentMethodId($query, array $params = [])
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
}
