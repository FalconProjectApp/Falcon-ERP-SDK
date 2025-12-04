<?php

declare(strict_types = 1);

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

        /*
         * Credentials
         */
        Auth::macro('is_master', fn () => static::check() ? static::user()->is_master : false);
    }
}
