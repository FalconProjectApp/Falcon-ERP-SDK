<?php

namespace FalconERP\Skeleton\Models\Erp\Finance;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use QuantumTecnology\SetSchemaTrait\SetSchemaTrait;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActionMovement extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;

    protected $fillable = [
        'description',
        'types',
        'amount',
        'value',
        'payday',
    ];

    /**
     * ActionsDividends function.
     */
    public function action(): BelongsTo
    {
        return $this->belongsTo(Action::class);
    }

    public function scopeByActionID(Builder $query, int $actionId, string $operation = '='): Builder
    {
        return $query->where('action_id', $operation, $actionId);
    }

    public function scopeByTypes(Builder $query, string $types = 'compra', string $operation = '='): Builder
    {
        return $query->where('types', $operation, $types);
    }

    public function scopeByPortfolioId(Builder $query, int $id): Builder
    {
        return $query->whereHas('action', function ($action) use ($id) {
            return $action->where('portfolio_id', $id);
        });
    }
}
