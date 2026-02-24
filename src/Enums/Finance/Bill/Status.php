<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Enums\Finance\Bill;

enum Status: string
{
    case Open         = 'open';          // Em aberto
    case Paid         = 'paid';          // Pago
    case PaidPartial  = 'paid_partial';  // Pago parcialmente
    case Canceled     = 'canceled';      // Cancelado
    case InRecurrence = 'in_recurrence'; // Em recorrência (todas as parcelas pagas, mas novas serão geradas)

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function implode(string $glue = ','): string
    {
        return implode($glue, self::values());
    }
}
