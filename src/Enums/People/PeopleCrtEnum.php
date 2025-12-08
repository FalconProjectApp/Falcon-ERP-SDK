<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Enums\People;

enum PeopleCrtEnum: int
{
    case REGIME_SIMPLES_NACIONAL                   = 1;
    case REGIME_SIMPLES_NACIONAL_EXCESSO_SUBLIMITE = 2;
    case REGIME_NORMAL                             = 3;
    case REGIME_MEI                                = 4;

    protected static function labels(): array
    {
        return [
            self::REGIME_SIMPLES_NACIONAL                   => 'Simples Nacional',
            self::REGIME_SIMPLES_NACIONAL_EXCESSO_SUBLIMITE => 'Simples Nacional - Excesso de Sublimite',
            self::REGIME_NORMAL                             => 'Normal',
            self::REGIME_MEI                                => 'MEI',
        ];
    }
}
