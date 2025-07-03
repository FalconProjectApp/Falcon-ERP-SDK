<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Database\Seeders\Starter\People;

use FalconERP\Skeleton\Enums\People\PeopleTypeEnum;
use FalconERP\Skeleton\Models\Erp\People\Type;
use Illuminate\Database\Seeder;

class TypeSeeder extends Seeder
{
    protected $items = [
        [PeopleTypeEnum::TYPE_ADMIN],
        [PeopleTypeEnum::TYPE_FUNCIONARIO],
        [PeopleTypeEnum::TYPE_GERENTE],
        [PeopleTypeEnum::TYPE_CLIENTE],
        [PeopleTypeEnum::TYPE_FORNECEDOR],
        [PeopleTypeEnum::TYPE_VENDEDOR],
        [PeopleTypeEnum::TYPE_TRANSPORTADORA],
        [PeopleTypeEnum::TYPE_CONTRATANTE],
        [PeopleTypeEnum::TYPE_CONTRATADO],
        [PeopleTypeEnum::TYPE_CEO],
        [PeopleTypeEnum::TYPE_COLABORADOR],
    ];

    public function run(): void
    {
        collect($this->items)->each(function ($item) {
            $itemExists = Type::query()
                ->where('description', $item[0]->value)
                ->exists();

            if ($itemExists) {
                return;
            }

            Type::query()->insert([
                'description' => $item[0],
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        });
    }
}
