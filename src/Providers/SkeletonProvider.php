<?php

namespace FalconERP\Skeleton\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use FalconERP\Skeleton\Providers\AuthServiceProvider;

class SkeletonProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        /**
         * TODO: Ele esta sendo executado antes de tudo, e acaba dando erro, pois o sanctum nao foi iniciado
         * precisa ver uma forma de registrar o AuthServiceProvider depois que o Sanctum for iniciado
         */
        //app()->register(AuthServiceProvider::class);

        /* Factory::guessFactoryNamesUsing(function (string $modelName) {
            return '\\JobMetric\\Setting\\Factories\\'.class_basename($modelName).'Factory';
        }); */

        $this->mergeConfigFrom(
            __DIR__.'/../Config/hashids.php',
            'hashids'
        );

        $this->mergeConfigFrom(
            __DIR__.'/../Config/perpage.php',
            'perpage'
        );

        $this->mergeConfigFrom(
            __DIR__.'/../Config/falconservices.php',
            'falconservices'
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        if (app()->environment(['falcon_testing'])) {
            $this->loadMigrationsFrom(realpath(__DIR__.'/../../database/migrations/finance'));
            $this->loadMigrationsFrom(realpath(__DIR__.'/../../database/migrations/fiscal'));
            $this->loadMigrationsFrom(realpath(__DIR__.'/../../database/migrations/people'));
            $this->loadMigrationsFrom(realpath(__DIR__.'/../../database/migrations/service'));
            $this->loadMigrationsFrom(realpath(__DIR__.'/../../database/migrations/shop'));
            $this->loadMigrationsFrom(realpath(__DIR__.'/../../database/migrations/stock'));
            $this->loadMigrationsFrom(realpath(__DIR__.'/../../database/migrations'));
        }

        $this->publishes([
            realpath(__DIR__.'/../Config/falconservices.php') => config_path('falconservices.php'),
        ], 'config');

        $this->publishes([
            realpath(__DIR__.'/../../database/seeders') => database_path('seeders'),
        ], 'seeders');

        // Registrar comandos do console
        if ($this->app->runningInConsole()) {
            $this->commands([
                \FalconERP\Skeleton\Console\Commands\TenantMigrate::class,
                \FalconERP\Skeleton\Console\Commands\TenantRollback::class,
                \FalconERP\Skeleton\Console\Commands\TenantStatus::class,
                \FalconERP\Skeleton\Console\Commands\CheckMigrations::class,
            ]);
        }
    }
}
