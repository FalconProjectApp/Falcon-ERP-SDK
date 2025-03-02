<?php

namespace FalconERP\Skeleton\Enums;

use Illuminate\Support\Collection;
use QuantumTecnology\EnumBasicsExtension\BaseEnum;

abstract class ReleaseTypeEnum extends BaseEnum
{
    public const RELEASE_TYPE_INPUT  = 'input';
    public const RELEASE_TYPE_OUTPUT = 'output';

    public const TYPE_SYSTEM = 'system';
    public const TYPE_CLIENT = 'client';

    public const RELEASE_DESCRIPTION_PAYMENT = 'Pagamento de Conta';
    public const RELEASE_DESCRIPTION_RECEIPT = 'Recebimento de Conta';

    /**
     * Return available types.
     */
    public static function releaseTypes(): Collection
    {
        return new Collection(static::filterConstants('RELEASE_TYPE'));
    }
}
