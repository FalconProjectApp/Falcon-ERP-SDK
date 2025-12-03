<?php

/*
 * Helpers.
 */

use FalconERP\Skeleton\Models\BackOffice\DataBase\Database;

if (!function_exists('people')) {
    function people(bool $active = true,
        bool $refresh = false,
    ): FalconERP\Skeleton\Models\Erp\People\People {
        return auth()->people($active, $refresh);
    }
}

if (!function_exists('setDatabase')) {
    function setDatabase(FalconERP\Skeleton\Models\BackOffice\DataBase\Database $database): void
    {
        auth()->setDatabase($database);
    }
}

if (!function_exists('database')) {
    function database(
        bool $active = true,
        bool $refresh = false,
    ): FalconERP\Skeleton\Models\BackOffice\DataBase\Database|false {
        return auth()->database($active, $refresh);
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
