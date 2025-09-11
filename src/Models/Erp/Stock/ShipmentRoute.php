<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\Stock;

use FalconERP\Skeleton\Database\Factories\ShipmentFactory;
use FalconERP\Skeleton\Enums\ArchiveEnum;
use FalconERP\Skeleton\Observers\NotificationObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Observers\CacheObserver;
use QuantumTecnology\ModelBasicsExtension\Traits\ActionTrait;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;
use QuantumTecnology\ServiceBasicsExtension\Traits\ArchiveModelTrait;

#[ObservedBy([
    CacheObserver::class,
    NotificationObserver::class,
])]
class ShipmentRoute extends BaseModel implements AuditableContract
{
    use ActionTrait;
    use ArchiveModelTrait;
    use Auditable;
    use HasFactory;
    use SetSchemaTrait;
    use SoftDeletes;

    public const ATTRIBUTE_ID                         = 'id';
    public const ATTRIBUTE_SHIPMENT_ID                = 'shipment_id';
    public const ATTRIBUTE_ROUTE                      = 'route';
    public const ATTRIBUTE_PRIORITY                   = 'priority';
    public const ATTRIBUTE_DRIVER_DEPARTURE           = 'departured_at';
    public const ATTRIBUTE_DRIVER_ARRIVAL             = 'arrived_at';
    public const ATTRIBUTE_DRIVER_ESTIMATED_ARRIVAL   = 'estimated_arrival_at';
    public const ATTRIBUTE_DRIVER_ESTIMATED_DEPARTURE = 'estimated_departure_at';
    public const ATTRIBUTE_STATUS                     = 'status';

    protected $fillable = [
        self::ATTRIBUTE_SHIPMENT_ID,
        self::ATTRIBUTE_ROUTE,
        self::ATTRIBUTE_PRIORITY,
        self::ATTRIBUTE_DRIVER_DEPARTURE,
        self::ATTRIBUTE_DRIVER_ARRIVAL,
        self::ATTRIBUTE_DRIVER_ESTIMATED_ARRIVAL,
        self::ATTRIBUTE_DRIVER_ESTIMATED_DEPARTURE,
        self::ATTRIBUTE_STATUS,

    ];

    protected $casts = [
        self::ATTRIBUTE_ID                         => 'integer',
        self::ATTRIBUTE_SHIPMENT_ID                => 'integer',
        self::ATTRIBUTE_ROUTE                      => 'string',
        self::ATTRIBUTE_PRIORITY                   => 'integer',
        self::ATTRIBUTE_DRIVER_DEPARTURE           => 'datetime',
        self::ATTRIBUTE_DRIVER_ARRIVAL             => 'datetime',
        self::ATTRIBUTE_DRIVER_ESTIMATED_ARRIVAL   => 'datetime',
        self::ATTRIBUTE_DRIVER_ESTIMATED_DEPARTURE => 'datetime',
        self::ATTRIBUTE_STATUS                     => 'string',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you may specify the relationships that the model should have with
    |
    */

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    |
    | Here you may specify the attributes that should be cast to native types.
    |
    */

    /**
     * ProductImages function.
     */
    public function productImages()
    {
        return $this->archives()
            ->where('name', ArchiveEnum::NAME_PRODUCT_IMAGE);
    }

    /**
     * LastArchiveByName function.
     */
    public function lastArchiveByName(string $name)
    {
        return $this->archives()
            ->where('name', $name)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    protected static function newFactory()
    {
        return ShipmentFactory::new();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    | Here you may specify the scopes that the model should have with
    |
    */

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
        return !$this->trashed();
    }

    private function canFollow(): bool
    {
        return (
            !$this->trashed()
            && auth()->check()
            && !$this->followers()->where('follower_people_id', auth()->people()->id)->exists()
        ) ?? false;
    }

    private function canUnfollow(): bool
    {
        return (
            !$this->trashed()
            && auth()->check()
            && $this->followers()->where('follower_people_id', auth()->people()->id)->exists()
        ) ?? false;
    }
}
