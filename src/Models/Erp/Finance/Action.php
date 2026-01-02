<?php

namespace FalconERP\Skeleton\Models\Erp\Finance;

use OwenIt\Auditing\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Attributes\Scope;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use QuantumTecnology\ModelBasicsExtension\HasManySyncable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use QuantumTecnology\ModelBasicsExtension\Traits\ActionTrait;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

class Action extends BaseModel implements AuditableContract
{
    use ActionTrait;
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
        $this->loadMissing('ActionsMovements');

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
        $this->loadMissing('ActionsMovements');

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
        $this->loadMissing('ActionsMovements');

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
        $this->loadMissing('ActionsMovements');

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
        $this->loadMissing('ActionsDividends');

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
    #[Scope]
    public function byDescription(Builder $query, string $description, string $operation = '='): Builder
    {
        return $query->where('description', $operation, $description);
    }

    /**
     * byPortfolioID function.
     */
    #[Scope]
    public function byPortfolioID(Builder $query, int $portfolioId, string $operation = '='): Builder
    {
        return $query->where('portfolio_id', $operation, $portfolioId);
    }

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
            'can_view'     => $this->canView(),
            'can_restore'  => $this->canRestore(),
            'can_update'   => $this->canUpdate(),
            'can_delete'   => $this->canDelete(),
            'can_follow'   => $this->canFollow(),
            'can_unfollow' => $this->canUnfollow(),
        ];
    }

    private function canView(): bool
    {
        return true;
    }

    private function canRestore(): bool
    {
        return $this->trashed();
    }

    private function canUpdate(): bool
    {
        return !$this->trashed();
    }

    private function canDelete(): bool
    {
        return !$this->trashed();
    }

    private function canFollow(): bool
    {
        return true;

        /* return (!$this->trashed()
            && !$this->is_public
            && !$this->followers()->where('follower_people_id', people()?->id)->exists()
            && $this->id !== people()?->id) ?? false; */
    }

    private function canUnfollow(): bool
    {
        return true;

        /* return (!$this->trashed()
            && !$this->is_public
            && $this->followers()->where('follower_people_id', people()?->id)->exists()) ?? false; */
    }
}
