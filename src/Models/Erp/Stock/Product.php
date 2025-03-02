<?php

namespace FalconERP\Skeleton\Models\Erp\Stock;

use FalconERP\Skeleton\Enums\ArchiveEnum;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use FalconERP\Skeleton\Models\Erp\Archive;
use QuantumTecnology\SetSchemaTrait\SetSchemaTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Product extends BaseModel implements AuditableContract
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;
    use Auditable;

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

    /**
     * Archives function.
     */
    public function archives(): MorphMany
    {
        return $this->morphMany(Archive::class, 'archivable');
    }

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

    public function scopeByGroup(Builder $query, string|array $params = []): Builder
    {
        return $query
            ->when($this->filtered($params, 'groups_id'), function ($query, $params) {
                $query->whereIn('groups_id', $params);
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
        public static function boot(): void
        {
            parent::boot();
            static::created(function (Model $model) {
            });
        } */
}
