<?php

namespace FalconERP\Skeleton\Models\Erp\Finance;

use FalconERP\Skeleton\Enums\ReleaseTypeEnum;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReleasesType extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;

    protected $fillable = [
        'description',
        'release_type',
        'type',
        'active',
    ];

    protected $attributes = [
        'active' => true,
        'type'   => ReleaseTypeEnum::TYPE_CLIENT,
    ];

    public $allowedIncludes = [];
}
