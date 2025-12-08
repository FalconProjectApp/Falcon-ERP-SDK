<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\Stock;

use FalconERP\Skeleton\Database\Factories\ProductFactory;
use FalconERP\Skeleton\Enums\ArchiveEnum;
use FalconERP\Skeleton\Models\Erp\People\PeopleFollow;
use FalconERP\Skeleton\Models\Erp\Stock\Traits\Request\ProductCollunsTrait;
use FalconERP\Skeleton\Models\Erp\Stock\Traits\Request\ProductSegmentTrait;
use FalconERP\Skeleton\Observers\NotificationObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
class Product extends BaseModel implements AuditableContract
{
    use ActionTrait;
    use ArchiveModelTrait;
    use Auditable;
    use HasFactory;
    use ProductCollunsTrait;
    use ProductSegmentTrait;
    use SetSchemaTrait;
    use SoftDeletes;

    protected $fillable = [
        self::ATTRIBUTE_GROUPS_ID,
        self::ATTRIBUTE_STATUS,
        self::ATTRIBUTE_DESCRIPTION,
        self::V_ATTRIBUTE_EAN,
        self::V_ATTRIBUTE_NCM,
        self::ATTRIBUTE_LAST_BUY_VALUE,
        self::ATTRIBUTE_LAST_SELL_VALUE,
        self::ATTRIBUTE_LAST_RENT_VALUE,
        self::ATTRIBUTE_OBSERVATIONS,
    ];

    protected $casts = [
        self::ATTRIBUTE_ID              => 'integer',
        self::ATTRIBUTE_GROUPS_ID       => 'integer',
        self::ATTRIBUTE_STATUS          => 'string',
        self::ATTRIBUTE_DESCRIPTION     => 'string',
        self::V_ATTRIBUTE_EAN           => 'string',
        self::V_ATTRIBUTE_NCM           => 'string',
        self::ATTRIBUTE_LAST_BUY_VALUE  => 'integer',
        self::ATTRIBUTE_LAST_SELL_VALUE => 'integer',
        self::ATTRIBUTE_LAST_RENT_VALUE => 'integer',
        self::ATTRIBUTE_OBSERVATIONS    => 'string',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you may specify the relationships that the model should have with
    |
    */

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function productComments(): HasMany
    {
        return $this->hasMany(ProductComment::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, self::ATTRIBUTE_GROUPS_ID);
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

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    | Here you may specify the scopes that the model should have with
    |
    */

    #[Scope]
    public function byGroupIds(Builder $query, string | array $params = []): Builder
    {
        return $query
            ->when($this->filtered($params, 'group_ids'), function ($query, $params) {
                $query->whereIn(self::ATTRIBUTE_GROUPS_ID, $params);
            });
    }

    #[Scope]
    public function byIds(Builder $query, string | array $params = []): Builder
    {
        return $query
            ->when($this->filtered($params, 'ids'), function ($query, $params) {
                $query->whereIn(self::ATTRIBUTE_ID, $params);
            });
    }

    protected static function newFactory()
    {
        return ProductFactory::new();
    }

    /**
     * ProductImageUrl function.
     */
    protected function productImageUrl(): Attribute
    {
        return new Attribute(
            get: fn () => $this->lastArchiveByName(ArchiveEnum::NAME_PRODUCT_IMAGE)->url ?? null,
        );
    }

    protected function balanceTransitTotal(): Attribute
    {
        $this->loadMissing('stocks');

        return Attribute::make(
            get: fn () => $this
                ->stocks()
                ->when(request()->hasHeader('x-shop-name'), function ($query) {
                    $query->whereHas('shops', function ($query) {
                        $query->where('name', request()->header('x-shop-name'));
                    });
                })
                ->sum('balance_transit'),
        );
    }

    protected function balanceStockTotal(): Attribute
    {
        $this->loadMissing('stocks');

        return Attribute::make(
            get: fn (): int => $this
                ->stocks()
                ->when(request()->hasHeader('x-shop-name'), function ($query) {
                    $query->whereHas('shops', function ($query) {
                        $query->where('name', request()->header('x-shop-name'));
                    });
                })->sum('balance_stock'),
        );
    }

    protected function balanceTotal(): Attribute
    {
        return Attribute::make(
            get: fn (): int => $this->balance_stock_total + $this->balance_transit_total,
        );
    }

    protected function valueTotal(): Attribute
    {
        $this->loadMissing('stocks');

        return Attribute::make(
            get: fn () => $this
                ->stocks()
                ->when(request()->hasHeader('x-shop-name'), function ($query) {
                    $query->whereHas('shops', function ($query) {
                        $query->where('name', request()->header('x-shop-name'));
                    });
                })->sum('value'),
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
        return !$this->trashed()
            && $this->stocks()->count() > 1;
    }

    private function canFollow(): bool
    {
        return (!$this->trashed()
            && !$this->followers()->where('follower_people_id', people()->id)->exists())
            ?? false;
    }

    private function canUnfollow(): bool
    {
        return (!$this->trashed()
            && $this->followers()->where('follower_people_id', people()->id)->exists())
            ?? false;
    }
}
