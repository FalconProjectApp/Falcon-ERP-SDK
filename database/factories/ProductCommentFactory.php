<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Database\Factories;

use FalconERP\Skeleton\Models\Erp\People\People;
use FalconERP\Skeleton\Models\Erp\Stock\Product;
use FalconERP\Skeleton\Models\Erp\Stock\ProductComment;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductCommentFactory extends Factory
{
    protected $model = ProductComment::class;

    public function definition(): array
    {
        return [
            'product_id'         => Product::factory(),
            'product_comment_id' => fake()->boolean(30)
                ? ProductComment::factory()
                : null,
            'people_id' => fake()->boolean(30)
                ? People::factory()
                : null,
            'comment'    => fake()->text(200),
            'origin'     => fake()->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
