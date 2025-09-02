<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Enums\People\Type;

enum LegalEntityTypesEnum: string
{
    /**
     * Pessoa Física.
     */
    case INDIVIDUAL = 'individual';

    /**
     * Pessoa Jurídica.
     */
    case COMPANY = 'company';
}
