<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton;

use Exception;
use FalconERP\Skeleton\Repositories\BigData\AuthRepository;
use FalconERP\Skeleton\Repositories\BigData\CityRepository;
use FalconERP\Skeleton\Repositories\BigData\IpRepository;
use FalconERP\Skeleton\Repositories\BigData\XmlRepository;
use FalconERP\Skeleton\Repositories\Finance\AccountRepository;
use FalconERP\Skeleton\Repositories\Finance\BillRepository;
use FalconERP\Skeleton\Repositories\Fiscal\InvoiceRepository;
use FalconERP\Skeleton\Repositories\Shop\ShopRepository;

class Falcon
{
    private static $auth;

    public static function bigDataService(string $module, bool $isPrivate = true)
    {
        if (!self::$auth && $isPrivate) {
            self::$auth = true;

            try {
                self::$auth = self::bigDataService('auth')->login();
            } catch (Exception $e) {
                self::$auth = false;

                return null;
            }
        }

        return match ($module) {
            'xml'   => new XmlRepository(self::$auth),
            'city'  => new CityRepository(self::$auth),
            'ip'    => new IpRepository(self::$auth),
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
