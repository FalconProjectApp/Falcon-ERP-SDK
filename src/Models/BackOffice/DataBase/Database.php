<?php

namespace FalconERP\Skeleton\Models\BackOffice\DataBase;

use FalconERP\Skeleton\Models\BackOffice\DatabasesUsersAccess;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use FalconERP\Skeleton\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Database extends BaseModel
{
    use HasFactory;

    protected $connection = 'pgsql';

    protected $fillable = [
        'base',
    ];

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

    public function scopeByActiveAndUser(
        $query,
        bool $active,
        User $user
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
}
