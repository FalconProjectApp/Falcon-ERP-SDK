<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\BackOffice\DataBase;

use FalconERP\Skeleton\Models\BackOffice\DatabasesUsersAccess;
use FalconERP\Skeleton\Models\User;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use QuantumTecnology\ModelBasicsExtension\BaseModel;

class Database extends BaseModel
{
    use HasFactory;

    protected $connection = 'pgsql';

    protected $fillable = [
        'base_people_id',
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
                'base_people_id',
            ]);
    }

    public function databasesUsersAccess(): HasMany
    {
        return $this->hasMany(DatabasesUsersAccess::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(DatabaseGroup::class, 'database_group_id');
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
    public function byActiveAndUser(
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

    public function connect(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        DB::purge('tenant');

        $this->loadMissing('group');

        Config::set([
            'database.default'                           => 'tenant',
            'database.connections.tenant.driver'         => config('database.connections.pgsql.driver'),
            'database.connections.tenant.host'           => app()->isLocal() ? config('database.connections.pgsql.host') : $this->group->host,
            'database.connections.tenant.port'           => app()->isLocal() ? config('database.connections.pgsql.port') : $this->group->port,
            'database.connections.tenant.username'       => $this->group->user,
            'database.connections.tenant.password'       => Crypt::decryptString($this->group->secret),
            'database.connections.tenant.database'       => sprintf('bc_%s', $this->base),
            'database.connections.tenant.charset'        => config('database.connections.pgsql.charset'),
            'database.connections.tenant.prefix_indexes' => config('database.connections.pgsql.prefix_indexes'),
            'database.connections.tenant.search_path'    => config('database.connections.tenant.search_path', 'public'),
            'database.connections.tenant.sslmode'        => config('database.connections.pgsql.sslmode'),
        ]);

        DB::reconnect('tenant');
    }

    public function disconnect(): void
    {
        $databaseConnection = 'pgsql';

        DB::purge($databaseConnection);
        Config::set([
            'database.default' => $databaseConnection,
        ]);

        DB::reconnect($databaseConnection);

    }

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
        $this->loadMissing('userAccess');

        return Attribute::get(fn () => $this->userAccess->first()?->pivot->environment);
    }

    protected function isActive(): Attribute
    {
        $this->loadMissing('userAccess');

        return Attribute::get(fn () => $this->userAccess->first()?->pivot->is_active);
    }

    protected function basePeopleId(): Attribute
    {
        $this->loadMissing('userAccess');

        return Attribute::get(fn () => $this->userAccess->first()?->pivot->base_people_id);
    }
}
