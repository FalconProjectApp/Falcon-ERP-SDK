<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Enums\Fiscal;

enum NatureOperationTypeEnum: string
{
    case TYPE_INDUSTRIALIZATION = 'industrialization';
    case TYPE_COMMERCIALIZATION = 'commercialization';
    case TYPE_RETURN            = 'return';
    case TYPE_TRANSFER          = 'transfer';
    case TYPE_OTHER             = 'other';

    public function operationType(): string
    {
        return match ($this) {
            self::TYPE_INDUSTRIALIZATION => '102',
            self::TYPE_COMMERCIALIZATION => '102',
            self::TYPE_RETURN            => '201',
            self::TYPE_TRANSFER          => '401',
            default                      => '000',
        };
    }
}
