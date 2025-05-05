<?php

namespace FalconERP\Skeleton\Models\BackOffice;

use OwenIt\Auditing\Auditable;
use FalconERP\Skeleton\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class GiftCode extends BaseModel implements AuditableContract
{
    use HasFactory;
    use SoftDeletes;
    use Auditable;

    protected $connection = 'pgsql';

    protected $fillable = [
        'owner_id',
        'code',
        'owner_bonus',
        'client_bonus',
        'max_uses',
        'uses',
        'used_at',
        'expires_at',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
