<?php

declare(strict_types = 1);

namespace Database\Factories\FalconERP\Skeleton;

use FalconERP\Skeleton\Models\Erp\Stock\ProductComment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ProductCommentFactory extends Factory
{
    protected $model = ProductComment::class;

    public function definition(): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
