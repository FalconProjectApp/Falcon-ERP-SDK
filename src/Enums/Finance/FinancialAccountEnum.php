<?php

namespace FalconERP\Skeleton\Enums\Finance;

use QuantumTecnology\EnumBasicsExtension\BaseEnum;

abstract class FinancialAccountEnum extends BaseEnum
{
    public const STATUS_OPENED = 'opened';
    public const STATUS_CLOSED = 'closed';

    public const TYPE_SYSTEM = 'system';
    public const TYPE_CLIENT = 'client';
}
