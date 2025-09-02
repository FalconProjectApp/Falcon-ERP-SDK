<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Providers;

use Carbon\Carbon;
use FalconERP\Skeleton\Models\BackOffice\Shop;
use Illuminate\Http\Response;
use Illuminate\Support\ServiceProvider;
use QuantumTecnology\HandlerBasicsExtension\Traits\ApiResponseTrait;

class DatabaseServiceProvider extends ServiceProvider
{
    use ApiResponseTrait;

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): bool
    {
        $isAuth = match (true) {
            $this->routeIs('/erp/private/')  => $this->private(),
            $this->routeIs('/user/private/') => $this->private(),
            $this->routeIs('/user/public/')  => $this->public(),
            $this->routeIs('/erp/public/')   => true,
            $this->routeIs('/backoffice/')   => $this->private(),
            $this->routeIs('/telescope/')    => false,
            default                          => true,
        };

        abort_if(
            false === $isAuth && 'OPTIONS' !== request()->server->get('REQUEST_METHOD'),
            Response::HTTP_UNAUTHORIZED,
            __('Não autorizado!')
        );

        return true;
    }

    private function public(): bool
    {
        abort_if(
            !request()->has('shop'),
            Response::HTTP_BAD_REQUEST,
            __('Shop não informado!')
        );

        abort_if(
            request()->has('shop') && is_null(request()->shop),
            Response::HTTP_BAD_REQUEST,
            __('Shop não informado!')
        );

        $shop = Shop::query()
            ->where('slug', request()->shop)
            ->first();

        abort_if(
            null === $shop,
            Response::HTTP_NOT_FOUND,
            __('Loja não encontrada!')
        );

        $shop->update([
            'searched'         => $shop->searched + 1,
            'last_searched_at' => Carbon::now(),
        ]);

        auth()->setDatabase($shop->databases);

        return true;
    }

    private function private()
    {
        if (!auth()->check()) {
            return false;
        }

        return auth()->database();
    }

    private function routeIs(string $route): bool
    {
        $requestUri = request()->server->get('REQUEST_URI');

        return str_starts_with((string) $requestUri, $route);
    }
}
