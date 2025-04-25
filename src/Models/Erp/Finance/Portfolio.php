<?php

namespace FalconERP\Skeleton\Models\Erp\Finance;

use Carbon\Carbon;
use OwenIt\Auditing\Auditable;
use Illuminate\Database\Eloquent\Builder;
use FalconERP\Skeleton\Models\Erp\Billing;
use Illuminate\Database\Eloquent\SoftDeletes;
use FalconERP\Skeleton\Models\Erp\People\People;
use Illuminate\Database\Eloquent\Casts\Attribute;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use QuantumTecnology\ModelBasicsExtension\HasManySyncable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

class Portfolio extends BaseModel implements AuditableContract
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;
    use Auditable;

    protected $fillable = [
        'description',
        'people_id',
    ];

    protected $appends = [
        'value_total',
        'dividend_value_total',
        'investment_value_total',
        'invested_value_total',
    ];

    public $allowedIncludes = [
        'people',
        'people.peopleDocuments',
        'actions',
    ];

    /**
     * Get the value Total.
     */
    protected function valueTotal(): Attribute
    {
        $total = 0;

        foreach ($this->actions as $action) {
            $total += $action->valueTotal;
        }

        return new Attribute(
            get: fn () => $total,
        );
    }

    /**
     * Get the dividend Value Total.
     */
    protected function dividendValueTotal(): Attribute
    {
        $total = 0;
        foreach ($this->actions as $action) {
            $total += $action->value_total_earnings;
        }

        return new Attribute(
            get: fn () => $total,
        );
    }

    /**
     * Get the invested Value Total.
     */
    protected function investedValueTotal(): Attribute
    {
        $total = 0;
        foreach ($this->actions as $action) {
            $total += $action->value_total_buy;
        }

        return new Attribute(
            get: fn () => $total,
        );
    }

    /**
     * Get the valueTotal.
     */
    protected function investmentValueTotal(): Attribute
    {
        $total = 0;
        foreach ($this->actions as $action) {
            $total += $action->value_total_sell;
            $total += $action->value_total_buy;
            $total += $action->value_total_earnings;
        }

        return new Attribute(
            get: fn () => $total,
        );
    }

    public function people(): BelongsTo
    {
        return $this->belongsTo(People::class);
    }

    /**
     * Actions function.
     */
    public function actions(): HasManySyncable
    {
        return $this->hasMany(Action::class);
    }

    /**
     * TODO Remover quando migrar para aws
     * Utilizado para quando o request vim com array com coluna repetida.
     * orWhere[][descricao].
     */
    public function arrayWhereOr(Builder $query, array $orWhere): Builder
    {
        foreach ($orWhere as $indice => $value) {
            if (is_array($value)) {
                $this->arrayWhereOr($query, $value);
            } else {
                $indice = 'cpfcnpj' == $indice ?
                    "translate({$indice}, '.,-/', '')" : $indice;
                $query->orWhereRaw(
                    "UPPER({$indice}::text)
                    like UPPER('%{$value}%')"
                );
            }
        }

        return $query;
    }

    /**
     * TODO Remover quando migrar para aws
     * Utilizado para quando o request vim com array com coluna repetida.
     * Where[][descricao].
     */
    public function arrayWhere(Builder $query, array $where): Builder
    {
        foreach ($where as $indice => $value) {
            if (is_array($value)) {
                $this->arrayWhere($query, $value);
            } else {
                $indice = 'cpfcnpj' == $indice ?
                    "translate({$indice}, '.,-/', '')" : $indice;
                $query->WhereRaw(
                    "UPPER({$indice}::text)
                    like UPPER('%{$value}%')"
                );
            }
        }

        return $query;
    }

    public static function boot(): void
    {
        parent::boot();
        static::updating(function () {
            if (!Billing::where('rule', 'billing.rules.modules.finance.portfolio.update')->exists()) {
                Billing::create([
                    'rule'                 => 'billing.rules.modules.finance.portfolio.update',
                    'effective_start_date' => Carbon::now(),
                    'effective_end_date'   => Carbon::now(),
                ]);
            }
        });
        static::updated(function () {
            $billing = Billing::whereColumn('effective_start_date', '!=', 'effective_end_date')
                ->where('rule', 'billing.rules.modules.finance.portfolio.update')
                ->first();

            if (!$billing) {
                $billing = Billing::whereColumn('effective_start_date', 'effective_end_date')
                    ->where('rule', 'billing.rules.modules.finance.portfolio.update')
                    ->first();
            }

            if ($billing) {
                $billing->update([
                    'used_quantity' => $billing->used_quantity + 1,
                ]);
            }
        });
    }
}
