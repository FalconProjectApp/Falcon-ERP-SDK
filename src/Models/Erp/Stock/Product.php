<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\Stock;

use FalconERP\Skeleton\Enums\ArchiveEnum;
use FalconERP\Skeleton\Models\Erp\People\PeopleFollow;
use FalconERP\Skeleton\Observers\CacheObserver;
use FalconERP\Skeleton\Observers\NotificationObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
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
    use SetSchemaTrait;
    use SoftDeletes;

    public const ATTRIBUTE_ID              = 'id';
    public const ATTRIBUTE_GROUPS_ID       = 'groups_id';
    public const ATTRIBUTE_VOLUME_TYPES_ID = 'volume_types_id';
    public const ATTRIBUTE_STATUS          = 'status';
    public const ATTRIBUTE_DESCRIPTION     = 'description';
    public const ATTRIBUTE_BAR_CODE        = 'bar_code';
    public const ATTRIBUTE_LAST_BUY_VALUE  = 'last_buy_value';
    public const ATTRIBUTE_LAST_SELL_VALUE = 'last_sell_value';
    public const ATTRIBUTE_LAST_RENT_VALUE = 'last_rent_value';
    public const ATTRIBUTE_PROVIDER_CODE   = 'provider_code';
    public const ATTRIBUTE_OBSERVATIONS    = 'observations';

    protected $fillable = [
        self::ATTRIBUTE_GROUPS_ID,
        self::ATTRIBUTE_VOLUME_TYPES_ID,
        self::ATTRIBUTE_STATUS,
        self::ATTRIBUTE_DESCRIPTION,
        self::ATTRIBUTE_BAR_CODE,
        self::ATTRIBUTE_LAST_BUY_VALUE,
        self::ATTRIBUTE_LAST_SELL_VALUE,
        self::ATTRIBUTE_LAST_RENT_VALUE,
        self::ATTRIBUTE_PROVIDER_CODE,
        self::ATTRIBUTE_OBSERVATIONS,
    ];

    protected $casts = [
        'birth_date' => 'date',
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

    public function volumeType(): BelongsTo
    {
        return $this->belongsTo(VolumeType::class, self::ATTRIBUTE_VOLUME_TYPES_ID)->withTrashed();
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

    public function scopeByGroupIds(Builder $query, string | array $params = []): Builder
    {
        return $query
            ->when($this->filtered($params, 'group_ids'), function ($query, $params) {
                $query->whereIn(self::ATTRIBUTE_GROUPS_ID, $params);
            });
    }

    public function scopeByVolumeTypeIds(Builder $query, string | array $params = []): Builder
    {
        return $query
            ->when($this->filtered($params, 'volume_type_ids'), function ($query, $params) {
                $query->whereIn(self::ATTRIBUTE_VOLUME_TYPES_ID, $params);
            });
    }

    public function scopeById(Builder $query, string | array $params = []): Builder
    {
        return $query
            ->when($this->filtered($params, 'ids'), function ($query, $params) {
                $query->whereIn(self::ATTRIBUTE_ID, $params);
            });
    }

    public function canView(): bool
    {
        return true;
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

    protected function ncm(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'ncm')->first()?->value,
        );
    }

    protected function unitAbbreviation(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'unit_abbreviation')->first()?->value,
        );
    }

    protected function unitDescription(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'unit_description')->first()?->value,
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
