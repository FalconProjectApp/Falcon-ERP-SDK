<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Enums\Stock;

enum PositionSideEnum: string
{
    case EVEN = 'even';
    case ODD  = 'odd';

    public function label(): string
    {
        return match ($this) {
            self::EVEN => __('Even - Par'),
            self::ODD  => __('Odd - Ímpar'),
        };
    }
}
