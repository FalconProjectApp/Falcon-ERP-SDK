<?php

namespace FalconERP\Skeleton\Models\Erp;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Contracts\Database\Eloquent\Builder;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
    #[Scope]
    public function byBetweenAndRule(Builder $query, string $ruleConfig, string $date): Builder
    {
        return $query->where('rule', $ruleConfig)
            ->whereDate('effective_start_date', '<=', $date)
            ->whereDate('effective_end_date', '>=', $date);
    }
}
