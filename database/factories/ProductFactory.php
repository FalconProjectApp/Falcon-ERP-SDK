<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Database\Factories;

use FalconERP\Skeleton\Models\Erp\Stock\Group;
use FalconERP\Skeleton\Models\Erp\Stock\Product;
use FalconERP\Skeleton\Models\Erp\Stock\VolumeType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'group_id'        => Group::factory()->create(),
            'volume_type_id'  => VolumeType::factory()->create(),
            'status'          => fake()->randomBoolean(),
            'description'     => fake()->sentence(),
            'bar_code'        => fake()->unique()->ean13(),
            'last_buy_value'  => fake()->randomNumber(6, true),
            'last_sell_value' => fake()->randomNumber(6, true),
            'last_rent_value' => fake()->randomNumber(6, true),
            'provider_code'   => fake()->unique()->ean13(),
            'observations'    => fake()->text(200),
            'created_at'      => now(),
            'updated_at'      => now(),
        ];
    }
}
