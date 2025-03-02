<?php

namespace FalconERP\Skeleton\Enums\Finance;

use QuantumTecnology\EnumBasicsExtension\BaseEnum;

abstract class BillToPayEnum extends BaseEnum
{
    public const STATUS_LAUNCHED   = 'launched';
    public const STATUS_DOWNLOADED = 'downloaded';
    public const STATUS_REVERSED   = 'reversed';
}
