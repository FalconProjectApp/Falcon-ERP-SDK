<?php

namespace FalconERP\Skeleton\Models\Erp\Finance;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Scope;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

class ActionDividend extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;

    protected $fillable = [
        'description',
        'dividend_event_id',
        'payment_forecast',
        'approval_date',
        'amount',
        'value',
    ];

    /**
     * ActionsDividends function.
     */
    public function action(): BelongsTo
    {
        return $this->belongsTo(Action::class);
    }

    #[Scope]
    public function byPortfolioId(Builder $query, int $id): Builder
    {
        return $query->whereHas('action', function ($action) use ($id) {
            return $action->where('portfolio_id', $id);
        });
    }
}
