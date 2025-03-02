<?php

namespace FalconERP\Skeleton\Models\Erp\Stock;

use QuantumTecnology\ModelBasicsExtension\BaseModel;
use FalconERP\Skeleton\Models\Erp\People\People;
use QuantumTecnology\SetSchemaTrait\SetSchemaTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestHeader extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;

    protected $fillable = [
        'description',
        'observations',
        'status',
        'request_type_id',
        'responsible_id',
        'third_id',
        'allower_id',
    ];

    public function requestBodies(): HasMany
    {
        return $this->hasMany(RequestBody::class);
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(People::class, 'responsible_id');
    }

    public function third(): BelongsTo
    {
        return $this->belongsTo(People::class, 'third_id');
    }

    public function allower(): BelongsTo
    {
        return $this->belongsTo(People::class, 'allower_id');
    }

    public function requestType(): BelongsTo
    {
        return $this->belongsTo(RequestType::class);
    }
}
