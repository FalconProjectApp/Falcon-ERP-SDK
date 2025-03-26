<?php

namespace FalconERP\Skeleton\Models\Erp\People;

use FalconERP\Skeleton\Enums\ArchiveEnum;
use FalconERP\Skeleton\Models\BackOffice\DatabasesUsersAccess;
use FalconERP\Skeleton\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\ActionTrait;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;
use QuantumTecnology\ServiceBasicsExtension\Traits\ArchiveModelTrait;

class People extends BaseModel implements AuditableContract
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;
    use Auditable;
    use ActionTrait;
    use ArchiveModelTrait;

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

    public function types()
    {
        return $this->belongsTo(Type::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function peopleContacts()
    {
        return $this->hasMany(PeopleContact::class);
    }

    public function peopleDocuments()
    {
        return $this->hasMany(PeopleDocument::class);
    }

    public function followers()
    {
        return $this->morphToMany(People::class, 'followable', PeopleFollow::class, 'followable_id', 'follower_people_id')->withTimestamps();
    }

    public function users()
    {
        return $this
            ->belongsToMany(User::class, DatabasesUsersAccess::class, 'base_people_id', 'user_id')
            ->withPivot([
                'base_people_id',
                'is_active',
                'environment',
            ]);
    }

    public function user()
    {
        return $this
            ->users()
            ->wherePivot('database_id', auth()->database()->id)
            ->wherePivot('base_people_id', auth()->people()->id);
    }

    /**
     * PeopleImages function.
     */
    public function peopleImages()
    {
        return $this->archives()
            ->where('name', ArchiveEnum::NAME_PEOPLE_IMAGE);
    }

    public function scopeByCnpjCpf($query, $cnpjCpf)
    {
        return $query->where('cnpj_cpf', $cnpjCpf);
    }

    protected function setActions(): array
    {
        return [
            'can_view'     => true,
            'can_restore'  => $this->trashed(),
            'can_update'   => !$this->trashed(),
            'can_delete'   => !$this->trashed(),
            'can_follow'   => $this->canFollow(),
            'can_unfollow' => $this->canUnfollow(),
        ];
    }

    private function canFollow(): bool
    {
        return (!$this->trashed()
            && !$this->is_public
            && !$this->followers()->where('follower_people_id', auth()->people()->id)->exists()
            && $this->id !== auth()->people()->id) ?? false;
    }

    private function canUnfollow(): bool
    {
        return (!$this->trashed()
            && !$this->is_public
            && $this->followers()->where('follower_people_id', auth()->people()->id)->exists()) ?? false;
    }
}
