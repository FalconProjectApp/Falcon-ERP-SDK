<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Models\Erp\Finance;

use FalconERP\Skeleton\Events\BillCheck;
use FalconERP\Skeleton\Models\Erp\People\People;
use FalconERP\Skeleton\Models\Erp\People\PeopleFollow;
use FalconERP\Skeleton\Observers\Finance\BillObserver;
use FalconERP\Skeleton\Traits\HasTagsTrait;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
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
    BillObserver::class,
])]
class Bill extends BaseModel implements AuditableContract
{
    use ActionTrait;
    use Auditable;
    use HasFactory;
    use HasTagsTrait;
    use SetSchemaTrait;
    use SoftDeletes;

    public $events = [
        BillCheck::class,
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

    public function followers(): MorphToMany
    {
        return $this
            ->morphToMany(static::class, 'followable', PeopleFollow::class, 'followable_id', 'follower_people_id')
            ->withTimestamps()
            ->withTrashed();
    }

    public function followings(): MorphToMany
    {
        return $this
            ->morphToMany(static::class, 'followable', PeopleFollow::class, 'follower_people_id', 'followable_id')
            ->withTimestamps()
            ->withTrashed();
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable', 'taggables');
    }

    #[Scope]
    protected function byTagIds($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'tag_ids'), function ($query, $tagIds) {
            return $query->whereHas('tags', function ($q) use ($tagIds) {
                $q->whereIn('tags.id', $tagIds);
            });
        });
    }

    #[Scope]
    protected function byPeopleIds($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'people_ids'), fn ($query, $params) => $query->whereIn('people_id', $params));
    }

    #[Scope]
    protected function byFinancialAccountIds($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'financial_account_ids'), fn ($query, $params) => $query->whereIn('financial_account_id', $params));
    }

    #[Scope]
    protected function byType($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'type'), fn ($query, $params) => $query->whereIn('type', $params));
    }

    #[Scope]
    protected function byRepetition($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'repetition'), fn ($query, $params) => $query->whereIn('repetition', $params));
    }

    #[Scope]
    protected function byPeriodicity($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'periodicity'), fn ($query, $params) => $query->whereIn('periodicity', $params));
    }

    #[Scope]
    protected function byStatus($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'status'), fn ($query, $params) => $query->whereIn('status', $params));
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
        return (!$this->trashed()
            && !$this->is_public
            && !$this->followers()->where('follower_people_id', people()?->id)->exists()
            && $this->id !== people()?->id) ?? false;
    }

    private function canUnfollow(): bool
    {
        return (!$this->trashed()
            && !$this->is_public
            && $this->followers()->where('follower_people_id', people()?->id)->exists()) ?? false;
    }
}
