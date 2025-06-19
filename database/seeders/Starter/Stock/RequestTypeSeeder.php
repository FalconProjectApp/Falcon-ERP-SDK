<?php

declare(strict_types=1);

namespace Database\Seeders\Starter\Stock;

use FalconERP\Skeleton\Enums\RequestEnum;
use FalconERP\Skeleton\Models\Erp\Stock\RequestType;
use Illuminate\Database\Seeder;

class RequestTypeSeeder extends Seeder
{
    protected $items = [
        [RequestEnum::REQUEST_DESCRIPTION_SIMPLE_ENTRY, RequestEnum::REQUEST_TYPE_INPUT, RequestEnum::TYPE_SYSTEM, true],
        [RequestEnum::REQUEST_DESCRIPTION_SIMPLE_EXIT, RequestEnum::REQUEST_TYPE_OUTPUT, RequestEnum::TYPE_SYSTEM, true],
        [RequestEnum::REQUEST_DESCRIPTION_STOCK_CONVERSION, RequestEnum::REQUEST_TYPE_SPECIAL, RequestEnum::TYPE_SYSTEM, true],
        [RequestEnum::REQUEST_DESCRIPTION_STOCK_PRODUCTION, RequestEnum::REQUEST_TYPE_SPECIAL, RequestEnum::TYPE_SYSTEM, true],
        [RequestEnum::REQUEST_DESCRIPTION_STOCK_TRANSFER, RequestEnum::REQUEST_TYPE_SPECIAL, RequestEnum::TYPE_SYSTEM, true],
        [RequestEnum::REQUEST_DESCRIPTION_STOCK_INVENTORY, RequestEnum::REQUEST_TYPE_SPECIAL, RequestEnum::TYPE_SYSTEM, true],
        [RequestEnum::REQUEST_DESCRIPTION_STOCK_LOSS, RequestEnum::REQUEST_TYPE_OUTPUT, RequestEnum::TYPE_SYSTEM, true],
        [RequestEnum::REQUEST_DESCRIPTION_STOCK_FOUND, RequestEnum::REQUEST_TYPE_INPUT, RequestEnum::TYPE_SYSTEM, true],
        [RequestEnum::REQUEST_DESCRIPTION_STOCK_RETURN, RequestEnum::REQUEST_TYPE_INPUT, RequestEnum::TYPE_SYSTEM, true],
        [RequestEnum::REQUEST_DESCRIPTION_PURCHASE_ORDER, RequestEnum::REQUEST_TYPE_NEUTRAL, RequestEnum::TYPE_SYSTEM, true],
        [RequestEnum::REQUEST_DESCRIPTION_SALES_ORDER, RequestEnum::REQUEST_TYPE_NEUTRAL, RequestEnum::TYPE_SYSTEM, true],
    ];

    public function run(): void
    {
        collect($this->items)->each(function ($item) {
            $itemExists = RequestType::query()
                ->where('description', $item[0])
                ->where('request_type', $item[1])
                ->where('type', $item[2])
                ->exists();

            if ($itemExists) {
                return;
            }

            RequestType::query()->insert([
                'description'  => $item[0],
                'request_type' => $item[1],
                'type'         => $item[2],
                'active'       => $item[3],
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        });
    }
}
