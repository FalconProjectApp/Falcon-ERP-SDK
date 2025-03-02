<?php

namespace FalconERP\Skeleton\Models\Erp\Shop;

use FalconERP\Skeleton\Enums\ArchiveEnum;
use FalconERP\Skeleton\Models\BackOffice\Shop as BackOfficeShop;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use FalconERP\Skeleton\Models\Erp\Archive;
use FalconERP\Skeleton\Models\Erp\Service\Service;
use QuantumTecnology\SetSchemaTrait\SetSchemaTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Shop extends BaseModel implements AuditableContract
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;
    use Auditable;

    protected $fillable = [
        'name',
        'slug',
        'instagram',
        'whatsapp',
        'main_color',
        'obs',
        'type',
        'authorization',
        'metadata',
        'status',
        'obs',
    ];

    protected $appends = [];

    /**
     * Archives function.
     */
    public function archives(): MorphMany
    {
        return $this->morphMany(Archive::class, 'archivable');
    }

    /**
     * Services function.
     */
    public function services(): BelongsToMany
    {
        return $this->morphToMany(
            related: Service::class,
            name: 'linkable',
            table: ShopLinked::class
        )->withTimestamps();
    }

    /**
     * backOfficeShop function.
     */
    public function backOfficeShop(): MorphMany
    {
        return $this
            ->morphMany(BackOfficeShop::class, 'shopable')
            ->where('database_id', auth()->database()->id);
    }

    /**
     * Archives function.
     */
    public function lastArchiveByName(string $name)
    {
        return $this->archives()
            ->where('name', $name)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * ProfileImage function.
     */
    protected function logoImageUrl(): Attribute
    {
        return new Attribute(
            get: fn () => $this->lastArchiveByName(ArchiveEnum::NAME_SHOP_LOGO)->url ?? null,
        );
    }

    /**
     * ProfileImage function.
     */
    protected function logoImageMime(): Attribute
    {
        return new Attribute(
            get: fn () => $this->lastArchiveByName(ArchiveEnum::NAME_SHOP_LOGO)->mime ?? null,
        );
    }

    /**
     * Get the slug.
     */
    protected function slug(): Attribute
    {
        return new Attribute(
            set: fn (string $value) => Str::slug($value),
        );
    }

    /**
     * Authorization function.
     */
    protected function authorization(): Attribute
    {
        return new Attribute(
            get: fn (string $value) => json_decode($value),
        );
    }

    /**
     * Metadata function.
     */
    protected function metadata(): Attribute
    {
        return new Attribute(
            get: fn (string $value) => json_decode($value),
        );
    }

    public function scopeByType(Builder $query): Builder
    {
        return $query
            ->when(request()->filter['type'] ?? false, function ($query, $type) {
                $query->whereIn('type', explode(',', $type));
            });
    }

    public static function booting(): void
    {
        static::addGlobalScope('withRelations', function ($query) {
            $query->with([
                'backOfficeShop',
            ]);
        });
    }
}
