<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton;

use FalconERP\Skeleton\Repositories\BigData\AuthRepository;
use FalconERP\Skeleton\Repositories\BigData\XmlRepository;
use FalconERP\Skeleton\Repositories\Finance\AccountRepository;
use FalconERP\Skeleton\Repositories\Finance\BillRepository;
use FalconERP\Skeleton\Repositories\Fiscal\InvoiceRepository;
use FalconERP\Skeleton\Repositories\Shop\ShopRepository;

class Falcon
{
    private static $auth;

    public static function bigDataService(string $module)
    {
        if (!self::$auth) {
            self::$auth = true;
            self::$auth = self::bigDataService('auth')->login();
        }

        return match ($module) {
            'xml'   => new XmlRepository(self::$auth),
            'auth'  => new AuthRepository(),
            default => false,
        };
    }

    public static function financeService(string $module)
    {
        return match ($module) {
            'bill'    => new BillRepository(),
            'account' => new AccountRepository(),
            default   => false,
        };
    }

    public static function fiscalService(string $module)
    {
        return match ($module) {
            'invoice' => new InvoiceRepository(),
            default   => false,
        };
    }

    public static function shopService(string $module, array $params = [])
    {
        return match ($module) {
            'shop'  => new ShopRepository($params),
            default => false,
        };
    }
}
