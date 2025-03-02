<?php

namespace FalconERP\Skeleton\Enums\People;

use QuantumTecnology\EnumBasicsExtension\BaseEnum;

abstract class PeopleEnum extends BaseEnum
{
    public const TYPE_ADMIN       = 0;
    public const TYPE_FUNCIONARIO = 1;
    public const TYPE_GERENTE     = 2;
}
