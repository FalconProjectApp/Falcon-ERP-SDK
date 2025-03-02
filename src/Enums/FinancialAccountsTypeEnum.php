<?php

namespace FalconERP\Skeleton\Enums;

use QuantumTecnology\EnumBasicsExtension\BaseEnum;
use Illuminate\Support\Collection;

abstract class FinancialAccountsTypeEnum extends BaseEnum
{
    public const SYSTEM_TYPE = 'system';
    public const CLIENT_TYPE = 'client';

    /**
     * Return available types.
     */
    public static function FinancialAccountsTypes(): Collection
    {
        return new Collection(static::filterConstants('FINANCIALACCOUNT_TYPE'));
    }
}
