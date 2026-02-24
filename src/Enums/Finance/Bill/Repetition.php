<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Enums\Finance\Bill;

enum Repetition: string
{
    case NotRecurrent = 'not_recurrent'; // Avulso — evento único, sem repetição (ex: pagamento pontual)
    case Fixed        = 'fixed';         // Parcelado — todas as parcelas pré-definidas de uma vez (ex: financiamento 60x)
    case Recurrent    = 'recurrent';     // Recorrente — nova parcela gerada automaticamente a cada período, sem fim definido (ex: conta de luz)

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function implode(string $glue = ','): string
    {
        return implode($glue, self::values());
    }
}
