<?php

namespace FalconERP\Skeleton\Enums\Service;

use QuantumTecnology\EnumBasicsExtension\BaseEnum;

abstract class OrderEnum extends BaseEnum
{
    public const STATUS_OPEN        = 'open';
    public const STATUS_PAUSE       = 'pause';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_CLOSEDS     = 'closed';
}
