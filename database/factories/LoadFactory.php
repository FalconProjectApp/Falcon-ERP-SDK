<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Database\Factories;

use FalconERP\Skeleton\Enums\RequestEnum;
use FalconERP\Skeleton\Models\Erp\People\People;
use FalconERP\Skeleton\Models\Erp\Stock\Load;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoadFactory extends Factory
{
    protected $model = Load::class;

    public function definition(): array
    {
        return [
            'driver_id'  => People::factory(),
            'status'     => fake()->randomElement(RequestEnum::statuses()->toArray()),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
