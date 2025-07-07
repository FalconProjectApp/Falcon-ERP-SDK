<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Enums\People\Type;

enum TypesEnum: string
{
    case TYPE_INDIVIDUAL  = 'individual';
    case TYPE_COMPANY     = 'company';
    case TYPE_GOVERNMENT  = 'government';
    case TYPE_NON_PROFIT  = 'non_profit';
    case TYPE_PARTNERSHIP = 'partnership';
}
