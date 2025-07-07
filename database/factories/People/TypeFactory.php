<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Database\Factories\People;

use FalconERP\Skeleton\Enums\People\Type\TypesEnum;
use FalconERP\Skeleton\Models\Erp\People\Type;
use Illuminate\Database\Eloquent\Factories\Factory;

class TypeFactory extends Factory
{
    protected $model = Type::class;

    public function definition(): array
    {
        return [
            'description' => fake()->name(),
            'type'        => fake()->randomElement(TypesEnum::cases()),
            'created_at'  => now(),
            'updated_at'  => now(),
        ];
    }
}
