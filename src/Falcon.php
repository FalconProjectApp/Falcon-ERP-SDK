<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton;

use Exception;
use FalconERP\Skeleton\Repositories\BigData\AuthRepository;
use FalconERP\Skeleton\Repositories\BigData\CityRepository;
use FalconERP\Skeleton\Repositories\BigData\DeliveryRepository;
use FalconERP\Skeleton\Repositories\BigData\IpRepository;
use FalconERP\Skeleton\Repositories\BigData\XmlRepository;
use FalconERP\Skeleton\Repositories\Finance\AccountRepository;
use FalconERP\Skeleton\Repositories\Finance\BillRepository;
use FalconERP\Skeleton\Repositories\Fiscal\InvoiceRepository;
use FalconERP\Skeleton\Repositories\Shop\ShopRepository;
use Illuminate\Support\Facades\Cache;

class Falcon
{
    private static $auth;

    public static function bigDataService(string $module)
    {
        return match ($module) {
            'xml'      => new XmlRepository(self::$auth),
            'city'     => new CityRepository(self::$auth),
            'ip'       => new IpRepository(self::$auth),
            'delivery' => new DeliveryRepository(self::$auth),
            'auth'     => new AuthRepository(),
            default    => throw new Exception("Invalid module: $module"),
        };
    }

    public static function financeService(string $module)
    {
        return match ($module) {
            'bill'    => new BillRepository(),
            'account' => new AccountRepository(),
            default   => throw new Exception("Invalid module: $module"),
        };
    }

    public static function fiscalService(string $module)
    {
        return match ($module) {
            'invoice' => new InvoiceRepository(),
            default   => throw new Exception("Invalid module: $module"),
        };
    }

    public static function shopService(string $module, array $params = [])
    {
        return match ($module) {
            'shop'  => new ShopRepository($params),
            default => throw new Exception("Invalid module: $module"),
        };
    }
}
