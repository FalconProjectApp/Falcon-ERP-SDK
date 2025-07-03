<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Database\Seeders;

use Database\Seeders\Starter\Stock\RequestTypeSeeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Database\Seeders\Starter\Stock\VolumeTypeSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            VolumeTypeSeeder::class,
            RequestTypeSeeder::class,
        ]);
    }
}
