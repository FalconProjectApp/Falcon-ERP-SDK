<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Models\BackOffice\DataBase;

use FalconERP\Skeleton\Models\BackOffice\DatabasesUsersAccess;
use FalconERP\Skeleton\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use QuantumTecnology\ModelBasicsExtension\BaseModel;

class Database extends BaseModel
{
    use HasFactory;

    protected $connection = 'pgsql';

    protected $fillable = [
        'base',
    ];

    protected $casts = [
        'base' => 'string',
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
     * ActionsMovements function.
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    /**
     * userAccess function.
     *
     * @return HasMany
     */
    public function userAccess(): BelongsToMany
    {
        return $this
            ->belongsToMany(User::class, DatabasesUsersAccess::class)
            ->withPivot([
                'is_active',
                'environment',
            ]);
    }

    public function databasesUsersAccess(): HasMany
    {
        return $this->hasMany(DatabasesUsersAccess::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    | Here you may specify the scopes that the model should have with
    |
    */

    public function scopeByActiveAndUser(
        $query,
        bool $active,
        User $user,
    ) {
        return $query->with([
            'databasesUsersAccess' => fn ($query) => $query
                ->where('user_id', $user->id)
                ->when($active, fn ($query) => $query->where('is_active', true)),
        ])
            ->whereHas('databasesUsersAccess', function ($query) use ($active, $user) {
                $query
                    ->where('user_id', $user->id)
                    ->when($active, fn ($query) => $query->where('is_active', true));
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

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    |
    | Here you may specify the attributes that should be cast to native types.
    |
    */

    protected function environment(): Attribute
    {
        return Attribute::get(fn () => $this->databasesUsersAccess()->first()?->environment);
    }

    protected function isActive(): Attribute
    {
        return Attribute::get(fn () => $this->databasesUsersAccess()->first()?->is_active);
    }

    protected function basePeopleId(): Attribute
    {
        return Attribute::get(fn () => $this->databasesUsersAccess()->first()?->base_people_id);
    }
}
