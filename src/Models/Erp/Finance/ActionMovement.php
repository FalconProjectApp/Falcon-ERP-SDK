<?php

namespace FalconERP\Skeleton\Models\Erp\Finance;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Scope;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

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

    #[Scope]
    public function byActionID(Builder $query, int $actionId, string $operation = '='): Builder
    {
        return $query->where('action_id', $operation, $actionId);
    }

    #[Scope]
    public function byTypes(Builder $query, string $types = 'compra', string $operation = '='): Builder
    {
        return $query->where('types', $operation, $types);
    }

    #[Scope]
    public function byPortfolioId(Builder $query, int $id): Builder
    {
        return $query->whereHas('action', function ($action) use ($id) {
            return $action->where('portfolio_id', $id);
        });
    }
}
