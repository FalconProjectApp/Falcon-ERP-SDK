<?php

namespace FalconERP\Skeleton;

use FalconERP\Skeleton\Repositories\BigData\XmlRepository;
use FalconERP\Skeleton\Repositories\Finance\AccountRepository;
use FalconERP\Skeleton\Repositories\Finance\BillRepository;

class Falcon
{
    public static function bigDataService(string $module)
    {
        return match ($module) {
            'xml'   => new XmlRepository(),
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
