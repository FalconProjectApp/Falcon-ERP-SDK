<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\Stock;

use FalconERP\Skeleton\Models\Erp\Stock\WarehouseAisle;
use FalconERP\Skeleton\Models\Erp\Stock\WarehousePosition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\ActionTrait;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

class Warehouse extends BaseModel implements AuditableContract
{
    use ActionTrait;
    use Auditable;
    use HasFactory;
    use SetSchemaTrait;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'address',
        'active',
    ];

    protected $casts = [
        'active'     => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relação: Um armazém tem muitas ruas/corredores.
     */
    public function aisles(): HasMany
    {
        return $this->hasMany(WarehouseAisle::class);
    }

    /**
     * Relação: Um armazém tem muitas posições (através das ruas).
     */
    public function positions(): HasManyThrough
    {
        return $this->hasManyThrough(
            WarehousePosition::class,
            WarehouseAisle::class,
            'warehouse_id',
            'warehouse_aisle_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    |
    | Here you may specify the actions that the model should have with
    |
    */

    protected function setActions(): array
    {
        return [
            'can_view'     => $this->canView(),
            'can_restore'  => $this->canRestore(),
            'can_update'   => $this->canUpdate(),
            'can_delete'   => $this->canDelete(),
            'can_follow'   => $this->canFollow(),
            'can_unfollow' => $this->canUnfollow(),
        ];
    }

    private function canView(): bool
    {
        return true;
    }

    private function canRestore(): bool
    {
        return $this->trashed();
    }

    private function canUpdate(): bool
    {
        return !$this->trashed();
    }

    private function canDelete(): bool
    {
        return !$this->trashed() && !$this->aisles()->exists();
    }

    private function canFollow(): bool
    {
        return false;
    }

    private function canUnfollow(): bool
    {
        return false;
    }
}
