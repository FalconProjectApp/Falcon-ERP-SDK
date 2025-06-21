<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Database\Factories\People;

use FalconERP\Skeleton\Models\Erp\People\People;
use FalconERP\Skeleton\Models\Erp\People\Type;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PersonFactory extends Factory
{
    protected $model = People::class;

    public function definition(): array
    {
        $name = fake()->name();

        return [
            'name'         => $name,
            'type_id'      => Type::factory(),
            'display_name' => Str::slug($name),
            'is_public'    => fake()->boolean(50),
            'about'        => fake()->paragraph(),
            'created_at'   => now(),
            'updated_at'   => now(),
        ];
    }
}
