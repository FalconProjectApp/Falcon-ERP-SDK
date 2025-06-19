<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Database\Factories;

use FalconERP\Skeleton\Enums\RequestEnum;
use FalconERP\Skeleton\Models\Erp\Stock\RequestType;
use Illuminate\Database\Eloquent\Factories\Factory;

class RequestTypeFactory extends Factory
{
    protected $model = RequestType::class;

    public function definition(): array
    {
        return [
            'description'  => fake()->sentence(),
            'request_type' => fake()->randomElement(RequestEnum::requestTypes()->toArray()),
            'type'         => fake()->randomElement(RequestEnum::types()->toArray()),
            'is_active'    => fake()->boolean(90),
            'created_at'   => now(),
            'updated_at'   => now(),
        ];
    }
}
