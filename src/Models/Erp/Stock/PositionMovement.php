<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\Stock;

use App\Enums\MovementTypeEnum;
use FalconERP\Skeleton\Models\Erp\Stock\Stock;
use FalconERP\Skeleton\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\ActionTrait;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

class PositionMovement extends BaseModel implements AuditableContract
{
    use ActionTrait;
    use Auditable;
    use HasFactory;
    use SetSchemaTrait;

    protected $fillable = [
        'stock_id',
        'from_position_id',
        'to_position_id',
        'type',
        'quantity',
        'volume',
        'weight',
        'batch',
        'reason',
        'notes',
        'user_id',
        'moved_at',
    ];

    protected $casts = [
        'type'       => MovementTypeEnum::class,
        'quantity'   => 'decimal:4',
        'volume'     => 'decimal:2',
        'weight'     => 'decimal:2',
        'moved_at'   => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relação: Uma movimentação pertence a um estoque.
     */
    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    /**
     * Relação: Uma movimentação tem uma posição de origem.
     */
    public function fromPosition(): BelongsTo
    {
        return $this->belongsTo(WarehousePosition::class, 'from_position_id');
    }

    /**
     * Relação: Uma movimentação tem uma posição de destino.
     */
    public function toPosition(): BelongsTo
    {
        return $this->belongsTo(WarehousePosition::class, 'to_position_id');
    }

    /**
     * Relação: Uma movimentação pertence a um usuário.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verifica se é uma transferência entre posições.
     */
    public function isTransfer(): bool
    {
        return MovementTypeEnum::TRANSFER === $this->type
            && $this->from_position_id
            && $this->to_position_id;
    }

    /**
     * Verifica se é uma entrada.
     */
    public function isEntry(): bool
    {
        return MovementTypeEnum::IN === $this->type;
    }

    /**
     * Verifica se é uma saída.
     */
    public function isExit(): bool
    {
        return MovementTypeEnum::OUT === $this->type;
    }
}
