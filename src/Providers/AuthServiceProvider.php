<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Providers;

use FalconERP\Skeleton\Models\User;
use Illuminate\Auth\RequestGuard;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

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
        * Kong Guard - Registrar o driver customizado
        */
        Auth::extend('kong', function ($app, $name, array $config) {
            $provider = Auth::createUserProvider($config['provider'] ?? null);

            return new RequestGuard(function ($request) {
                // A autenticação Kong passa os dados via headers
                if ($request->hasHeader('x-user-id')) {
                    $userId = (int) $request->header('x-user-id');

                    return User::find($userId);
                }

                return null;
            }, $app['request'], $provider);
        });

        /*
         * Credentials
         */
        Auth::macro('is_master', fn () => static::check() ? static::user()->is_master : false);
    }
}
