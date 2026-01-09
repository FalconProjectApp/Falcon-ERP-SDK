<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Middleware;

use Carbon\Carbon;
use Closure;
use FalconERP\Skeleton\Models\BackOffice\DataBase\Database;
use FalconERP\Skeleton\Models\BackOffice\Shop;
use FalconERP\Skeleton\Models\Erp\People\People;
use FalconERP\Skeleton\Models\PersonalAccessToken;
use FalconERP\Skeleton\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use QuantumTecnology\ValidateTrait\Data;
use RuntimeException;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (tenant() instanceof Database) {
            tenant()->disconnect();
        }

        $data = match (config('auth.defaults.guard')) {
            'sanctum'  => $this->sanctumAuth($request),
            'keycloak' => $this->keycloakAuth($request),
            'kong'     => $this->kongAuth($request),
            default    => Log::warning('Using an unrecognized authentication guard for tenant identification.'),
        };

        if ($request->hasHeader('x-shop-name') && !blank($request->header('x-shop-name'))) {
            $shopName = $request->header('x-shop-name');

            $shop = Shop::query()
                ->where('slug', $shopName)
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

            $tenant = $shop->databases;
        }

        if (!$data->tenant) {
            return $next($request);
        }

        tenant($data->tenant);

        $isLoggedIn = auth()->check();
        $data->tenant->connect();
        Log::info('Tenant connected: ' . $data->tenant->id);
        Log::debug('Tenant details: ', $data->tenant->toArray());

        if ($isLoggedIn) {
            Log::debug('User ' . auth()->id() . ' connected to tenant: ' . $data->tenant->id);

            people($this->getPeople());
        }

        return $next($request);
    }

    private function keycloakAuth(Request $request): ?string
    {
        Log::info('Using Keycloak guard for tenant identification.');

        abort_if(
            !$request->hasHeader('Authorization'),
            Response::HTTP_UNAUTHORIZED,
            __('Authorization header is missing.')
        );

        throw new RuntimeException('Keycloak tenant identification not implemented yet.');

        return null;
    }

    private function sanctumAuth(Request $request): Data
    {
        Log::info('Using Sanctum or API guard for tenant identification.');

        abort_if(
            !$request->hasHeader('Authorization')
            || blank($request->header('authorization'))
            || in_array($request->header('authorization'), ['Bearer ', 'Bearer null']),
            Response::HTTP_UNAUTHORIZED,
            __('Authorization header is missing.')
        );

        $user = $this->getUser($request->header('authorization'));

        return new Data([
            'user'   => $user,
            'tenant' => $user->databasesAccess()->where('is_active', true)->sole(),
        ]);
    }

    private function kongAuth(Request $request): Data
    {
        Log::info('Using Kong guard for tenant identification.');

        if ($request->hasHeader('x-tenant-name') && !blank($request->header('x-tenant-name'))) {
            $tenantName = $request->header('x-tenant-name');

            $tenant = Database::query()
                ->where('base', $tenantName)
                ->sole();

            abort_if(
                null === $tenant,
                Response::HTTP_NOT_FOUND,
                __('Database not found or inactive!')
            );
        }

        return new Data([
            'tenant' => $tenant ?? null,
        ]);
    }

    private function getUser(string $token): User
    {
        $tenantKey = str_replace('Bearer ', '', $token);
        $id        = explode('|', $tenantKey)[0] ?? '';

        return $id ? PersonalAccessToken::query()->where('id', $id)->firstOrFail()->tokenable : throw new InvalidArgumentException('Invalid token provided.');
    }

    private function getPeople(): People
    {
        $basePeopleId = auth()->user()->databasesAccess()->wherePivot('database_id', tenant()->id)->first()?->pivot->base_people_id;

        if (!$basePeopleId) {
            throw new RuntimeException('Base people ID not found for the current user.');
        }

        $people = People::find($basePeopleId);

        if (!$people) {
            throw new RuntimeException('People not found for the given base people ID.');
        }

        return $people;
    }
}
