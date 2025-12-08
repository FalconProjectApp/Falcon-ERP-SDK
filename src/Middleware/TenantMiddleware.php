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
use RuntimeException;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (tenant() instanceof Database) {
            tenant()->disconnect();
        }

        $tenant = false;

        if ($request->hasHeader('authorization') && !blank($request->header('authorization')) && !in_array($request->header('authorization'), ['Bearer ', 'Bearer null'])) {
            $user   = $this->getUser($request->header('authorization'));
            $tenant = $user->databasesAccess()->where('is_active', true)->sole();
        }

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

        if (!$tenant) {
            return $next($request);
        }

        tenant($tenant);

        $isLoggedIn = auth()->check();

        $tenant->connect();
        Log::info('Tenant connected: ' . $tenant->id);
        Log::debug('Tenant details: ', $tenant->toArray());

        if ($isLoggedIn) {
            Log::debug('User ' . auth()->id() . ' connected to tenant: ' . $tenant->id);

            people($this->getPeople());
        }

        return $next($request);
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
