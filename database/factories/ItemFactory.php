<?php

declare(strict_types = 1);

namespace Database\Factories\FalconERP\Skeleton;

use FalconERP\Skeleton\Models\Erp\Stock\Item;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
