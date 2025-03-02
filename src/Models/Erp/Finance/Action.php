<?php

namespace FalconERP\Skeleton\Models\Erp\Finance;

use OwenIt\Auditing\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use QuantumTecnology\SetSchemaTrait\SetSchemaTrait;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use QuantumTecnology\ModelBasicsExtension\HasManySyncable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Action extends BaseModel implements AuditableContract
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;
    use Auditable;

    protected $fillable = [
        'description',
        'portfolio_id',
    ];

    protected $appends = [
        'amount_total',
        'value_total',
        'value_total_earnings',
        'value_total_buy',
        'value_total_sell',
        'average_price',
    ];

    public $allowedIncludes = [
        'actions_movements',
        'actions_dividends',
        'portfolio',
    ];

    /**
     * Get the amountTotal.
     */
    protected function amountTotal(): Attribute
    {
        $total = 0;
        foreach ($this->ActionsMovements as $movement) {
            $total += $movement->amount;
        }

        return new Attribute(
            get: fn () => $total,
        );
    }

    /**
     * Get the valueTotal.
     */
    protected function valueTotal(): Attribute
    {
        $total = 0;
        foreach ($this->ActionsMovements as $movement) {
            if ('compra' == $movement->types) {
                $total += $movement->amount * $movement->value;
            }
            if ('venda' == $movement->types) {
                $total -= $movement->amount * $movement->value;
            }
        }

        return new Attribute(
            get: fn () => $total,
        );
    }

    /**
     * Get the valueTotalSell.
     */
    protected function valueTotalSell(): Attribute
    {
        $total = 0;
        foreach ($this->ActionsMovements as $movement) {
            if ('venda' == $movement->types) {
                $total -= $movement->amount * $movement->value;
            }
        }

        return new Attribute(
            get: fn () => $total,
        );
    }

    /**
     * Get the valueTotalBuy.
     */
    protected function valueTotalBuy(): Attribute
    {
        $total = 0;
        foreach ($this->ActionsMovements as $movement) {
            if ('compra' == $movement->types) {
                $total += $movement->amount * $movement->value;
            }
        }

        return new Attribute(
            get: fn () => $total,
        );
    }

    /**
     * Get the valueTotalEarnings.
     */
    protected function valueTotalEarnings(): Attribute
    {
        $total = 0;
        foreach ($this->ActionsDividends as $dividends) {
            $total += $dividends->amount * $dividends->value;
        }

        return new Attribute(
            get: fn () => $total,
        );
    }

    /**
     * Get the averagePrice.
     */
    protected function averagePrice(): Attribute
    {
        $total = 0;

        if ($this->value_total && 0 != $this->value_total) {
            $total = $this->value_total / $this->amount_total;
        }

        return new Attribute(
            get: fn () => $total,
        );
    }

    /**
     * ActionsMovements function.
     */
    public function ActionsMovements(): HasManySyncable
    {
        return $this->hasMany(ActionMovement::class);
    }

    /**
     * ActionsDividends function.
     */
    public function ActionsDividends(): HasManySyncable
    {
        return $this->hasMany(ActionDividend::class);
    }

    /**
     * ActionsDividends function.
     *
     * @return HasManySyncable
     */
    public function Portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }

    /**
     * byDescription function.
     */
    public function scopeByDescription(Builder $query, string $description, string $operation = '='): Builder
    {
        return $query->where('description', $operation, $description);
    }

    /**
     * byPortfolioID function.
     */
    public function scopeByPortfolioID(Builder $query, int $portfolioId, string $operation = '='): Builder
    {
        return $query->where('portfolio_id', $operation, $portfolioId);
    }
}
