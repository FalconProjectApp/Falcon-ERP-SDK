<?php

declare(strict_types = 1);

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
}
