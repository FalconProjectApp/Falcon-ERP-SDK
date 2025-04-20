<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Enums\Fiscal;

enum SerieDescriptionEnum: string
{
    case DESCRIPTION_NFE   = 'Nota Fiscal Eletrônica';
    case DESCRIPTION_NFCE  = 'Nota Fiscal Eletrônica de Consumidor';
    case DESCRIPTION_NFSE  = 'Nota Fiscal Eletrônica de Serviço';
    case DESCRIPTION_CTE   = 'Conhecimento de Transporte Eletrônico';
    case DESCRIPTION_CTEAN = 'Conhecimento de Transporte Eletrônico de Animais';
    case DESCRIPTION_CTEOS = 'Conhecimento de Transporte Eletrônico de Outros Serviços';
    case DESCRIPTION_CTEE  = 'Conhecimento de Transporte Eletrônico de Outros';
    case DESCRIPTION_NF3E  = 'Nota Fiscal Eletrônica de Energia Elétrica';

    public function model(): SerieModelEnum
    {
        return match ($this) {
            self::DESCRIPTION_NFE   => SerieModelEnum::MODEL_NFE,
            self::DESCRIPTION_NFCE  => SerieModelEnum::MODEL_NFCE,
            self::DESCRIPTION_NFSE  => SerieModelEnum::MODEL_NFSE,
            self::DESCRIPTION_CTE   => SerieModelEnum::MODEL_CTE,
            self::DESCRIPTION_CTEAN => SerieModelEnum::MODEL_CTEAN,
            self::DESCRIPTION_CTEOS => SerieModelEnum::MODEL_CTEOS,
            self::DESCRIPTION_CTEE  => SerieModelEnum::MODEL_CTEE,
            self::DESCRIPTION_NF3E  => SerieModelEnum::MODEL_NF3E,
        };
    }

    public function environmentDefault(): SerieEnvironmentEnum
    {
        return match ($this) {
            self::DESCRIPTION_NFE,
            self::DESCRIPTION_NFCE,
            self::DESCRIPTION_NFSE,
            self::DESCRIPTION_CTE,
            self::DESCRIPTION_CTEAN,
            self::DESCRIPTION_CTEOS,
            self::DESCRIPTION_CTEE,
            self::DESCRIPTION_NF3E => SerieEnvironmentEnum::ENVIRONMENT_HOMOLOGATION,
        };
    }

    public function initialSequenceNumber(): int
    {
        return match ($this) {
            self::DESCRIPTION_NFE,
            self::DESCRIPTION_NFCE,
            self::DESCRIPTION_NFSE,
            self::DESCRIPTION_CTE,
            self::DESCRIPTION_CTEAN,
            self::DESCRIPTION_CTEOS,
            self::DESCRIPTION_CTEE,
            self::DESCRIPTION_NF3E => 1,
        };
    }
}
