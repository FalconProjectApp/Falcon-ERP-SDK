<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\Finance;

use OwenIt\Auditing\Auditable;
use App\Events\InstallmentCheck;
use FalconERP\Skeleton\Enums\ArchiveEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use QuantumTecnology\ServiceBasicsExtension\Models\Archive;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;
use QuantumTecnology\ModelBasicsExtension\Observers\CacheObserver;
use QuantumTecnology\ModelBasicsExtension\Observers\NotificationObserver;
use QuantumTecnology\ModelBasicsExtension\Observers\EventDispatcherObserver;

#[ObservedBy([
    CacheObserver::class,
    NotificationObserver::class,
    EventDispatcherObserver::class,
])]
class BillInstallment extends BaseModel implements AuditableContract
{
    use Auditable;
    use HasFactory;
    use SetSchemaTrait;
    use SoftDeletes;

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

    public $events = [
        InstallmentCheck::class,
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

    protected function scopeByBills(Builder $query, array $bills = []): Builder
    {
        return $query->when(
            $this->filtered($bills, 'bill_ids'),
            fn ($query, $bills) => $query->whereIn('bill_id', $bills)
        );
    }
}
