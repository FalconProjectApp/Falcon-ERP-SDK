<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Enums\Stock\Shipment;

enum ShipmentStatusEnum: string
{
    case PENDING             = 'pending';
    case IN_TRANSIT          = 'in_transit';
    case DELIVERED           = 'delivered';
    case CANCELLED           = 'cancelled';
    case RETURNED            = 'returned';
    case FAILED              = 'failed';
    case ON_HOLD             = 'on_hold';
    case COMPLETED           = 'completed';
    case PARTIALLY_DELIVERED = 'partially_delivered';
    case AWAITING_PICKUP     = 'awaiting_pickup';
    case IN_WAREHOUSE        = 'in_warehouse';
}
