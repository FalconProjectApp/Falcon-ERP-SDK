<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Database\Factories;

use FalconERP\Skeleton\Models\Erp\Stock\Stock;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class StockFactory extends Factory
{
    protected $model = Stock::class;

    public function definition(): array
    {
        return [
            'description'     => fake()->text(20),
            'balance_transit' => fake()->numberBetween(0, 10),
            'balance_stock'   => fake()->numberBetween(0, 10),
            'value'           => fake()->randomNumber(5),
            'observation'     => fake()->text(100),
            'created_at'      => Carbon::now(),
            'updated_at'      => Carbon::now(),
        ];
    }
}
