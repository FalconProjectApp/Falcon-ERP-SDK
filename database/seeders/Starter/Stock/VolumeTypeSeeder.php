<?php

declare(strict_types = 1);

namespace Database\Seeders\Starter\Stock;

use FalconERP\Skeleton\Models\Erp\Stock\VolumeType;
use Illuminate\Database\Seeder;

class VolumeTypeSeeder extends Seeder
{
    protected $items = [
        ['description' => 'Caixa com 12', 'initials' => 'CX12'],
        ['description' => 'Caixa com 6', 'initials' => 'CX6'],
        ['description' => 'Unidade', 'initials' => 'UN'],
        ['description' => 'Litro', 'initials' => 'LT'],
    ];

    public function run(): void
    {
        collect($this->items)->each(function ($releaseType) {
            $releaseTypeExists = VolumeType::query()
                ->where('description', $releaseType[0])
                ->where('initials', $releaseType[1])
                ->exists();

            if ($releaseTypeExists) {
                return;
            }

            VolumeType::query()->insert([
                'description' => $releaseType[0],
                'initials'    => $releaseType[1],
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        });
    }
}
