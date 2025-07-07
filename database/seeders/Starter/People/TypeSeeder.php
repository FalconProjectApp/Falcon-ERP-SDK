<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Database\Seeders\Starter\People;

use FalconERP\Skeleton\Enums\People\PeopleTypeEnum;
use FalconERP\Skeleton\Enums\People\Type\TypesEnum;
use FalconERP\Skeleton\Models\Erp\People\Type;
use Illuminate\Database\Seeder;

class TypeSeeder extends Seeder
{
    protected $items = [
        [PeopleTypeEnum::TYPE_ADMIN, TypesEnum::TYPE_INDIVIDUAL],
        [PeopleTypeEnum::TYPE_FUNCIONARIO, TypesEnum::TYPE_INDIVIDUAL],
        [PeopleTypeEnum::TYPE_GERENTE, TypesEnum::TYPE_INDIVIDUAL],
        [PeopleTypeEnum::TYPE_CLIENTE, TypesEnum::TYPE_INDIVIDUAL],
        [PeopleTypeEnum::TYPE_FORNECEDOR, TypesEnum::TYPE_COMPANY],
        [PeopleTypeEnum::TYPE_VENDEDOR, TypesEnum::TYPE_INDIVIDUAL],
        [PeopleTypeEnum::TYPE_TRANSPORTADORA, TypesEnum::TYPE_COMPANY],
        [PeopleTypeEnum::TYPE_CONTRATANTE, TypesEnum::TYPE_COMPANY],
        [PeopleTypeEnum::TYPE_CONTRATADO, TypesEnum::TYPE_COMPANY],
        [PeopleTypeEnum::TYPE_CEO, TypesEnum::TYPE_INDIVIDUAL],
        [PeopleTypeEnum::TYPE_COLABORADOR, TypesEnum::TYPE_INDIVIDUAL],
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
                'type'        => $item[1],
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        });
    }
}
