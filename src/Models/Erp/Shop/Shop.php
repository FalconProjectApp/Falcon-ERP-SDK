<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\Shop;

use FalconERP\Skeleton\Enums\ArchiveEnum;
use FalconERP\Skeleton\Models\BackOffice\Shop as BackOfficeShop;
use FalconERP\Skeleton\Models\Erp\People\People;
use FalconERP\Skeleton\Models\Erp\People\PeopleFollow;
use FalconERP\Skeleton\Models\Erp\Service\Service;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Tools;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\ActionTrait;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;
use QuantumTecnology\ServiceBasicsExtension\Traits\ArchiveModelTrait;
use QuantumTecnology\ValidateTrait\Data;

class Shop extends BaseModel implements AuditableContract
{
    use ActionTrait;
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
        'printer_name'             => 'string',
        'printer_ip'               => 'string',
        'printer_port'             => 'string',
        'printer_model'            => 'string',
        'main_color'               => 'string',
        'whatsapp_number'          => 'string',
        'instagram'                => 'string',
        'has_automatically_finish' => 'bool',
        'certificate_password'     => 'string',
        'schemes_sped_nfe'         => 'string',
        'version_sped'             => 'string',
        'token_ibpt'               => 'string',
        'csc'                      => 'string',
        'csc_id'                   => 'string',
        'a_proxy_conf'             => 'array',
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
            ->when(request()->has('database'), fn ($query) => $query->where('database_id', request()->database->id));
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

    public function certificates(): MorphMany
    {
        return $this->archives()
            ->where('name', ArchiveEnum::NAME_CERTIFICATE_FILE);
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
    public function byType(Builder $query): Builder
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
            get: fn () => $this->segments()->where('name', 'printer_name')->first()?->value,
        );
    }

    protected function printerIp(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments()->where('name', 'printer_ip')->first()?->value,
        );
    }

    protected function printerPort(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments()->where('name', 'printer_port')->first()?->value,
        );
    }

    protected function printerModel(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments()->where('name', 'printer_model')->first()?->value,
        );
    }

    protected function mainColor(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments()->where('name', 'main_color')->first()?->value,
        );
    }

    protected function whatsappNumber(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments()->where('name', 'whatsapp_number')->first()?->value,
        );
    }

    protected function instagram(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments()->where('name', 'instagram')->first()?->value,
        );
    }

    protected function hasAutomaticallyFinish(): Attribute
    {
        return Attribute::make(
            get: fn () => (bool) $this->segments()->where('name', 'has_automatically_finish')->first()?->value,
        );
    }

    protected function certificatePassword(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments()->where('name', 'certificate_password')->first()?->value,
        );
    }

    protected function schemesSpedNfe(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments()->where('name', 'schemes_sped_nfe')->first()?->value,
        );
    }

    protected function versionSped(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments()->where('name', 'version_sped')->first()?->value,
        );
    }

    protected function tokenIbpt(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments()->where('name', 'token_ibpt')->first()?->value,
        );
    }

    protected function csc(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments()->where('name', 'csc')->first()?->value,
            set: fn ($value) => [
                'name'  => 'csc',
                'value' => $value,
            ],
        );
    }

    protected function cscId(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments()->where('name', 'csc_id')->first()?->value,
        );
    }

    protected function aProxyConf(): Attribute
    {
        return Attribute::make(
            get: fn () => json_decode($this->segments()->where('name', 'a_proxy_conf')->first()?->value ?? '{}', true),
            set: fn ($value) => [
                'name'  => 'a_proxy_conf',
                'value' => json_encode($value),
            ],
        );
    }

    /**
     * config_sped_nfe.
     */
    protected function configSpedNfe(): Attribute
    {
        return Attribute::make(
            get: fn (): Data => new Data([
                'atualizacao' => '2015-10-02 06:01:21',
                'tpAmb'       => 2,
                'razaosocial' => $this->peopleIssuer?->name,
                'siglaUF'     => $this->peopleIssuer?->main_address?->state,
                'cnpj'        => $this->peopleIssuer?->cnpj,
                'schemes'     => $this->schemes_sped_nfe,
                'versao'      => $this->version_sped,
                'tokenIBPT'   => $this->token_ibpt,
                'CSC'         => $this->csc,
                'CSCid'       => $this->csc_id,
                'aProxyConf'  => $this->a_proxy_conf,
            ]),
        );
    }

    /**
     * certificate.
     */
    protected function certificate(): Attribute
    {
        return Attribute::make(
            get: fn (): Certificate => Certificate::readPfx(base64_decode($this->certificates->first()->base64), $this->certificate_password),
        );
    }

    /**
     * sefaz.
     */
    protected function sefaz(): Attribute
    {
        return Attribute::make(
            get: fn (): Tools => new Tools($this->config_sped_nfe->toJson(), $this->certificate),
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
        return !$this->trashed();
    }

    private function canFollow(): bool
    {
        return (!$this->trashed()
            && !$this->is_public
            && !$this->followers()->where('follower_people_id', auth()->people()?->id)->exists()
            && $this->id !== auth()->people()?->id) ?? false;
    }

    private function canUnfollow(): bool
    {
        return (!$this->trashed()
            && !$this->is_public
            && $this->followers()->where('follower_people_id', auth()->people()?->id)->exists()) ?? false;
    }
}
