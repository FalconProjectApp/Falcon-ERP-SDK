<?php

namespace FalconERP\Skeleton\Models\Erp\Finance;

use FalconERP\Skeleton\Enums\FinancialAccountsTypeEnum;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use FalconERP\Skeleton\Models\Erp\People\People;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

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

    public $allowedIncludes = [
        'people',
        'people.peopleDocuments',
        'people.peopleContacts',
        'people.peopleImages',
    ];

    public function people(): BelongsTo
    {
        return $this->belongsTo(People::class);
    }
}
