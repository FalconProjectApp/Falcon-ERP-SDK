<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Enums\Finance\Bill;

enum Periodicity: string
{
    case Daily   = 'daily';   // Diário
    case Weekly  = 'weekly';  // Semanal
    case Monthly = 'monthly'; // Mensal
    case Annual  = 'annual';  // Anual

    /**
     * Retorna a unidade de tempo para uso com Carbon::add().
     * Ex: Periodicity::Monthly->unit() === 'month'
     */
    public function unit(): string
    {
        return match ($this) {
            self::Daily   => 'day',
            self::Weekly  => 'week',
            self::Monthly => 'month',
            self::Annual  => 'year',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function implode(string $glue = ','): string
    {
        return implode($glue, self::values());
    }
}
