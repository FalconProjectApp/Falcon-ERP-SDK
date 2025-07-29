<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Providers;

use FalconERP\Skeleton\Models\BackOffice\DataBase\Database;
use FalconERP\Skeleton\Models\Erp\People\People;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'FalconERP\Skeleton\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Auth::macro('setDatabase', function (
            Database $database,
        ): void {
            Config::set([
                'database.connections.tenant.driver'         => 'pgsql',
                'database.connections.tenant.host'           => app()->isLocal() ? config('database.connections.tenant.host') : $database->group->host,
                'database.connections.tenant.port'           => app()->isLocal() ? config('database.connections.tenant.port') : $database->group->port,
                'database.connections.tenant.username'       => $database->group->user,
                'database.connections.tenant.password'       => Crypt::decryptString($database->group->secret),
                'database.connections.tenant.database'       => sprintf('bc_%s', $database->base),
                'database.connections.tenant.charset'        => 'utf8',
                'database.connections.tenant.prefix_indexes' => true,
                'database.connections.tenant.schema'         => 'public',
                'database.connections.tenant.sslmode'        => 'prefer',
                'database.default'                           => 'tenant',
            ]);

            request()->merge([
                'database' => $database,
            ]);
        });

        Auth::macro('database', function (
            bool $active = true,
            bool $refresh = false,
        ) {
            /*
             * Se o usuário estiver logado, então ele tem acesso a um ou mais bancos de dados.
             */
            if (static::check()) {
                $database = Database::byActiveAndUser($active, static::user())->get();

                if (0 === $database->count() && $database = Database::byActiveAndUser(!$active, static::user())->get()) {
                    $databasesUsersAccess            = $database->first()->databasesUsersAccess->first();
                    $databasesUsersAccess->is_active = true;
                    $databasesUsersAccess->save();
                }

                if ($database->count() > 1) {
                    return $database;
                }

                if (0 === $database->count()) {
                    $database = false;
                }

                if (1 === $database->count()) {
                    $database = $database->first();
                }
            }

            if ($refresh || (isset($database) && !request()->database)) {
                static::setDatabase($database);
            }

            return $database ?? false;
        });

        Auth::macro('people', function (bool $active = true, bool $refresh = false) {
            $database = static::database(
                active: $active,
                refresh: $refresh
            );

            if (!$database) {
                return null;
            }

            return People::find(
                $database->databasesUsersAccess->first()?->base_people_id
            );
        });

        /*
         * Credentials
         */
        Auth::macro('is_master', fn () => static::check() ? static::user()->is_master : false);
    }
}
