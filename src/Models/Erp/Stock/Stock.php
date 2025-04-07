<?php

namespace FalconERP\Skeleton\Models\Erp\Stock;

use FalconERP\Skeleton\Models\Erp\Shop\Shop;
use FalconERP\Skeleton\Models\Erp\Shop\ShopLinked;
use FalconERP\Skeleton\Observers\CacheObserver;
use FalconERP\Skeleton\Observers\NotificationObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\ActionTrait;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

#[ObservedBy([
    CacheObserver::class,
    NotificationObserver::class,
])]
class Stock extends BaseModel implements AuditableContract
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;
    use Auditable;
    use ActionTrait;

    public const ATTRIBUTE_ID              = 'id';
    public const ATTRIBUTE_DESCRIPTION     = 'description';
    public const ATTRIBUTE_BALANCE_TRANSIT = 'balance_transit';
    public const ATTRIBUTE_BALANCE_STOCK   = 'balance_stock';
    public const ATTRIBUTE_VALUE           = 'value';
    public const ATTRIBUTE_COLOR           = 'color';
    public const ATTRIBUTE_ON_SHOP         = 'on_shop';
    public const ATTRIBUTE_MEASURE         = 'measure';
    public const ATTRIBUTE_WEIGHT          = 'weight';
    public const ATTRIBUTE_HEIGHT          = 'height';
    public const ATTRIBUTE_WIDTH           = 'width';
    public const ATTRIBUTE_DEPTH           = 'depth';
    public const ATTRIBUTE_STATUS          = 'status';
    public const ATTRIBUTE_OBS             = 'obs';

    protected $fillable = [
        self::ATTRIBUTE_ID,
        self::ATTRIBUTE_DESCRIPTION,
        self::ATTRIBUTE_BALANCE_TRANSIT,
        self::ATTRIBUTE_BALANCE_STOCK,
        self::ATTRIBUTE_VALUE,
        self::ATTRIBUTE_COLOR,
        self::ATTRIBUTE_ON_SHOP,
        self::ATTRIBUTE_MEASURE,
        self::ATTRIBUTE_WEIGHT,
        self::ATTRIBUTE_HEIGHT,
        self::ATTRIBUTE_WIDTH,
        self::ATTRIBUTE_DEPTH,
        self::ATTRIBUTE_STATUS,
        self::ATTRIBUTE_OBS,
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you may specify the relationships that the model should have with
    |
    */

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id')->withTrashed();
    }

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

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    |
    | Here you may specify the attributes that should be cast to native types.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    | Here you may specify the scopes that the model should have with
    |
    */

    public function scopeByStockId(Builder $query, string|array $params = []): Builder
    {
        return $query
            ->when($this->filtered($params, 'stock_ids'), function ($query, $params) {
                $query->whereIn(self::ATTRIBUTE_ID, $params);
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
            'can_view'     => $this->canView(),
            'can_restore'  => $this->canRestore(),
            'can_update'   => $this->canUpdate(),
            'can_delete'   => $this->canDelete(),
            'can_follow'   => $this->canFollow(),
            'can_unfollow' => $this->canUnfollow(),
        ];
    }

    public function canView(): bool
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
        return (!$this->trashed()
            && !$this->followers()->where('follower_people_id', auth()->people()->id)->exists())
            ?? false;
    }

    private function canUnfollow(): bool
    {
        return (!$this->trashed()
            && $this->followers()->where('follower_people_id', auth()->people()->id)->exists())
            ?? false;
    }
}
