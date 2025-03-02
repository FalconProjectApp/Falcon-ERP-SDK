<?php

namespace FalconERP\Skeleton\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Notifications\Notifiable;
use QuantumTecnology\ModelBasicsExtension\BaseModel;

class BillingPackBillingPivot extends Pivot
{
    use Notifiable;

    protected $connection = 'pgsql';

    protected $fillable = [];
}
