<?php

namespace FalconERP\Skeleton\Models\Erp\People;

use FalconERP\Skeleton\Enums\ArchiveEnum;
use FalconERP\Skeleton\Models\BackOffice\DatabasesUsersAccess;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use FalconERP\Skeleton\Models\Erp\Archive;
use FalconERP\Skeleton\Models\User;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class People extends BaseModel implements AuditableContract
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;
    use Auditable;

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
     * Archives function.
     */
    public function archives(): MorphMany
    {
        return $this->morphMany(Archive::class, 'archivable');
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
}
