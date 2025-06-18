<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Database\Factories;

use FalconERP\Skeleton\Models\Erp\Stock\VolumeType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class VolumeTypeFactory extends Factory
{
    protected $model = VolumeType::class;

    public function definition(): array
    {
        return [
            'description' => $this->faker->word(),
            'initials'    => $this->faker->word(),
            'created_at'  => Carbon::now(),
            'updated_at'  => Carbon::now(),
        ];
    }
}
