<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Database\Factories;

use FalconERP\Skeleton\Models\Erp\Stock\Product;
use FalconERP\Skeleton\Models\Erp\Stock\Stock;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockFactory extends Factory
{
    protected $model = Stock::class;

    public function definition(): array
    {
        return [
            'product_id'      => Product::factory(),
            'color'           => fake()->colorName(),
            'on_shop'         => fake()->boolean(90),
            'measure'         => fake()->randomElement(['kg', 'g', 'l', 'ml', 'un']),
            'width'           => fake()->randomNumber(3),
            'weight'          => fake()->randomNumber(3),
            'height'          => fake()->randomNumber(3),
            'depth'           => fake()->randomNumber(3),
            'description'     => fake()->text(20),
            'balance_transit' => fake()->numberBetween(0, 10),
            'balance_stock'   => fake()->numberBetween(0, 10),
            'value'           => fake()->randomNumber(5),
            'observation'     => fake()->text(100),
            'status'          => fake()->boolean(90),
            'created_at'      => now(),
            'updated_at'      => now(),
        ];
    }
}
