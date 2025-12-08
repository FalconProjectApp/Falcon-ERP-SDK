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
