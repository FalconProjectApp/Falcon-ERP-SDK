<?php

namespace FalconERP\Skeleton;

use FalconERP\Skeleton\Repositories\BigData\XmlRepository;
use FalconERP\Skeleton\Repositories\BigData\AuthRepository;
use FalconERP\Skeleton\Repositories\Finance\BillRepository;
use FalconERP\Skeleton\Repositories\Finance\AccountRepository;

class Falcon
{
    private static $auth;

    public static function bigDataService(string $module)
    {
        if(!self::$auth) {
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
}
