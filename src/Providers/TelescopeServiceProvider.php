<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Providers;

use Dom\Text;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     */
    #[\Override]
    public function register(): void
    {
        Telescope::night();

        Telescope::tag(function ($entry) {
            return ['env:'.Str::slug(config('app.env'))];
        });

        Telescope::tag(function ($entry) {
            return ['debug:'.Str::slug(config('app.debug'))];
        });

        Telescope::tag(function ($entry) {
            return ['service:'.Str::slug(config('app.name'))];
        });

        Telescope::tag(function ($entry) {
            return ['tenant:'.Str::slug(tenant()->base ?? 'no-tenant')];
        });

        $this->hideSensitiveRequestDetails();

        Telescope::filter(function (IncomingEntry $entry): bool {
            if (in_array(config('app.env'), explode(',', (string) config('telescope.enabled_env')))) {
                return true;
            }

            if ($entry->isReportableException()) {
                return true;
            }

            if ($entry->isFailedRequest()) {
                return true;
            }

            if ($entry->isFailedJob()) {
                return true;
            }

            if ($entry->isScheduledTask()) {
                return true;
            }

            return $entry->hasMonitoredTag();
        });
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     */
    protected function hideSensitiveRequestDetails(): void
    {
        if ($this->app->environment('local')) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    #[\Override]
    protected function gate(): void
    {
        Gate::define('viewTelescope', fn ($user): bool => in_array($user->email, [
        ]));
    }

    #[\Override]
    protected function authorization(): void
    {
        $this->gate();

        Telescope::auth(fn ($request): bool => in_array(config('app.env'), explode(',', (string) config('telescope.enabled_env')))
            || Gate::check('viewTelescope', [$request->user()]));
    }
}
