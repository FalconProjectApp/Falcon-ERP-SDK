<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Database\Factories;

use FalconERP\Skeleton\Models\Erp\Stock\Item;
use FalconERP\Skeleton\Models\Erp\Stock\Request;
use FalconERP\Skeleton\Models\Erp\Stock\Stock;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'request_id' => Request::factory(),
            'stock_id'   => Stock::factory(),
            'value'      => fake()->randomNumber(6, true),
            'discount'   => fake()->randomNumber(6, true),
            'amount'     => fake()->randomNumber(6, true),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
