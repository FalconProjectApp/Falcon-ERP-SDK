<?php

namespace FalconERP\Skeleton\Models\Erp\Finance;

use OwenIt\Auditing\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use FalconERP\Skeleton\Models\Erp\People\People;
use Illuminate\Database\Eloquent\Relations\HasMany;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use FalconERP\Skeleton\Enums\FinancialAccountsTypeEnum;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

class FinancialAccount extends BaseModel implements AuditableContract
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;
    use Auditable;

    protected $fillable = [
        'description',
        'people_id',
        'status',
        'active',
        'obs',
    ];

    protected $attributes = [
        'type' => FinancialAccountsTypeEnum::CLIENT_TYPE,
    ];

    public function people(): BelongsTo
    {
        return $this->belongsTo(People::class);
    }

    public function financialMovement(): HasMany
    {
        return $this->hasMany(FinancialMovement::class, 'financial_accounts_id');
    }
}
