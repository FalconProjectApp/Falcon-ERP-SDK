<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\People;

use FalconERP\Skeleton\Database\Factories\People\PersonFactory;
use FalconERP\Skeleton\Enums\ArchiveEnum;
use FalconERP\Skeleton\Enums\People\PeopleContactEnum;
use FalconERP\Skeleton\Enums\People\PeopleCrtEnum;
use FalconERP\Skeleton\Enums\People\PeopleDocumentEnum;
use FalconERP\Skeleton\Enums\People\Type\LegalEntityTypesEnum;
use FalconERP\Skeleton\Models\BackOffice\DatabasesUsersAccess;
use FalconERP\Skeleton\Models\Erp\People\Traits\People\PeopleSegmentTrait;
use FalconERP\Skeleton\Models\User;
use FalconERP\Skeleton\Observers\NotificationObserver;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Observers\CacheObserver;
use QuantumTecnology\ModelBasicsExtension\Observers\EventDispatcherObserver;
use QuantumTecnology\ModelBasicsExtension\Traits\ActionTrait;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;
use QuantumTecnology\ServiceBasicsExtension\Traits\ArchiveModelTrait;

#[ObservedBy([
    CacheObserver::class,
    NotificationObserver::class,
    EventDispatcherObserver::class,
])]
class People extends BaseModel implements AuditableContract
{
    use ActionTrait;
    use ArchiveModelTrait;
    use Auditable;
    use HasFactory;
    use PeopleSegmentTrait;
    use SetSchemaTrait;
    use SoftDeletes;

    protected $table = 'peoples';

    protected $fillable = [
        'name',
        'types_id',
        'is_public',
        'about',
        'display_name',
        'photo_url',
    ];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'is_public' => false,
    ];

    protected $casts = [
        'birth_date'                              => 'date',
        'marital_status'                          => 'string',
        'education_level'                         => 'string',
        'gender'                                  => 'string',
        'skin_color'                              => 'string',
        'admission_date'                          => 'date',
        'demission_date'                          => 'date',
        'contract_type'                           => 'string',
        'salary_type'                             => 'string',
        'salary_value'                            => 'integer',
        'payment_day'                             => 'integer',
        'payment_method'                          => 'string',
        'job_title'                               => 'string',
        'exame_admission_date'                    => 'date',
        'exame_demission_date'                    => 'date',
        'uses_transportation_voucher'             => 'boolean',
        'transportation_voucher_value'            => 'integer',
        'uses_food_voucher'                       => 'boolean',
        'food_voucher_value'                      => 'integer',
        'uses_health_plan'                        => 'boolean',
        'health_plan_value'                       => 'integer',
        'uses_life_insurance'                     => 'boolean',
        'life_insurance_value'                    => 'integer',
        'first_job'                               => 'boolean',
        'has_disability'                          => 'boolean',
        'works_simultaneously_in_another_company' => 'boolean',
        'crt'                                     => PeopleCrtEnum::class,
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you may specify the relationships that the model should have with
    |
    */

    /**
     * @deprecated version 8.0.0 Use type() instead
     */
    public function types(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class, 'types_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function peopleContacts(): HasMany
    {
        return $this->hasMany(PeopleContact::class);
    }

    public function peopleDocuments(): HasMany
    {
        return $this->hasMany(PeopleDocument::class);
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

    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    public function users(): BelongsToMany
    {
        return $this
            ->belongsToMany(User::class, DatabasesUsersAccess::class, 'base_people_id', 'user_id')
            ->withPivot([
                'base_people_id',
                'is_active',
                'environment',
            ]);
    }

    public function files(): MorphMany
    {
        return $this->archives()
            ->where('name', ArchiveEnum::NAME_PEOPLE_FILE);
    }

    public function peopleImages()
    {
        return $this->archives()
            ->where('name', ArchiveEnum::NAME_PEOPLE_IMAGE);
    }

    public function user()
    {
        return $this
            ->users()
            ->wherePivot('database_id', database()->id)
            ->wherePivot('base_people_id', people()->id);
    }

    public function segments(): HasMany
    {
        return $this->hasMany(PeopleSegment::class);
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
    public function byCnpjCpf($query, $cnpjCpf)
    {
        return $query->where('cnpj_cpf', $cnpjCpf);
    }

    #[Scope]
    public function byTypeIds(Builder $query, string | array $params = []): Builder
    {
        return $query
            ->when($this->filtered($params, 'type_ids'), function ($query, $params) {
                $query->whereIn('types_id', $params);
            });
    }

    protected static function newFactory()
    {
        return PersonFactory::new();
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

    protected function mainEmail(): Attribute
    {
        $this->loadMissing('peopleContacts');

        return Attribute::make(
            get: fn () => $this->peopleContacts->where('type', PeopleContactEnum::TYPE_EMAIL)->first()?->value,
        );
    }

    protected function mainPhone(): Attribute
    {
        $this->loadMissing('peopleContacts');

        return Attribute::make(
            get: fn () => $this->peopleContacts->where('type', PeopleContactEnum::TYPE_PHONE)->first()?->value,
        );
    }

    protected function mainDocument(): Attribute
    {
        $this->loadMissing('peopleDocuments');

        return Attribute::make(
            get: fn () => $this->peopleDocuments()->first()?->value,
        );
    }

    protected function mainAddress(): Attribute
    {
        $this->loadMissing('addresses');

        return Attribute::make(
            get: fn () => $this->addresses->where('main', true)->first(),
        );
    }

    protected function cnpj(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->peopleDocuments()->where('type', PeopleDocumentEnum::TYPE_CNPJ)->first()?->value,
        );
    }

    protected function cpf(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->peopleDocuments()->where('type', PeopleDocumentEnum::TYPE_CPF)->first()?->value,
        );
    }

    protected function indFinal(): Attribute
    {
        return Attribute::make(
            get: fn () => (int) ((bool) $this->cpf ?? false),
        );
    }

    protected function ie(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->peopleDocuments()->where('type', PeopleDocumentEnum::TYPE_IE)->first()?->value,
        );
    }

    protected function im(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->peopleDocuments()->where('type', PeopleDocumentEnum::TYPE_IM)->first()?->value,
        );
    }

    protected function mainCnae(): Attribute
    {
        return Attribute::make(
            get: fn () => '4789099',
        );
    }

    /**
     * is_legal_entity_type.
     */
    protected function isLegalEntityType(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => LegalEntityTypesEnum::COMPANY === $this->type->legal_entity_type,
        );
    }

    protected function age(): Attribute
    {
        return Attribute::make(
            get: fn (): int => $this->birth_date?->age ?? 0,
        );
    }

    protected function isMinor(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => (int) $this->age < 18,
        );
    }

    protected function isAdult(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => (int) $this->age >= 18,
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
            && $this->id !== people()?->id;
    }

    private function canFollow(): bool
    {
        return (!$this->trashed()
            && !$this->is_public
            && !$this->followers()->where('follower_people_id', people()?->id)->exists()
            && $this->id !== people()?->id) ?? false;
    }

    private function canUnfollow(): bool
    {
        return (!$this->trashed()
            && !$this->is_public
            && $this->followers()->where('follower_people_id', people()?->id)->exists()) ?? false;
    }
}
