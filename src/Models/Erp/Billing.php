<?php

namespace FalconERP\Skeleton\Models\Erp;

use QuantumTecnology\ModelBasicsExtension\BaseModel;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Billing extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'rule',
        'effective_start_date',
        'effective_end_date',
        'contracted_quantity',
        'used_quantity',
    ];

    /**
     * byPortfolioID function.
     */
    public function scopeByBetweenAndRule(Builder $query, string $ruleConfig, string $date): Builder
    {
        return $query->where('rule', $ruleConfig)
            ->whereDate('effective_start_date', '<=', $date)
            ->whereDate('effective_end_date', '>=', $date);
    }
}
