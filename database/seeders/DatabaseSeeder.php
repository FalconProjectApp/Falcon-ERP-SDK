<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use FalconERP\Skeleton\Database\Seeders\Starter\People\TypeSeeder;
use FalconERP\Skeleton\Database\Seeders\Starter\Stock\RequestTypeSeeder;
use FalconERP\Skeleton\Database\Seeders\Starter\Stock\VolumeTypeSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            TypeSeeder::class,
            VolumeTypeSeeder::class,
            RequestTypeSeeder::class,
        ]);
    }
}
