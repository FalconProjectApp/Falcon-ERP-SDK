<?php

namespace FalconERP\Skeleton\Models\Erp\Finance;

use Illuminate\Database\Eloquent\Builder;
use FalconERP\Skeleton\Enums\ReleaseTypeEnum;
use Illuminate\Database\Eloquent\SoftDeletes;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

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

    protected function scopeByReleaseType(Builder $query, array $params = []): Builder
    {
        return $query->when(
            $this->filtered($params, 'release_type'),
            fn ($query, $params) => $query->whereIn('release_type', $params)
        );
    }
}
