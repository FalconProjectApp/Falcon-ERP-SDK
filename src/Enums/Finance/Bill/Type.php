<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Enums\Finance\Bill;

enum Type: string
{
    case Receive = 'receive'; // Receber
    case Pay     = 'pay';     // Pagar

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function implode(string $glue = ','): string
    {
        return implode($glue, self::values());
    }
}
