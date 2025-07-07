<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Models\Erp\Stock\Traits\Request;

trait ProductCollunsTrait
{
    public const ATTRIBUTE_ID              = 'id';
    public const ATTRIBUTE_GROUPS_ID       = 'group_id';
    public const ATTRIBUTE_STATUS          = 'status';
    public const ATTRIBUTE_DESCRIPTION     = 'description';
    public const ATTRIBUTE_EAN             = 'ean';
    public const ATTRIBUTE_BAR_CODE        = 'bar_code';
    public const ATTRIBUTE_LAST_BUY_VALUE  = 'last_buy_value';
    public const ATTRIBUTE_LAST_SELL_VALUE = 'last_sell_value';
    public const ATTRIBUTE_LAST_RENT_VALUE = 'last_rent_value';
    public const ATTRIBUTE_PROVIDER_CODE   = 'provider_code';
    public const ATTRIBUTE_OBSERVATIONS    = 'observations';
}
