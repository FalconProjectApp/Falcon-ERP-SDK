<?php

namespace FalconERP\Skeleton\Observers;

use FalconERP\Skeleton\Enums\CacheEnum;
use FalconERP\Skeleton\Models\Erp\People\People;
use Illuminate\Database\Eloquent\Model;

class CacheObserver
{
    public function deleted(Model $model)
    {
        match ((new \ReflectionClass($model))->getName()) {
            People::class => $this->people(),
            default       => null,
        };
    }

    public function restored(Model $model)
    {
        match ((new \ReflectionClass($model))->getName()) {
            People::class => $this->people(),
            default       => null,
        };
    }

    /**
     * Handle the User "created" event.
     */
    public function created(Model $model): void
    {
        match ((new \ReflectionClass($model))->getName()) {
            People::class => $this->people(),
            default       => null,
        };
    }

    private function deleteCache(array $keys): void
    {
        foreach ($keys as $key) {
            cache()->delete(config('database.connections.pgsql_bases.database').'_'.$key);
        }
    }

    private function people(): void
    {
        $this->deleteCache([
            CacheEnum::KEY_PEOPLE_TOTAL_COUNT,
            CacheEnum::KEY_PEOPLE_TRASHED_COUNT,
            CacheEnum::KEY_PEOPLE_IS_PUBLIC_COUNT,
            CacheEnum::KEY_PEOPLE_IS_PRIVATE_COUNT,
        ]);
    }
}
