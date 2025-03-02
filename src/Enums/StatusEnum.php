<?php

namespace FalconERP\Skeleton\Enums;

use QuantumTecnology\EnumBasicsExtension\BaseEnum;

/**
 * StatusEnum class.
 *
 * @deprecated use RequestEnum instead
 */
abstract class StatusEnum extends BaseEnum
{
    public const OPEN       = 0;
    public const AUTHORIZED = 1;
    public const DENIED     = 2;
    public const FINISHED   = 3;
}
