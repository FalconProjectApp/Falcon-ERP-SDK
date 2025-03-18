<?php

namespace FalconERP\Skeleton\Models\Erp\Service;

use QuantumTecnology\ModelBasicsExtension\BaseModel;
use FalconERP\Skeleton\Models\Erp\Shop\Shop;
use FalconERP\Skeleton\Models\Erp\Shop\ShopLinked;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Service extends BaseModel implements AuditableContract
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;
    use Auditable;

    protected $fillable = [
        'description',
        'value',
        'service_time',
        'active',
        'observations',
    ];

    protected $appends = [];

    /**
     * ShopServices function.
     */
    public function shops(): BelongsToMany
    {
        return $this->morphToMany(
            related: Shop::class,
            name: 'linkable',
            table: ShopLinked::class
        )->withTimestamps();
    }
}
