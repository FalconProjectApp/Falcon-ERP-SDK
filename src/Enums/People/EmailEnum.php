<?php

namespace FalconERP\Skeleton\Enums\People;

use QuantumTecnology\EnumBasicsExtension\BaseEnum;

abstract class EmailEnum extends BaseEnum
{
    public const STATUS_PENDING   = 'pending';
    public const STATUS_REJECTED  = 'rejected';
    public const STATUS_BLOCKED   = 'blocked';
    public const STATUS_INVALID   = 'invalid';
    public const STATUS_ARCHIVED  = 'archived';
    public const STATUS_DRAFT     = 'draft';
    public const STATUS_SENT      = 'sent';
    public const STATUS_DELIVERED = 'delivered';
}
