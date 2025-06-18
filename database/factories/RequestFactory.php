<?php

declare(strict_types = 1);

namespace Database\Factories\FalconERP\Skeleton;

use FalconERP\Skeleton\Models\Erp\Stock\Request;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class RequestFactory extends Factory
{
    protected $model = Request::class;

    public function definition(): array
    {
        return [
            'description' => $this->faker->sentence,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
