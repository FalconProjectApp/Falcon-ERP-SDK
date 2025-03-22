<?php

namespace App\Models\BackOffice;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class GiftCode extends BaseModel implements AuditableContract
{
    use HasFactory;
    use SoftDeletes;
    use Auditable;

    protected $connection = 'pgsql';

    protected $fillable = [];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
