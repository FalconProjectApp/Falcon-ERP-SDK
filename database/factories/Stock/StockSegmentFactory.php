<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Database\Factories\Stock;

use Illuminate\Support\Carbon;
use FalconERP\Skeleton\Models\Erp\Stock\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use FalconERP\Skeleton\Models\Erp\Stock\StockSegment;
use FalconERP\Skeleton\Models\Erp\Stock\ProductSegment;
use FalconERP\Skeleton\Models\Erp\Stock\Stock;

class StockSegmentFactory extends Factory
{
    protected $model = StockSegment::class;

    public function definition(): array
    {
        return [
            'stock_id' => Stock::factory(),
            'name'       => fake()->randomElement(['Segment A', 'Segment B', 'Segment C']),
            'value'      => fake()->randomNumber(6, true),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
