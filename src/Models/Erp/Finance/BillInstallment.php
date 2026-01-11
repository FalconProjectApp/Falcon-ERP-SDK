<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\Finance;

use FalconERP\Skeleton\Enums\ArchiveEnum;
use FalconERP\Skeleton\Enums\Finance\BillEnum;
use FalconERP\Skeleton\Events\InstallmentCheck;
use FalconERP\Skeleton\Models\Erp\People\PeopleFollow;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
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
use QuantumTecnology\ServiceBasicsExtension\Models\Archive;
use QuantumTecnology\ServiceBasicsExtension\Traits\ArchiveModelTrait;

#[ObservedBy([
    CacheObserver::class,
    NotificationObserver::class,
    EventDispatcherObserver::class,
])]
class BillInstallment extends BaseModel implements AuditableContract
{
    use ActionTrait;
    use ArchiveModelTrait;
    use Auditable;
    use HasFactory;
    use SetSchemaTrait;
    use SoftDeletes;

    public $events = [
        InstallmentCheck::class,
    ];

    protected $fillable = [
        'financial_account_id',
        'due_date',
        'issue_date',
        'value',
        'value_interest',
        'value_paid',
        'discount',
        'status',
        'obs',
    ];

    protected $appends = [
        'valueTotal',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    /**
     * Archives function.
     */
    public function archives(): MorphMany
    {
        return $this->morphMany(Archive::class, 'archivable');
    }

    /**
     * Archives function.
     */
    public function lastArchiveByName(string $name)
    {
        return $this->archives()
            ->where('name', $name)
            ->orderBy('created_at', 'desc')
            ->first();
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

    public function financialAccount(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class);
    }

    /**
     * RegistrationFile1 function.
     * Returning the main email of the people.
     */
    protected function registrationFile1(): Attribute
    {
        return new Attribute(
            get: fn () => $this->lastArchiveByName(ArchiveEnum::NAME_BILL_FILE)->s3_key ?? null,
        );
    }

    protected function valueTotal(): Attribute
    {
        return new Attribute(
            get: fn () => $this->value + $this->value_interest - $this->discount,
        );
    }

    protected function value(): Attribute
    {
        return new Attribute(
            set: fn ($value) => $value ?? 0,
        );
    }

    protected function dueDate(): Attribute
    {
        return new Attribute(
            set: fn ($value) => $value ?? now(),
        );
    }

    protected function issueDate(): Attribute
    {
        return new Attribute(
            set: fn ($value) => $value ?? now(),
        );
    }

    #[Scope]
    protected function byBillIds(Builder $query, array $bills = []): Builder
    {
        return $query->when(
            $this->filtered($bills, 'bill_ids'),
            fn ($query, $bills) => $query->whereIn('bill_id', $bills)
        );
    }

    #[Scope]
    protected function byDueDateStart(Builder $query, array $due_date_start = []): Builder
    {
        return $query->when(
            $this->filtered($due_date_start, 'due_date_start'),
            fn ($query, $due_date_start) => $query->where('due_date', '>=', $due_date_start[0])
        );
    }

    #[Scope]
    protected function byDueDateEnd(Builder $query, array $due_date_end = []): Builder
    {
        return $query->when(
            $this->filtered($due_date_end, 'due_date_end'),
            fn ($query, $due_date_end) => $query->where('due_date', '<=', $due_date_end[0])
        );
    }

    #[Scope]
    protected function byPeopleIds(Builder $query, array $people_ids = []): Builder
    {
        return $query->when(
            $this->filtered($people_ids, 'people_ids'),
            fn ($query, $people_ids) => $query->whereHas('bill', fn ($q) => $q->whereIn('people_id', $people_ids))
        );
    }

    #[Scope]
    protected function byFinancialAccountIds(Builder $query, array $financial_account_ids = []): Builder
    {
        return $query->when(
            $this->filtered($financial_account_ids, 'financial_account_ids'),
            fn ($query, $financial_account_ids) => $query->whereHas('bill', fn ($q) => $q->whereIn('financial_account_id', $financial_account_ids))
        );
    }

    #[Scope]
    protected function byType(Builder $query, array $types = []): Builder
    {
        return $query->when(
            $this->filtered($types, 'types'),
            fn ($query, $types) => $query->whereHas('bill', fn ($q) => $q->whereIn('type', $types))
        );
    }

    #[Scope]
    protected function byStatus(Builder $query, array $status = []): Builder
    {
        return $query->when(
            $this->filtered($status, 'status'),
            fn ($query, $status) => $query->whereIn('status', $status)
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
        $this->loadMissing('bill');

        return [
            'can_view'     => $this->canView(),
            'can_restore'  => $this->canRestore(),
            'can_update'   => $this->canUpdate(),
            'can_delete'   => $this->canDelete(),
            'can_follow'   => $this->canFollow(),
            'can_unfollow' => $this->canUnfollow(),
            'can_cancel'   => $this->canCancel(),
            'can_pay'      => $this->canPay(),
            'can_receive'  => $this->canReceive(),
            'can_reversal' => $this->canReversal(),
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

    private function canCancel(): bool
    {
        return !$this->trashed() && BillEnum::STATUS_OPEN === $this->status;
    }

    private function canPay(): bool
    {
        return !$this->trashed()
            && in_array($this->status, [BillEnum::STATUS_OPEN, BillEnum::STATUS_PAID_PARTIAL])
            && null !== $this->bill
            && BillEnum::TYPE_PAY === $this->bill?->type;
    }

    private function canReceive(): bool
    {
        return !$this->trashed()
            && in_array($this->status, [BillEnum::STATUS_OPEN, BillEnum::STATUS_PAID_PARTIAL])
            && null !== $this->bill
            && BillEnum::TYPE_RECEIVE === $this->bill?->type;
    }

    private function canReversal(): bool
    {
        return !$this->trashed()
            && BillEnum::STATUS_PAID === $this->status
            && null !== $this->bill;
    }
}
