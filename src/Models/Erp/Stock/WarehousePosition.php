<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\Stock;

use App\Enums\PositionStatusEnum;
use App\Policies\WarehousePositionPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\ActionTrait;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

#[UsePolicy(WarehousePositionPolicy::class)]
class WarehousePosition extends BaseModel implements AuditableContract
{
    use ActionTrait;
    use Auditable;
    use HasFactory;
    use SetSchemaTrait;
    use SoftDeletes;

    protected $fillable = [
        'warehouse_aisle_id',
        'code',
        'name',
        'level',
        'column',
        'depth',
        'max_volume',
        'max_weight',
        'current_volume',
        'current_weight',
        'status',
        'notes',
    ];

    protected $casts = [
        'level'          => 'integer',
        'column'         => 'integer',
        'depth'          => 'integer',
        'max_volume'     => 'decimal:2',
        'max_weight'     => 'decimal:2',
        'current_volume' => 'decimal:2',
        'current_weight' => 'decimal:2',
        'status'         => PositionStatusEnum::class,
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
        'deleted_at'     => 'datetime',
    ];

    /**
     * Relação: Uma posição pertence a uma rua.
     */
    public function aisle(): BelongsTo
    {
        return $this->belongsTo(WarehouseAisle::class, 'warehouse_aisle_id');
    }

    /**
     * Relação: Uma posição tem muitas alocações de estoque.
     */
    public function stockPositions(): HasMany
    {
        return $this->hasMany(StockPosition::class);
    }

    /**
     * Relação: Uma posição tem muitas movimentações de entrada.
     */
    public function movementsTo(): HasMany
    {
        return $this->hasMany(PositionMovement::class, 'to_position_id');
    }

    /**
     * Relação: Uma posição tem muitas movimentações de saída.
     */
    public function movementsFrom(): HasMany
    {
        return $this->hasMany(PositionMovement::class, 'from_position_id');
    }

    /**
     * Verifica se a posição está disponível.
     */
    public function isAvailable(): bool
    {
        return PositionStatusEnum::AVAILABLE === $this->status;
    }

    /**
     * Verifica se há volume disponível.
     */
    public function hasVolumeAvailable(float $requiredVolume): bool
    {
        $availableVolume = $this->max_volume - $this->current_volume;

        return $availableVolume >= $requiredVolume;
    }

    /**
     * Verifica se há peso disponível.
     */
    public function hasWeightAvailable(float $requiredWeight): bool
    {
        $availableWeight = $this->max_weight - $this->current_weight;

        return $availableWeight >= $requiredWeight;
    }

    /**
     * Calcula percentual de ocupação por volume.
     */
    public function getVolumeUsagePercentage(): float
    {
        if (0 == $this->max_volume) {
            return 0;
        }

        return ($this->current_volume / $this->max_volume) * 100;
    }

    /**
     * Calcula percentual de ocupação por peso.
     */
    public function getWeightUsagePercentage(): float
    {
        if (0 == $this->max_weight) {
            return 0;
        }

        return ($this->current_weight / $this->max_weight) * 100;
    }
}
