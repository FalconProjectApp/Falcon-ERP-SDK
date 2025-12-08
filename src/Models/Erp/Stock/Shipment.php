<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\Stock;

use App\Policies\ShipmentPolicy;
use FalconERP\Skeleton\Database\Factories\ShipmentFactory;
use FalconERP\Skeleton\Enums\ArchiveEnum;
use FalconERP\Skeleton\Enums\Stock\Shipment\ShipmentStatusEnum;
use FalconERP\Skeleton\Models\Erp\People\People;
use FalconERP\Skeleton\Models\Erp\People\PeopleFollow;
use FalconERP\Skeleton\Observers\NotificationObserver;
use Gate;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
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
#[UsePolicy(ShipmentPolicy::class)]
class Shipment extends BaseModel implements AuditableContract
{
    use ActionTrait;
    use ArchiveModelTrait;
    use Auditable;
    use HasFactory;
    use SetSchemaTrait;
    use SoftDeletes;
    // use HasUuids;

    public const ATTRIBUTE_ID        = 'id';
    public const ATTRIBUTE_DRIVER_ID = 'driver_id';
    public const ATTRIBUTE_STATUS    = 'status';

    public const ATTRIBUTE_DISTANCE_METERS = 'distance_meters';

    protected $fillable = [
        self::ATTRIBUTE_STATUS,
        self::ATTRIBUTE_DRIVER_ID,
        self::ATTRIBUTE_DISTANCE_METERS,
    ];

    protected $casts = [
        self::ATTRIBUTE_ID              => 'integer',
        self::ATTRIBUTE_DRIVER_ID       => 'integer',
        self::ATTRIBUTE_STATUS          => ShipmentStatusEnum::class,
        self::ATTRIBUTE_DISTANCE_METERS => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you may specify the relationships that the model should have with
    |
    */

    public function driver(): BelongsTo
    {
        return $this->belongsTo(People::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function shipmentRoutes(): HasMany
    {
        return $this->hasMany(ShipmentRoute::class);
    }

    public function followers(): MorphToMany
    {
        return $this
            ->morphToMany(static::class, 'followable', PeopleFollow::class, 'followable_id', 'follower_people_id')
            ->withTimestamps()
            ->withTrashed();
    }

    public function followings(): MorphToMany
    {
        return $this
            ->morphToMany(static::class, 'followable', PeopleFollow::class, 'follower_people_id', 'followable_id')
            ->withTimestamps()
            ->withTrashed();
    }

    public function segments(): HasMany
    {
        return $this->hasMany(ProductSegment::class);
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

    #[Scope]
    protected function byStatus(Builder $query, string | array $params = []): void
    {
        $query->when($this->filtered($params, 'status'), function ($query, $params) {
            $query->whereIn(self::ATTRIBUTE_STATUS, $params);
        });
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
            'can_view'              => $this->canView(),
            'can_restore'           => $this->canRestore(),
            'can_update'            => $this->canUpdate(),
            'can_delete'            => $this->canDelete(),
            'can_follow'            => $this->canFollow(),
            'can_unfollow'          => $this->canUnfollow(),
            'can_transit'           => $this->canTransit(),
            'can_deliver'           => $this->canDeliver(),
            'can_cancel'            => $this->canCancel(),
            'can_return'            => $this->canReturn(),
            'can_fail_delivery'     => $this->canFailDelivery(),
            'can_on_hold'           => $this->canOnHold(),
            'can_partially_deliver' => $this->canPartiallyDeliver(),
            'can_awaiting_pickup'   => $this->canAwaitingPickup(),
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

    private function canTransit(): bool
    {
        return Gate::inspect('transit', $this)->allowed();
    }

    private function canDeliver(): bool
    {
        return Gate::inspect('delivery', $this)->allowed();
    }

    private function canCancel(): bool
    {
        return Gate::inspect('cancel', $this)->allowed();
    }

    private function canReturn(): bool
    {
        return Gate::inspect('return', $this)->allowed();
    }

    private function canFailDelivery(): bool
    {
        return Gate::inspect('fail', $this)->allowed();
    }

    private function canOnHold(): bool
    {
        return Gate::inspect('onHold', $this)->allowed();
    }

    private function canPartiallyDeliver(): bool
    {
        return Gate::inspect('partiallyDelivered', $this)->allowed();
    }

    private function canAwaitingPickup(): bool
    {
        return Gate::inspect('awaitingPickup', $this)->allowed();
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
            && !$this->followers()->where('follower_people_id', people()->id)->exists()
        ) ?? false;
    }

    private function canUnfollow(): bool
    {
        return (
            !$this->trashed()
            && auth()->check()
            && $this->followers()->where('follower_people_id', people()->id)->exists()
        ) ?? false;
    }
}
