<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Database\Factories;

use FalconERP\Skeleton\Models\Erp\Finance\PaymentMethod;
use FalconERP\Skeleton\Models\Erp\People\People;
use FalconERP\Skeleton\Models\Erp\Stock\Request;
use FalconERP\Skeleton\Models\Erp\Stock\RequestType;
use Illuminate\Database\Eloquent\Factories\Factory;

class RequestFactory extends Factory
{
    protected $model = Request::class;

    public function definition(): array
    {
        return [
            'description'       => fake()->sentence(),
            'request_type_id'   => RequestType::factory()->create(),
            'payment_method_id' => PaymentMethod::factory()->create(),
            'responsible_id'    => People::factory()->create(),
            'third_id'          => People::factory()->create(),
            'allower_id'        => People::factory()->create(),
            'discount_value'    => fake()->randomNumber(6, true),
            'freight_value'     => fake()->randomNumber(6, true),
            'observations'      => fake()->text(200),
            'created_at'        => now(),
            'updated_at'        => now(),
        ];
    }
}
