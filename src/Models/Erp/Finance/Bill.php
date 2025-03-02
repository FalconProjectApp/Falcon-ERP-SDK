<?php

namespace FalconERP\Skeleton\Models\Erp\Finance;

use QuantumTecnology\ModelBasicsExtension\BaseModel;
use FalconERP\Skeleton\Models\Erp\People\People;
use QuantumTecnology\SetSchemaTrait\SetSchemaTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

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

    protected function installmentTotal(): Attribute
    {
        return new Attribute(
            get: fn () => $this->billInstallments->sum('value_total'),
        );
    }
}
