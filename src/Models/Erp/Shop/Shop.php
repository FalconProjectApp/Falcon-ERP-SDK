<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\Shop;

use Illuminate\Support\Str;
use OwenIt\Auditing\Auditable;
use FalconERP\Skeleton\Enums\ArchiveEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use FalconERP\Skeleton\Models\Erp\Service\Service;
use FalconERP\Skeleton\Models\Erp\Shop\ShopSegment;
use Illuminate\Database\Eloquent\Relations\HasMany;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use FalconERP\Skeleton\Models\BackOffice\Shop as BackOfficeShop;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;
use QuantumTecnology\ServiceBasicsExtension\Traits\ArchiveModelTrait;

class Shop extends BaseModel implements AuditableContract
{
    use ArchiveModelTrait;
    use Auditable;
    use HasFactory;
    use SetSchemaTrait;
    use SoftDeletes;

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

    protected $casts = [
        'printer_name'    => 'string',
        'printer_ip'      => 'string',
        'printer_port'    => 'string',
        'printer_model'   => 'string',
        'main_color'      => 'string',
        'whatsapp_number' => 'string',
        'instagram'       => 'string',
    ];

    protected $appends = [];

    public static function booting(): void
    {
        static::addGlobalScope('withRelations', function ($query) {
            $query->with([
                'backOfficeShop',
            ]);
        });
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

    public function segments(): HasMany
    {
        return $this->hasMany(ShopSegment::class);
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

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    | Here you may specify the scopes that the model should have with
    |
    */

    public function scopeByType(Builder $query): Builder
    {
        return $query
            ->when(request()->filter['type'] ?? false, function ($query, $type) {
                $query->whereIn('type', explode(',', $type));
            });
    }

    /*
    |--------------------------------------------------------------------------
    | Others
    |--------------------------------------------------------------------------
    |
    | Here you may specify the others that the model should have with
    |
    */

    protected static function boot()
    {
        parent::boot();
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    |
    | Here you may specify the attributes that should be cast to native types.
    |
    */

    protected function logoImageUrl(): Attribute
    {
        return new Attribute(
            get: fn () => $this->lastArchiveByName(ArchiveEnum::NAME_SHOP_LOGO)->url ?? null,
        );
    }

    protected function logoImageMime(): Attribute
    {
        return new Attribute(
            get: fn () => $this->lastArchiveByName(ArchiveEnum::NAME_SHOP_LOGO)->mime ?? null,
        );
    }

    protected function slug(): Attribute
    {
        return new Attribute(
            set: fn (string $value) => Str::slug($value),
        );
    }

    protected function authorization(): Attribute
    {
        return new Attribute(
            get: fn (string $value) => json_decode($value),
        );
    }

    protected function metadata(): Attribute
    {
        return new Attribute(
            get: fn (string $value) => json_decode($value),
        );
    }

    protected function printerName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'printer_name')->first()?->value,
        );
    }

    protected function printerIp(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'printer_ip')->first()?->value,
        );
    }

    protected function printerPort(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'printer_port')->first()?->value,
        );
    }

    protected function printerModel(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'printer_model')->first()?->value,
        );
    }

    protected function mainColor(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'main_color')->first()?->value,
        );
    }

    protected function whatsappNumber(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'whatsapp_number')->first()?->value,
        );
    }

    protected function instagram(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'instagram')->first()?->value,
        );
    }
}
