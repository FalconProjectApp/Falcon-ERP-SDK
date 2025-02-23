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
            __DIR__.'/../config/hashids.php',
            'hashids'
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
    }
}
