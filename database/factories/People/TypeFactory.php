<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Database\Factories\People;

use FalconERP\Skeleton\Models\Erp\People\Type;
use Illuminate\Database\Eloquent\Factories\Factory;

class TypeFactory extends Factory
{
    protected $model = Type::class;

    public function definition(): array
    {
        return [
            'description' => fake()->name(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ];
    }
}
