<?php

namespace App\Models\Erp\Finance;

use App\Models\BaseModel;
use App\Models\Erp\Stock\RequestHeader;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

class PaymentMethod extends BaseModel implements AuditableContract
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;
    use Auditable;

    protected $fillable = [
        'description',
        'observations',
        'method',
        'flag',
        'status',
    ];

    protected $appends = [];

    public function bills(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(RequestHeader::class);
    }
}
