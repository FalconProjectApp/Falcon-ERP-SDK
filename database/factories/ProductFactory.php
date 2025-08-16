<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Database\Factories;

use FalconERP\Skeleton\Models\Erp\Stock\Group;
use FalconERP\Skeleton\Models\Erp\Stock\Product;
use FalconERP\Skeleton\Models\Erp\Stock\Traits\Request\ProductCollunsTrait;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    use ProductCollunsTrait;

    protected $model = Product::class;

    public function definition(): array
    {
        return [
            self::ATTRIBUTE_GROUPS_ID       => Group::factory(),
            self::ATTRIBUTE_STATUS          => fake()->boolean(90),
            self::ATTRIBUTE_DESCRIPTION     => fake()->sentence(),
            self::V_ATTRIBUTE_EAN           => fake()->unique()->ean13(),
            self::ATTRIBUTE_LAST_BUY_VALUE  => fake()->randomNumber(6, true),
            self::ATTRIBUTE_LAST_SELL_VALUE => fake()->randomNumber(6, true),
            self::ATTRIBUTE_LAST_RENT_VALUE => fake()->randomNumber(6, true),
            self::ATTRIBUTE_OBSERVATIONS    => fake()->text(200),
            'created_at'                    => now(),
            'updated_at'                    => now(),
        ];
    }
}
