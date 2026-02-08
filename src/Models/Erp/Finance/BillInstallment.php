<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\Finance;

use FalconERP\Skeleton\Enums\ArchiveEnum;
use FalconERP\Skeleton\Enums\CacheEnum;
use FalconERP\Skeleton\Enums\Finance\BillEnum;
use FalconERP\Skeleton\Enums\ReleaseTypeEnum;
use FalconERP\Skeleton\Events\BillCheck;
use FalconERP\Skeleton\Models\Erp\People\PeopleFollow;
use FalconERP\Skeleton\Observers\Finance\BillInstallmentObserver;
use FalconERP\Skeleton\Traits\HasTagsTrait;
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
    BillInstallmentObserver::class,
])]
class BillInstallment extends BaseModel implements AuditableContract
{
    use ActionTrait;
    use ArchiveModelTrait;
    use Auditable;
    use HasFactory;
    use HasTagsTrait;
    use SetSchemaTrait;
    use SoftDeletes;

    public $events = [
        BillCheck::class,
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
        // Removido 'registrationFile1' do appends para evitar auto-append
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

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable', 'taggables');
    }

    public function financialAccount(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class);
    }

    public function clone(
        ?string $dueDate = null,
    ): true {
        $billInstallmentClone             = $this->replicate();
        $billInstallmentClone->due_date   = $dueDate ?? $this->due_date;
        $billInstallmentClone->status     = BillEnum::STATUS_OPEN;
        $billInstallmentClone->value_paid = 0;
        $billInstallmentClone->created_at = now();
        $billInstallmentClone->updated_at = now();
        $billInstallmentClone->save();

        return true;
    }

    public function cancel(): true
    {
        $this->status = BillEnum::STATUS_CANCELED;
        $this->save();

        return true;
    }

    public function syncStatus(): true
    {
        if (
            $this->value_paid < $this->value
            && $this->value_paid > 0
        ) {
            $this->status = BillEnum::STATUS_PAID_PARTIAL;
        }

        if (
            0 === $this->value_paid
        ) {
            $this->status = BillEnum::STATUS_OPEN;
        }

        if (
            $this->value_paid === $this->value
        ) {
            $this->status = BillEnum::STATUS_PAID;
        }

        if ($this->isDirty('status')) {
            $this->save();
        }

        return true;
    }

    public function revertValuePaid(): true
    {
        $this->value_paid = 0;

        if ($this->isDirty('value_paid')) {
            $this->save();
        }

        $this->syncStatus();

        return true;
    }

    public function updateNextInstallments(bool $updateNextInstallments = false): true
    {
        if (
            $updateNextInstallments
            && $this->bill_id
            && $this->due_date
        ) {
            self::query()
                ->where('bill_id', $this->bill_id)
                ->where('due_date', '>', $this->due_date)
                ->whereIn('status', [BillEnum::STATUS_OPEN, BillEnum::STATUS_PAID_PARTIAL])
                ->update([
                    'value'      => $this->value,
                    'updated_at' => now(),
                ]);
        }

        return true;
    }

    public function registerReceiptMovement(): true
    {
        $releaseTypeId = cache()->remember(
            config('database.connections.tenant.database') . '_' . CacheEnum::KEY_FINANCE_RELEASE_TYPE_RECEIPT_ID,
            now()->addDay(),
            fn () => ReleasesType::where('description', ReleaseTypeEnum::RELEASE_DESCRIPTION_RECEIPT)->value('id')
        );

        FinancialMovement::create(
            [
                'financial_account_id' => $this->financial_account_id,
                'releases_types_id'    => $releaseTypeId,
                'obs'                  => __('Receipt of installment ID #:id - Bill: :bill - Person: :person', [
                    'id'     => $this->id,
                    'bill'   => $this->bill_id,
                    'person' => people()->name ?? 'N/A',
                ]),
            ]
        );

        return true;
    }

    public function registerPaymentMovement(): true
    {
        $releaseTypeId = cache()->remember(
            config('database.connections.tenant.database') . '_' . CacheEnum::KEY_FINANCE_RELEASE_TYPE_PAYMENT_ID,
            now()->addDay(),
            fn () => ReleasesType::where('description', ReleaseTypeEnum::RELEASE_DESCRIPTION_PAYMENT)->value('id')
        );

        FinancialMovement::create(
            [
                'financial_account_id' => $this->financial_account_id,
                'releases_types_id'    => $releaseTypeId,
                'obs'                  => __('Payment of installment ID #:id - Bill: :bill - Person: :person', [
                    'id'     => $this->id,
                    'bill'   => $this->bill_id,
                    'person' => people()->name ?? 'N/A',
                ]),
            ]
        );

        return true;
    }

    public function financialAccountUpdate(int | string | null $financialAccountId): true
    {
        $this->financial_account_id = $financialAccountId ?? $this->bill->financial_account_id;

        if ($this->isDirty('financial_account_id')) {
            $this->save();
        }

        return true;
    }

    public function ValuePaidUpdate(int $valuePaid): true
    {
        $this->value_paid += $valuePaid;

        if ($this->isDirty('value_paid')) {
            $this->save();
        }

        $this->syncStatus();

        return true;
    }

    /**
     * RegistrationFile1 function.
     * Returning the main email of the people.
     */
    protected function registrationFile1(): Attribute
    {
        return new Attribute(
            get: function () {
                // Se archives já foi carregado com eager loading, busca da relação
                if ($this->relationLoaded('archives')) {
                    $archive = $this->archives
                        ->where('name', ArchiveEnum::NAME_BILL_FILE)
                        ->sortByDesc('created_at')
                        ->first();

                    return $archive?->s3_key ?? null;
                }

                // Caso contrário, faz a query (fallback)
                return $this->lastArchiveByName(ArchiveEnum::NAME_BILL_FILE)?->s3_key ?? null;
            },
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
    protected function byTagIds($query, array $params = []): Builder
    {
        return $query->when($this->filtered($params, 'tag_ids'), function ($query, $tagIds) {
            return $query->whereHas('tags', function ($q) use ($tagIds) {
                $q->whereIn('tags.id', $tagIds);
            });
        });
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
        // Removido loadMissing para evitar queries durante serialização
        // As relações devem ser eager loaded antes se necessário

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
            'can_clone'    => $this->canClone(),
        ];
    }

    private function canView(): bool
    {
        return true;
    }

    private function canClone(): bool
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
        // Use whereExists ao invés de carregar toda a relação
        $peopleId = people()?->id;

        return (!$this->trashed()
            && !$this->is_public
            && !$this->followers()->where('follower_people_id', $peopleId)->exists()
            && $this->id !== $peopleId) ?? false;
    }

    private function canUnfollow(): bool
    {
        // Use whereExists ao invés de carregar toda a relação
        $peopleId = people()?->id;

        return (!$this->trashed()
            && !$this->is_public
            && $this->followers()->where('follower_people_id', $peopleId)->exists()) ?? false;
    }

    private function canCancel(): bool
    {
        return !$this->trashed() && BillEnum::STATUS_OPEN === $this->status;
    }

    private function canPay(): bool
    {
        // Só verifica tipo se bill já foi carregado, evita lazy loading
        $bill = $this->relationLoaded('bill') ? $this->bill : null;

        return !$this->trashed()
            && in_array($this->status, [BillEnum::STATUS_OPEN, BillEnum::STATUS_PAID_PARTIAL])
            && null !== $bill
            && BillEnum::TYPE_PAY === $bill?->type;
    }

    private function canReceive(): bool
    {
        // Só verifica tipo se bill já foi carregado, evita lazy loading
        $bill = $this->relationLoaded('bill') ? $this->bill : null;

        return !$this->trashed()
            && in_array($this->status, [BillEnum::STATUS_OPEN, BillEnum::STATUS_PAID_PARTIAL])
            && null !== $bill
            && BillEnum::TYPE_RECEIVE === $bill?->type;
    }

    private function canReversal(): bool
    {
        // Só verifica se bill existe se já foi carregado, evita lazy loading
        $bill = $this->relationLoaded('bill') ? $this->bill : null;

        return !$this->trashed()
            && BillEnum::STATUS_PAID === $this->status
            && null !== $bill;
    }
}
