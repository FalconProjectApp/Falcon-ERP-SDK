<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Enums\Fiscal;

enum SerieEnvironmentEnum: string
{
    case ENVIRONMENT_PRODUCTION   = 'production';
    case ENVIRONMENT_HOMOLOGATION = 'homologation';

    public function tpAmb(): int
    {
        return match ($this) {
            self::ENVIRONMENT_PRODUCTION   => 1,
            self::ENVIRONMENT_HOMOLOGATION => 2,
        };
    }
}
