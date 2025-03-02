<?php

namespace FalconERP\Skeleton\Enums\Shop;

use QuantumTecnology\EnumBasicsExtension\BaseEnum;

abstract class ShopEnum extends BaseEnum
{
    public const STATUS_OPEN    = 'open';
    public const STATUS_CLOSEDS = 'closed';

    public const TYPES_SERVICE   = 'service';
    public const TYPES_MENU      = 'menu';
    public const TYPES_ECCOMERCE = 'eccomerce';
    public const TYPES_IFOOD     = 'ifood';
}
