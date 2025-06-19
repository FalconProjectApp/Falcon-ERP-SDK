<?php

declare(strict_types=1);

namespace Database\Seeders\Starter\Stock;

use FalconERP\Skeleton\Models\Erp\Stock\VolumeType;
use Illuminate\Database\Seeder;

class VolumeTypeSeeder extends Seeder
{
    protected $items = [
        ['Caixa com 12', 'CX12'],
        ['Caixa com 6', 'CX6'],
        ['Unidade', 'UN'],
        ['Litro', 'LT'],
    ];

    public function run(): void
    {
        collect($this->items)->each(function ($item) {
            $itemExists = VolumeType::query()
                ->where('description', $item[0])
                ->where('initials', $item[1])
                ->exists();

            if ($itemExists) {
                return;
            }

            VolumeType::query()->insert([
                'description' => $item[0],
                'initials'    => $item[1],
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        });
    }
}
