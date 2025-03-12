<?php

namespace FalconERP\Skeleton\Providers;

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
        $this->publishes([
            __DIR__.'/../Config/falconservices.php' => config_path('falconservices.php'),
        ], 'config');
    }
}
