<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\Shop;

use FalconERP\Skeleton\Models\BackOffice\Shop as BackOfficeShop;
use FalconERP\Skeleton\Models\Erp\People\People;
use FalconERP\Skeleton\Models\Erp\Service\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
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
        'responsible_people_id',
        'issuer_people_id',
        'name',
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
        'printer_name'           => 'string',
        'printer_ip'             => 'string',
        'printer_port'           => 'string',
        'printer_model'          => 'string',
        'main_color'             => 'string',
        'whatsapp_number'        => 'string',
        'instagram'              => 'string',
        'hasAutomaticallyFinish' => 'bool',
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

    public function peopleIssuer(): BelongsTo
    {
        return $this->belongsTo(
            related: People::class,
            foreignKey: 'issuer_people_id',
            ownerKey: 'id',
        );
    }

    public function peopleResponsible(): BelongsTo
    {
        return $this->belongsTo(
            related: People::class,
            foreignKey: 'responsible_people_id',
            ownerKey: 'id',
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

    protected function slug(): Attribute
    {
        return new Attribute(
            get: fn () => Str::slug($this->name),
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

    protected function hasAutomaticallyFinish(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'automatically_finish')->first()?->value,
        );
    }
}
