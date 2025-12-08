<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\Fiscal;

use FalconERP\Skeleton\Models\Erp\People\People;
use FalconERP\Skeleton\Models\Erp\Stock\Stock;
use FalconERP\Skeleton\Observers\NotificationObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Observers\CacheObserver as ObserversCacheObserver;
use QuantumTecnology\ModelBasicsExtension\Traits\ActionTrait;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

#[ObservedBy([
    ObserversCacheObserver::class,
    NotificationObserver::class,
])]
class InvoiceItem extends BaseModel
{
    use ActionTrait;
    use HasFactory;
    use SetSchemaTrait;
    use SoftDeletes;

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

    #[Scope]
    public function byInvoiceId($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'invoice_id'), fn ($query, $params) => $query->whereIn('invoice_id', $params));
    }

    public function byStockId($query, array $params = [])
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
