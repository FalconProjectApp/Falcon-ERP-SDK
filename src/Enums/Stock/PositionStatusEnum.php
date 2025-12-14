<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Enums\Stock;

enum PositionStatusEnum: string
{
    case AVAILABLE   = 'available';
    case BLOCKED     = 'blocked';
    case MAINTENANCE = 'maintenance';
    case FULL        = 'full';

    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE   => __('Available'),
            self::BLOCKED     => __('Blocked'),
            self::MAINTENANCE => __('Maintenance'),
            self::FULL        => __('Full'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::AVAILABLE   => 'success',
            self::BLOCKED     => 'danger',
            self::MAINTENANCE => 'warning',
            self::FULL        => 'info',
        };
    }
}
