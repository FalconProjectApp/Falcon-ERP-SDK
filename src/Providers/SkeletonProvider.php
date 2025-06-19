<?php

namespace FalconERP\Skeleton\Providers;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class SkeletonProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
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
            $this->loadMigrationsFrom(realpath(__DIR__.'/../../database/migrations/stock'));
           // $this->loadFactoriesFrom(realpath(__DIR__.'/../../database/factories/stock'));
        }

        $this->publishes([
            realpath(__DIR__.'/../Config/falconservices.php') => config_path('falconservices.php'),
        ], 'config');

        $this->publishes([
            realpath(__DIR__.'/../../database/seeders') => database_path('seeders'),
        ], 'seeders');
    }
}
