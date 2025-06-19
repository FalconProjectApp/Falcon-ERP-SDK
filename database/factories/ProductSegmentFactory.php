<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Database\Factories;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use FalconERP\Skeleton\Models\Erp\Stock\ProductSegment;

class ProductSegmentFactory extends Factory
{
    protected $model = ProductSegment::class;

    public function definition(): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
