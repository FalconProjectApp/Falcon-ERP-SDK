<?php

namespace FalconERP\Skeleton\Models\Erp\Finance;

use QuantumTecnology\ModelBasicsExtension\BaseModel;
use FalconERP\Skeleton\Models\Erp\People\People;
use QuantumTecnology\SetSchemaTrait\SetSchemaTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class BillToReceive extends BaseModel implements AuditableContract
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;
    use Auditable;

    protected $fillable = [
        'description',
        'number',
        'order',
        'due_date',
        'issue_date',
        'people_id',
        'value_interest',
        'value',
        'value_total',
        'status',
        'obs',
    ];

    public $allowedIncludes = [
        'people',
        'people.peopleDocuments',
    ];

    public function people(): BelongsTo
    {
        return $this->belongsTo(People::class);
    }

    public static function boot(): void
    {
        parent::boot();
    }
}
