<?php

/*
 * Helpers.
 */

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

if (!function_exists('rememberForever')) {
    function rememberForever(
        string $key,
        callable $callback,
    ) {
        return cache()->rememberForever(config('database.connections.pgsql_bases.database').'_'.$key, $callback);
    }
}
