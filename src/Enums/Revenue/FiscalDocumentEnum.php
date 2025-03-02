<?php

namespace FalconERP\Skeleton\Enums\Revenue;

use QuantumTecnology\EnumBasicsExtension\BaseEnum;

abstract class FiscalDocumentEnum extends BaseEnum
{
    public const STATUS_IMPORTED = 'imported';
    public const STATUS_CANCELED = 'canceled';
    public const STATUS_PENDING  = 'pending';

    public const TYPE_NFSE = 'NFS-e';
    public const TYPE_NFE  = 'NF-e';
    public const TYPE_CTE  = 'CT-e';
    public const TYPE_MDFE = 'MDF-e';
    public const TYPE_NFCE = 'NFC-e';
}
