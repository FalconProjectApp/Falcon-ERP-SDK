<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\People;

use FalconERP\Skeleton\Enums\ArchiveEnum;
use FalconERP\Skeleton\Models\BackOffice\DatabasesUsersAccess;
use FalconERP\Skeleton\Models\User;
use FalconERP\Skeleton\Observers\CacheObserver;
use FalconERP\Skeleton\Observers\NotificationObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
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
use QuantumTecnology\ModelBasicsExtension\Traits\ActionTrait;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;
use QuantumTecnology\ServiceBasicsExtension\Traits\ArchiveModelTrait;

#[ObservedBy([
    CacheObserver::class,
    NotificationObserver::class,
])]
class People extends BaseModel implements AuditableContract
{
    use ActionTrait;
    use ArchiveModelTrait;
    use Auditable;
    use HasFactory;
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
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you may specify the relationships that the model should have with
    |
    */

    public function types(): BelongsTo
    {
        return $this->belongsTo(Type::class);
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
            ->wherePivot('database_id', auth()->database()->id)
            ->wherePivot('base_people_id', auth()->people()->id);
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

    public function scopeByCnpjCpf($query, $cnpjCpf)
    {
        return $query->where('cnpj_cpf', $cnpjCpf);
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

    protected function bank(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'bank')->first()?->value,
        );
    }

    protected function agency(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'agency')->first()?->value,
        );
    }

    protected function currentAccount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'current_account')->first()?->value,
        );
    }

    protected function birthDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'birth_date')->first()?->value,
        );
    }

    protected function maritalStatus(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'marital_status')->first()?->value,
        );
    }

    protected function educationLevel(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'education_level')->first()?->value,
        );
    }

    protected function gender(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'gender')->first()?->value,
        );
    }

    protected function skinColor(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'skin_color')->first()?->value,
        );
    }

    protected function admissionDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'admission_date')->first()?->value,
        );
    }

    protected function demissionDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'demission_date')->first()?->value,
        );
    }

    protected function contractType(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'contract_type')->first()?->value,
        );
    }

    protected function salaryType(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'salary_type')->first()?->value,
        );
    }

    protected function salaryValue(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'salary_value')->first()?->value,
        );
    }

    protected function paymentDay(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'payment_day')->first()?->value,
        );
    }

    protected function paymentMethod(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'payment_method')->first()?->value,
        );
    }

    protected function jobTitle(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'job_title')->first()?->value,
        );
    }

    protected function exameAdmissionDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'exame_admission_date')->first()?->value,
        );
    }

    protected function exameDemissionDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'exame_demission_date')->first()?->value,
        );
    }

    protected function usesTransportationVoucher(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'uses_transportation_voucher')->first()?->value,
        );
    }

    protected function transportationVoucherValue(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'transportation_voucher_value')->first()?->value,
        );
    }

    protected function usesFoodVoucher(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'uses_food_voucher')->first()?->value,
        );
    }

    protected function foodVoucherValue(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'food_voucher_value')->first()?->value,
        );
    }

    protected function usesHealthPlan(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'uses_health_plan')->first()?->value,
        );
    }

    protected function healthPlanValue(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'health_plan_value')->first()?->value,
        );
    }

    protected function usesLifeInsurance(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'uses_life_insurance')->first()?->value,
        );
    }

    protected function lifeInsuranceValue(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'life_insurance_value')->first()?->value,
        );
    }

    protected function firstJob(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'first_job')->first()?->value,
        );
    }

    protected function hasDisability(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'has_disability')->first()?->value,
        );
    }

    protected function worksSimultaneouslyInAnotherCompany(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->segments->where('name', 'works_simultaneously_in_another_company')->first()?->value,
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
            && $this->id !== auth()->people()?->id;
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
