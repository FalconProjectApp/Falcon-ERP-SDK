<?php

namespace FalconERP\Skeleton\Enums;

use Illuminate\Support\Collection;
use QuantumTecnology\EnumBasicsExtension\BaseEnum;

abstract class CacheEnum extends BaseEnum
{
    public const KEY_PEOPLE_TOTAL_COUNT        = 'people_total_count';
    public const KEY_PEOPLE_TRASHED_COUNT      = 'people_trashed_count';
    public const KEY_PEOPLE_FOLLOWERS_ME_COUNT = 'people_followers_me_count_';
    public const KEY_PEOPLE_FOLLOWING_ME_COUNT = 'people_following_me_count_';
    public const KEY_PEOPLE_FOLLOWERS_COUNT    = 'people_followers_count';
    public const KEY_PEOPLE_FOLLOWING_COUNT    = 'people_following_count';
    public const KEY_PEOPLE_IS_PUBLIC_COUNT    = 'people_is_public_count';
    public const KEY_PEOPLE_IS_PRIVATE_COUNT   = 'people_is_private_count';

    public const KEY_PRODUCT_TOTAL_COUNT         = 'product_total_count';
    public const KEY_PRODUCT_TRASHED_COUNT       = 'product_trashed_count';
    public const KEY_PRODUCT_STOCK_TOTAL_COUNT   = 'product_stock_total_count';
    public const KEY_PRODUCT_STOCK_TRASHED_COUNT = 'product_stock_trashed_count';
    public const KEY_PRODUCT_STOCK_TOTAL_VALUE   = 'product_stock_total_value';
    public const KEY_PRODUCT_STOCK_TRASHED_VALUE = 'product_stock_trashed_value';
    public const KEY_PRODUCT_BALANCE_STOCK       = 'product_balance_stock';
    public const KEY_PRODUCT_BALANCE_TRANSIT     = 'product_balance_transit';

    public const KEY_ORDER_TOTAL_COUNT         = 'order_total_count';
    public const KEY_ORDER_TRASHED_COUNT       = 'order_trashed_count';
    public const KEY_ORDER_STATUS_OPEN_COUNT   = 'order_status_open_count';
    public const KEY_ORDER_STATUS_IN_PROGRESS_COUNT = 'order_status_in_progress_count';
    public const KEY_ORDER_STATUS_PAUSE_COUNT  = 'order_status_pause_count';
    public const KEY_ORDER_STATUS_CLOSEDS_COUNT = 'order_status_closeds_count';

    public const KEY_FINANCE_RELEASE_TYPE_RECEIPT_ID = 'finance_release_type_receipt_id';
    public const KEY_FINANCE_RELEASE_TYPE_PAYMENT_ID = 'finance_release_type_payment_id';

    public static function keys(): Collection
    {
        return new Collection(static::filterConstants('KEY'));
    }
}
