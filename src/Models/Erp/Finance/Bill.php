<?php

namespace FalconERP\Skeleton\Models\Erp\Finance;

use FalconERP\Skeleton\Events\InstallmentCheck;
use FalconERP\Skeleton\Models\Erp\People\People;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Observers\CacheObserver;
use QuantumTecnology\ModelBasicsExtension\Observers\EventDispatcherObserver;
use QuantumTecnology\ModelBasicsExtension\Observers\NotificationObserver;
use QuantumTecnology\ModelBasicsExtension\Traits\ActionTrait;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

#[ObservedBy([
    CacheObserver::class,
    NotificationObserver::class,
    EventDispatcherObserver::class,
])]
class Bill extends BaseModel implements AuditableContract
{
    use ActionTrait;
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;
    use Auditable;

    public $events = [
        InstallmentCheck::class,
    ];

    protected $fillable = [
        'description',
        'people_id',
        'financial_account_id',
        'type',
        'repetition',
        'periodicity',
        'status',
        'fees',
        'discount',
        'fine',
        'obs',
    ];

    protected $appends = [
        'is_installment',
        'installment_amount',
        'installment_start',
        'installment_end',
        'installment_value',
        'installment_interest',
        'installment_total',
    ];

    public function people(): BelongsTo
    {
        return $this->belongsTo(People::class);
    }

    public function financialAccount(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class);
    }

    public function billInstallments(): HasMany
    {
        return $this->hasMany(BillInstallment::class);
    }

    public function paymentMethod(): HasOne
    {
        return $this->hasOne(PaymentMethod::class);
    }

    protected function isInstallment(): Attribute
    {
        return new Attribute(
            get: fn () => $this->billInstallments->count() > 0,
        );
    }

    protected function installmentAmount(): Attribute
    {
        return new Attribute(
            get: fn () => $this->billInstallments->count(),
        );
    }

    protected function installmentStart(): Attribute
    {
        return new Attribute(
            get: fn () => $this->billInstallments->min('due_date'),
        );
    }

    protected function installmentEnd(): Attribute
    {
        return new Attribute(
            get: fn () => $this->billInstallments->max('due_date'),
        );
    }

    protected function installmentValue(): Attribute
    {
        return new Attribute(
            get: fn () => $this->billInstallments->sum('value'),
        );
    }

    protected function installmentInterest(): Attribute
    {
        return new Attribute(
            get: fn () => $this->billInstallments->sum('value_interest'),
        );
    }

    protected function installmentPaidTotal(): Attribute
    {
        return new Attribute(
            get: fn () => $this->billInstallments->sum('value_paid'),
        );
    }

    protected function installmentTotal(): Attribute
    {
        return new Attribute(
            get: fn () => $this->billInstallments->sum('value_total'),
        );
    }

    public function scopeByPeopleIds($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'people_ids'), fn ($query, $params) => $query->whereIn('people_id', $params));
    }

    public function scopeByFinancialAccountIds($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'financial_account_ids'), fn ($query, $params) => $query->whereIn('financial_account_id', $params));
    }

    public function scopeByType($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'type'), fn ($query, $params) => $query->whereIn('type', $params));
    }

    public function scopeByRepetition($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'repetition'), fn ($query, $params) => $query->whereIn('repetition', $params));
    }

    public function scopeByPeriodicity($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'periodicity'), fn ($query, $params) => $query->whereIn('periodicity', $params));
    }

    public function scopeByStatus($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'status'), fn ($query, $params) => $query->whereIn('status', $params));
    }

    public static function booting(): void
    {
        static::addGlobalScope('withRelations', function ($query) {
            $query->with([
                'billInstallments',
            ]);
        });
    }

    public static function boot(): void
    {
        parent::boot();

        static::deleting(function ($bill) {
            $bill->billInstallments()->delete();
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
            && !$this->followers()->where('follower_people_id', auth()->people()?->id)->exists()
            && $this->id !== auth()->people()?->id) ?? false; */
    }

    private function canUnfollow(): bool
    {
        return true;

        /* return (!$this->trashed()
            && !$this->is_public
            && $this->followers()->where('follower_people_id', auth()->people()?->id)->exists()) ?? false; */
    }
}
