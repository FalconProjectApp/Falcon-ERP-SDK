<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Enums\Fiscal;

enum SerieEnvironmentEnum: string
{
    case ENVIRONMENT_PRODUCTION   = '1';
    case ENVIRONMENT_HOMOLOGATION = '2';

    public function getDescription(): string
    {
        return match ($this) {
            self::ENVIRONMENT_PRODUCTION   => 'Production',
            self::ENVIRONMENT_HOMOLOGATION => 'Homologation',
        };
    }

    public function tpAmb(): string
    {
        return match ($this) {
            self::ENVIRONMENT_PRODUCTION   => '1',
            self::ENVIRONMENT_HOMOLOGATION => '2',
        };
    }
}
