<?php

namespace FalconERP\Skeleton\Models\Erp\Shop;

use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShopLinked extends BaseModel
{
    use HasFactory;
    use SetSchemaTrait;

    protected $fillable = [
        'name',
    ];
}
