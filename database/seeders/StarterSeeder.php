<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeders\Starter\Stock\RequestTypeSeeder;
use Database\Seeders\Starter\Stock\VolumeTypeSeeder;
use Illuminate\Database\Seeder;

class StarterSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            VolumeTypeSeeder::class,
            RequestTypeSeeder::class,
        ]);
    }
}
