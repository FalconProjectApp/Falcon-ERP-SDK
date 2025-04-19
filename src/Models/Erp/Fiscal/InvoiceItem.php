<?php

namespace FalconERP\Skeleton\Models\Erp\Fiscal;

use Illuminate\Database\Eloquent\SoftDeletes;
use FalconERP\Skeleton\Models\Erp\Stock\Stock;
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
class InvoiceItem extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;

    protected $fillable = [
        'invoice_id',
        'stock_id',
        'quantity',
        'unit_value',
        'total_value',
    ];
    protected $casts = [
        'invoice_id'  => 'integer',
        'stock_id'    => 'integer',
        'quantity'    => 'float',
        'unit_value'  => 'float',
        'total_value' => 'float',
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

    public function stock()
    {
        return $this->belongsTo(Stock::class);
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

    public function scopeByInvoiceId($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'invoice_id'), fn ($query, $params) => $query->whereIn('invoice_id', $params));
    }

    public function scopeByStockId($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'stock_id'), fn ($query, $params) => $query->whereIn('stock_id', $params));
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
