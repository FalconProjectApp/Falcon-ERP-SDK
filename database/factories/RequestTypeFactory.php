<?php

declare(strict_types = 1);

namespace Database\Factories\FalconERP\Skeleton;

use FalconERP\Skeleton\Models\Erp\Stock\RequestType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class RequestTypeFactory extends Factory
{
    protected $model = RequestType::class;

    public function definition(): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
