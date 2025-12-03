<?php

declare(strict_types = 1);

namespace App\Models;

use App\Facades\Keycloak;
use App\Support\Crypt\Decoder;
use Crypt;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * @property array $token
 */
class Tenant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'key',
        'data',
        'created_at',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function connect(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        $databaseConfig  = config('database.connections.tenant');
        $serviceKeycloak = config('services.keycloak');

        DB::purge('tenant');

        $secrets = $this->data_secrets;

        $username = when($this->tenancy_db_username, fn () => $this->tenancy_db_username, $databaseConfig['username']);
        $password = when(
            $secrets['database']['password'] ?? null,
            fn () => $secrets['database']['password'],
            $databaseConfig['password']
        );

        $schema = when($this->tenancy_db_search_path, fn () => $this->tenancy_db_search_path, $databaseConfig['search_path']);

        $keycloakRealm = config('services.keycloak.suffix') . '-' . $this->key . '-' . config('app.env');

        $keycloakSecret = when(
            $secrets['keycloak']['secret'] ?? null,
            fn () => $secrets['keycloak']['secret'],
            $serviceKeycloak['client_secret']
        );

        DB::purge('tenant');
        Config::set([
            'database.default'            => 'tenant',
            'database.connections.tenant' => array_merge($databaseConfig, [
                'username'    => $username,
                'password'    => $password,
                'search_path' => $schema,
            ]),
        ]);

        DB::reconnect('tenant');
        Schema::connection('tenant')->getConnection()->reconnect();
        Keycloak::connect($keycloakRealm, $keycloakSecret);

        if (in_array(config('cache.default'), ['redis', 'database'])) {
            /* @phpstan-ignore-next-line */
            Cache::setPrefix($this->id . ':');
        }
    }

    public function disconnect(): void
    {
        $databaseConnection = config('database.default_bkp');

        DB::purge($databaseConnection);
        Config::set([
            'database.default' => $databaseConnection,
        ]);

        DB::reconnect($databaseConnection);
        Schema::connection($databaseConnection)->getConnection()->reconnect();
    }

    public function tenancyDbUsername(): Attribute
    {
        return Attribute::get(fn () => $this->data['database']['username'] ?? null);
    }

    public function dataSecrets(): Attribute
    {
        return Attribute::get(fn () => when(
            filled($this->data),
            fn (): array => new Decoder()->handle($this->key, $this->created_at->format('Ymd'), $this->data['secrets'])
        ));
    }

    public function tenancyDbSearchPath(): Attribute
    {
        return Attribute::get(fn () => $this->data['database']['search_path'] ?? null);
    }

    public function keycloakClientSecret(): Attribute
    {
        return Attribute::get(function () {
            $value = $this->data['keycloak_client_secret'] ?? null;

            return null !== $value ? Crypt::decrypt($value) : null;
        });
    }
}
