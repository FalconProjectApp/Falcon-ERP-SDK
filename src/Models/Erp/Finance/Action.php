<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Models\Erp\Finance;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\HasManySyncable;
use QuantumTecnology\ModelBasicsExtension\Traits\ActionTrait;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

class Action extends BaseModel implements AuditableContract
{
    use ActionTrait;
    use Auditable;
    use HasFactory;
    use SetSchemaTrait;
    use SoftDeletes;

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

    public function actionsMovements(): HasManySyncable
    {
        return $this->hasMany(ActionMovement::class);
    }

    public function actionsDividends(): HasManySyncable
    {
        return $this->hasMany(ActionDividend::class);
    }

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }

    #[Scope]
    public function byDescription(Builder $query, string $description, string $operation = '='): Builder
    {
        return $query->where('description', $operation, $description);
    }

    #[Scope]
    public function byPortfolioID(Builder $query, int $portfolioId, string $operation = '='): Builder
    {
        return $query->where('portfolio_id', $operation, $portfolioId);
    }

    public function buyMovementCreate(
        string $description,
        int $amount = 0,
        int $value = 0,
        ?string $payday = null,
    ): true {
        $this->actionsMovements()->create([
            'description' => sprintf('Compra de %s', $description),
            'types'       => 'compra',
            'amount'      => $amount,
            'value'       => $value,
            'payday'      => $payday ?? now()->toDateString(),
        ]);

        return true;
    }

    public function sellMovementCreate(
        string $description,
        int $amount = 0,
        int $value = 0,
        ?string $payday = null,
    ): true {
        $this->actionsMovements()->create([
            'description' => sprintf('Venda de %s', $description),
            'types'       => 'venda',
            'amount'      => $amount,
            'value'       => $value,
            'payday'      => $payday ?? now()->toDateString(),
        ]);

        return true;
    }

    public function earningCreate(
        int $dividendEventId,
        string $paymentForecast,
        string $approvalDate,
        int $amount = 0,
        int $value = 0,
    ): true {
        $this->actionsDividends()->create([
            'description'       => sprintf('Rendimento de %s', $this->description),
            'dividend_event_id' => $dividendEventId,
            'payment_forecast'  => $paymentForecast,
            'approval_date'     => $approvalDate,
            'amount'            => $amount,
            'value'             => $value,
        ]);

        return true;
    }

    public function sell(
        int $amount = 0,
        int $value = 0,
    ): true {
        $this->update([
            'amount_total' => $this->amount_total - $amount,
            'value_total'  => $this->value_total + ($this->amount_total * $value),
        ]);

        return true;
    }

    protected function amountTotal(): Attribute
    {
        $this->loadMissing('actionsMovements');

        $total = 0;

        foreach ($this->actionsMovements as $movement) {
            $total += $movement->amount;
        }

        return new Attribute(
            get: fn () => $total,
        );
    }

    protected function valueTotal(): Attribute
    {
        $this->loadMissing('actionsMovements');

        $total = 0;

        foreach ($this->actionsMovements as $movement) {
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
        $this->loadMissing('actionsMovements');

        $total = 0;

        foreach ($this->actionsMovements as $movement) {
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
        $this->loadMissing('actionsMovements');

        $total = 0;

        foreach ($this->actionsMovements as $movement) {
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
        $this->loadMissing('actionsDividends');

        $total = 0;

        foreach ($this->actionsDividends as $dividends) {
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
            'can_buy'      => true,
            'can_sell'     => true,
            'can_earning'  => true,
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
