<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\Starter\MenuSeeder;
use Database\Seeders\Starter\ParameterSeeder;
use Database\Seeders\Starter\VolumeTypeSeeder;
use Database\Seeders\Starter\RequestTypeSeeder;

class StarterSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            VolumeTypeSeeder::class,
            RequestTypeSeeder::class,
            // MenuSeeder::class,
            // ParameterSeeder::class
        ]);
    }
}
