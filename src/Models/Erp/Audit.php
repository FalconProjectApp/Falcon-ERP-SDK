<?php

namespace FalconERP\Skeleton\Models\Erp;

use OwenIt\Auditing\Models\Audit as AuditBase;

class Audit extends AuditBase
{
    protected $table = 'public.audits';
}
