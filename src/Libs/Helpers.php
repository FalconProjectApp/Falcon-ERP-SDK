<?php

/*
 * Helpers.
 */

use FalconERP\Skeleton\Models\BackOffice\DataBase\Database;
use FalconERP\Skeleton\Models\Erp\People\People;

if (!function_exists('people')) {
    function people(?People $people = null ): ?People {
        static $currentPeople = null;

        if ($people instanceof People) {
            $currentPeople = $people;
        }

        return $currentPeople;
    }
}

if (!function_exists('tenant')) {
    function tenant(?Database $tenant = null): ?Database
    {
        static $currentTenant = null;
        
        if ($tenant instanceof Database) {
            $currentTenant = $tenant;
        }

        return $currentTenant;
    }
}

if (!function_exists('rememberForever')) {
    function rememberForever(
        string $key,
        callable $callback,
    ) {
        return cache()->rememberForever(config('database.connections.tenant.database').'_'.$key, $callback);
    }
}

if (!function_exists('deleteCache')) {
    function deleteCache(
        array $keys,
    ): void {
        foreach ($keys as $key) {
            cache()->delete(config('database.connections.tenant.database').'_'.$key);
        }
    }
}
