<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Enums\Stock;

enum MovementTypeEnum: string
{
    case IN         = 'in';
    case OUT        = 'out';
    case TRANSFER   = 'transfer';
    case INVENTORY  = 'inventory';
    case ADJUSTMENT = 'adjustment';

    public function label(): string
    {
        return match ($this) {
            self::IN         => __('Entry'),
            self::OUT        => __('Exit'),
            self::TRANSFER   => __('Transfer'),
            self::INVENTORY  => __('Inventory'),
            self::ADJUSTMENT => __('Adjustment'),
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::IN         => 'arrow-down',
            self::OUT        => 'arrow-up',
            self::TRANSFER   => 'arrows-left-right',
            self::INVENTORY  => 'clipboard-check',
            self::ADJUSTMENT => 'wrench',
        };
    }
}
