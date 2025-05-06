<?php

namespace FalconERP\Skeleton\Models\Erp\Finance;

use OwenIt\Auditing\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use FalconERP\Skeleton\Models\Erp\People\People;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

class Bill extends BaseModel implements AuditableContract
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;
    use Auditable;

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

    
    public function scopeByPeople($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'people_ids'), fn ($query, $params) => $query->whereIn('people_id', $params));
    }

    public function scopeByFinancialAccount($query, array $params = [])
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
}
