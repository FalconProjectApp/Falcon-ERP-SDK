<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Database\Factories;

use FalconERP\Skeleton\Models\Erp\Stock\VolumeType;
use Illuminate\Database\Eloquent\Factories\Factory;

class VolumeTypeFactory extends Factory
{
    protected $model = VolumeType::class;

    public function definition(): array
    {
        return [
            'description' => fake()->word(),
            'initials'    => fake()->word(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ];
    }
}
