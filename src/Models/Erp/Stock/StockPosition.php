<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\Stock;

use App\Policies\StockPositionPolicy;
use FalconERP\Skeleton\Models\Erp\Stock\Stock;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\ActionTrait;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

#[UsePolicy(StockPositionPolicy::class)]
class StockPosition extends BaseModel implements AuditableContract
{
    use ActionTrait;
    use Auditable;
    use HasFactory;
    use SetSchemaTrait;
    use SoftDeletes;

    protected $fillable = [
        'stock_id',
        'warehouse_position_id',
        'quantity',
        'volume',
        'weight',
        'batch',
        'expiration_date',
        'notes',
    ];

    protected $casts = [
        'quantity'        => 'decimal:4',
        'volume'          => 'decimal:2',
        'weight'          => 'decimal:2',
        'expiration_date' => 'date',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
        'deleted_at'      => 'datetime',
    ];

    /**
     * Relação: Uma alocação pertence a um estoque.
     */
    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    /**
     * Relação: Uma alocação pertence a uma posição.
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(WarehousePosition::class, 'warehouse_position_id');
    }

    /**
     * Verifica se o produto está próximo do vencimento.
     */
    public function isNearExpiration(int $days = 30): bool
    {
        if (!$this->expiration_date) {
            return false;
        }

        return $this->expiration_date->diffInDays(now()) <= $days;
    }

    /**
     * Verifica se o produto está vencido.
     */
    public function isExpired(): bool
    {
        if (!$this->expiration_date) {
            return false;
        }

        return $this->expiration_date->isPast();
    }
}
