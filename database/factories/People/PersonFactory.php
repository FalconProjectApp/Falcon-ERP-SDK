<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Database\Factories;

use FalconERP\Skeleton\Models\Erp\People\People;
use FalconERP\Skeleton\Models\Erp\Stock\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

class PersonFactory extends Factory
{
    protected $model = People::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ];
    }
}
