<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\Stock\Traits\Request;

trait ProductCollunsTrait
{
    public const ATTRIBUTE_ID              = 'id';
    public const ATTRIBUTE_GROUPS_ID       = 'group_id';
    public const ATTRIBUTE_VOLUME_TYPE_ID  = 'volume_types_id';
    public const ATTRIBUTE_STATUS          = 'status';
    public const ATTRIBUTE_DESCRIPTION     = 'description';
    public const V_ATTRIBUTE_EAN           = 'ean';
    public const ATTRIBUTE_LAST_BUY_VALUE  = 'last_buy_value';
    public const ATTRIBUTE_LAST_SELL_VALUE = 'last_sell_value';
    public const ATTRIBUTE_LAST_RENT_VALUE = 'last_rent_value';
    public const ATTRIBUTE_OBSERVATIONS    = 'observations';
    public const ATTRIBUTE_CREATED_AT      = 'created_at';
    public const ATTRIBUTE_UPDATED_AT      = 'updated_at';

    public const V_ATTRIBUTE_BALANCE_STOCK_TOTAL   = 'balance_stock_total';
    public const V_ATTRIBUTE_BALANCE_TRANSIT_TOTAL = 'balance_transit_total';
    public const V_ATTRIBUTE_BALANCE_TOTAL         = 'balance_total';
    public const V_ATTRIBUTE_VALUE_TOTAL           = 'value_total';
}
