<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\Finance;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

class FinancialMovement extends BaseModel
{
    use HasFactory;
    use SetSchemaTrait;
    use SoftDeletes;

    protected $fillable = [
        'releases_types_id',
        'financial_accounts_id',
        /*  'bill_id',
        'date',
        'value',
        'status', */
        'obs',
    ];

    protected $attributes = [];

    #[Scope]
    protected function byFinancialAccountsIds(Builder $query, array $params = []): Builder
    {
        return $query->when(
            $this->filtered($params, 'financial_accounts_ids'),
            fn ($query, $financial_account_ids) => $query->whereIn('financial_accounts_id', $financial_account_ids)
        );
    }

    #[Scope]
    protected function byReleasesTypesIds(Builder $query, array $releases_types_ids = []): Builder
    {
        return $query->when(
            $this->filtered($releases_types_ids, 'releases_types_ids'),
            fn ($query, $releases_types_ids) => $query->whereIn('releases_types_id', $releases_types_ids)
        );
    }
}
