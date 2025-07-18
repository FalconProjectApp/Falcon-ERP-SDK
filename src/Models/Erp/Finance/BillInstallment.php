<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\Finance;

use OwenIt\Auditing\Auditable;
use FalconERP\Skeleton\Enums\ArchiveEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use FalconERP\Skeleton\Enums\Finance\BillEnum;
use FalconERP\Skeleton\Events\InstallmentCheck;
use Illuminate\Database\Eloquent\Casts\Attribute;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use QuantumTecnology\ServiceBasicsExtension\Models\Archive;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use QuantumTecnology\ModelBasicsExtension\Traits\ActionTrait;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;
use QuantumTecnology\ModelBasicsExtension\Observers\CacheObserver;
use QuantumTecnology\ServiceBasicsExtension\Traits\ArchiveModelTrait;
use FalconERP\Skeleton\Observers\NotificationObserver;
use QuantumTecnology\ModelBasicsExtension\Observers\EventDispatcherObserver;

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
        'due_date',
        'issue_date',
        'value',
        'value_interest',
        'value_paid',
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
            get: fn () => $this->value + $this->value_interest,
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

    protected function scopeByBillIds(Builder $query, array $bills = []): Builder
    {
        return $query->when(
            $this->filtered($bills, 'bill_ids'),
            fn ($query, $bills) => $query->whereIn('bill_id', $bills)
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

    private function canCancel(): bool
    {
        return !$this->trashed() && BillEnum::STATUS_OPEN === $this->status;
    }

    private function canPay(): bool
    {
        return !$this->trashed()
            && BillEnum::STATUS_OPEN === $this->status
            && null !== $this->bill
            && BillEnum::TYPE_PAY === $this->bill?->type;
    }

    private function canReceive(): bool
    {
        return !$this->trashed()
            && BillEnum::STATUS_OPEN === $this->status
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
