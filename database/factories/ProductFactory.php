<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Database\Factories;

use FalconERP\Skeleton\Models\Erp\Stock\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'description'   => $this->faker->sentence(),
            'bar_code'      => $this->faker->unique()->ean13(),
            'provider_code' => $this->faker->unique()->ean13(),
            'created_at'    => Carbon::now(),
            'updated_at'    => Carbon::now(),
        ];
    }
}
