<?php

namespace FalconERP\Skeleton\Models\Erp\Stock;

use FalconERP\Skeleton\Enums\ArchiveEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\ActionTrait;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;
use QuantumTecnology\ServiceBasicsExtension\Traits\ArchiveModelTrait;

class Product extends BaseModel implements AuditableContract
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;
    use Auditable;
    use ActionTrait;
    use ArchiveModelTrait;

    protected $fillable = [
        'groups_id',
        'volume_types_id',
        'status',
        'description',
        'bar_code',
        'last_buy_value',
        'last_sell_value',
        'last_rent_value',
        'provider_code',
        'observations',
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
        return $this->belongsTo(VolumeType::class, 'volume_types_id')->withTrashed();
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'groups_id');
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

    /**
     * ProductImageUrl function.
     */
    protected function productImageUrl(): Attribute
    {
        return new Attribute(
            get: fn () => $this->lastArchiveByName(ArchiveEnum::NAME_PRODUCT_IMAGE)->url ?? null,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    | Here you may specify the scopes that the model should have with
    |
    */

    public function scopeByGroupIds(Builder $query, string|array $params = []): Builder
    {
        return $query
            ->when($this->filtered($params, 'group_ids'), function ($query, $params) {
                $query->whereIn('groups_id', $params);
            });
    }

    public function scopeByVolumeTypeIds(Builder $query, string|array $params = []): Builder
    {
        return $query
            ->when($this->filtered($params, 'volume_type_ids'), function ($query, $params) {
                $query->whereIn('volume_types_id', $params);
            });
    }

    public function scopeById(Builder $query, string|array $params = []): Builder
    {
        return $query
            ->when($this->filtered($params, 'id'), function ($query, $params) {
                $query->whereIn('id', $params);
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
            'can_view'    => $this->canView(),
            'can_restore' => $this->canRestore(),
            'can_create'  => $this->canCreate(),
            'can_update'  => $this->canUpdate(),
            'can_delete'  => $this->canDelete(),
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

    public function canCreate(): bool
    {
        return true;
    }

    private function canUpdate(): bool
    {
        return !$this->trashed();
    }

    private function canDelete(): bool
    {
        return !$this->trashed();
    }
}
