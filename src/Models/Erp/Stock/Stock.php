<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Models\Erp\Stock;

use FalconERP\Skeleton\Database\Factories\StockFactory;
use FalconERP\Skeleton\Models\Erp\People\PeopleFollow;
use FalconERP\Skeleton\Models\Erp\Shop\Shop;
use FalconERP\Skeleton\Models\Erp\Shop\ShopLinked;
use FalconERP\Skeleton\Models\Erp\Stock\Traits\Request\StockSegmentTrait;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Observers\CacheObserver;
use FalconERP\Skeleton\Observers\NotificationObserver;
use QuantumTecnology\ModelBasicsExtension\Traits\ActionTrait;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

#[ObservedBy([
    CacheObserver::class,
    NotificationObserver::class,
])]
class Stock extends BaseModel implements AuditableContract
{
    use ActionTrait;
    use Auditable;
    use HasFactory;
    use SetSchemaTrait;
    use SoftDeletes;
    use StockSegmentTrait;

    public const ATTRIBUTE_ID              = 'id';
    public const ATTRIBUTE_PRODUCT_ID      = 'product_id';
    public const ATTRIBUTE_VOLUME_TYPE_ID  = 'volume_type_id';
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
        self::ATTRIBUTE_PRODUCT_ID,
        self::ATTRIBUTE_VOLUME_TYPE_ID,
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

    protected $casts = [
        self::ATTRIBUTE_PRODUCT_ID     => 'integer',
        self::ATTRIBUTE_VOLUME_TYPE_ID => 'integer',
        self::ATTRIBUTE_VALUE          => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you may specify the relationships that the model should have with
    |
    */

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id')->withTrashed();
    }

    public function volumeType(): BelongsTo
    {
        return $this->belongsTo(VolumeType::class, self::ATTRIBUTE_VOLUME_TYPE_ID)->withTrashed();
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
        return $this->hasMany(StockSegment::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    | Here you may specify the scopes that the model should have with
    |
    */

    public function scopeByStockIds(Builder $query, string|array $params = []): Builder
    {
        return $query
            ->when($this->filtered($params, 'stock_ids'), function ($query, $params) {
                $query->whereIn(self::ATTRIBUTE_ID, $params);
            });
    }

    public function scopeByProductIds(Builder $query, string|array $params = []): Builder
    {
        return $query
            ->when($this->filtered($params, 'product_ids'), function ($query, $params) {
                $query->whereIn(self::ATTRIBUTE_PRODUCT_ID, $params);
            });
    }

    public function scopeByVolumeTypeIds(Builder $query, string|array $params = []): Builder
    {
        return $query
            ->when($this->filtered($params, 'volume_type_ids'), function ($query, $params) {
                $query->whereIn(self::ATTRIBUTE_VOLUME_TYPE_ID, $params);
            });
    }

    public function scopeByShopIds(Builder $query, string|array $params = []): Builder
    {
        return $query
            ->when($this->filtered($params, 'shop_ids'), function ($query, $params) {
                $query->whereHas('shops', function ($query) use ($params) {
                    $query->whereIn('shops.id', $params);
                });
            });
    }

    public function canView(): bool
    {
        return true;
    }

    protected static function newFactory()
    {
        return StockFactory::new();
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    |
    | Here you may specify the attributes that should be cast to native types.
    |
    */
    protected function balance(): Attribute
    {
        return Attribute::make(
            get: fn (): int => $this->balance_transit + $this->balance_stock,
        );
    }

    /**
     * value_total.
     */
    protected function valueTotal(): Attribute
    {
        return Attribute::make(
            get: fn (): int => $this->value * $this->balance,
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
            && !$this->followers()->where('follower_people_id', auth()->people()?->id)->exists()
        ) ?? false;
    }

    private function canUnfollow(): bool
    {
        return (
            !$this->trashed()
            && auth()->check()
            && $this->followers()->where('follower_people_id', auth()->people()?->id)->exists()
        ) ?? false;
    }
}
