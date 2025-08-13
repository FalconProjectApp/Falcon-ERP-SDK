<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Models\Erp\Stock\Traits\Stock;

trait StockCollunsTrait
{
    public const ATTRIBUTE_ID              = 'id';
    public const ATTRIBUTE_PRODUCT_ID      = 'product_id';
    public const ATTRIBUTE_VOLUME_TYPE_ID  = 'volume_type_id';
    public const ATTRIBUTE_DESCRIPTION     = 'description';
    public const V_ATTRIBUTE_DUN           = 'dun';
    public const ATTRIBUTE_BALANCE_TRANSIT = 'balance_transit';
    public const ATTRIBUTE_BALANCE_STOCK   = 'balance_stock';
    public const ATTRIBUTE_VALUE           = 'value';
    public const ATTRIBUTE_COLOR           = 'color';
    public const ATTRIBUTE_ON_SHOP         = 'on_shop';
    public const ATTRIBUTE_MEASURE         = 'measure';
    public const ATTRIBUTE_WEIGHT          = 'weight';
    public const ATTRIBUTE_HEIGHT          = 'height';
    public const ATTRIBUTE_WIDTH           = 'width';
    public const ATTRIBUTE_DEPTH           = 'depth';
    public const ATTRIBUTE_STATUS          = 'status';
    public const ATTRIBUTE_OBS             = 'obs';
    public const V_ATTRIBUTE_IDLE_DAYS     = 'idle_days';
    public const V_ATTRIBUTE_BALANCE       = 'balance';
    public const V_ATTRIBUTE_VALUE_TOTAL   = 'value_total';
    public const V_ATTRIBUTE_ACTIONS       = 'actions';
}
