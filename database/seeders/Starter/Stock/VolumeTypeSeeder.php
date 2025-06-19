<?php

declare(strict_types=1);

namespace Database\Seeders\Starter\Stock;

use FalconERP\Skeleton\Models\Erp\Stock\VolumeType;
use Illuminate\Database\Seeder;

class VolumeTypeSeeder extends Seeder
{
    protected $items = [
        [
            'id'          => 0,
            'description' => 'Caixa com 12',
            'initials'    => 'CX12',
            'created_at'  => now(),
            'updated_at'  => now(),
        ],
        [
            'id'          => 1,
            'description' => 'Caixa com 6',
            'initials'    => 'CX6',
            'created_at'  => now(),
            'updated_at'  => now(),
        ],
        [
            'id'          => 2,
            'description' => 'Unidade',
            'initials'    => 'UN',
            'created_at'  => now(),
            'updated_at'  => now(),
        ],
        [
            'id'          => 3,
            'description' => 'Litro',
            'initials'    => 'LT',
            'created_at'  => now(),
            'updated_at'  => now(),
        ],
    ];

    public function run(): void
    {
        VolumeType::insert($this->items);
    }
}
