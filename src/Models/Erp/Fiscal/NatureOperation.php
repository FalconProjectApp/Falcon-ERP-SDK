<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\Fiscal;

use FalconERP\Skeleton\Enums\Fiscal\NatureOperationTypeEnum;
use FalconERP\Skeleton\Observers\NotificationObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Observers\CacheObserver as ObserversCacheObserver;
use QuantumTecnology\ModelBasicsExtension\Traits\ActionTrait;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

#[ObservedBy([
    ObserversCacheObserver::class,
    NotificationObserver::class,
])]
class NatureOperation extends BaseModel
{
    use ActionTrait;
    use HasFactory;
    use SetSchemaTrait;
    use SoftDeletes;

    protected $fillable = [
        'description',
        'people_issuer_id',
    ];
    protected $casts = [
        'description'    => 'string',
        'serie_id'       => 'integer',
        'operation_type' => NatureOperationTypeEnum::class,
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you may specify the relationships that the model should have with
    |
    */

    public function serie(): BelongsTo
    {
        return $this->belongsTo(Serie::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    | Here you may specify the scopes that the model should have with
    |
    */

    #[Scope]
    public function bySerieIds($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'serie_ids'), fn ($query, $params) => $query->whereIn('serie_id', $params));
    }

    /*
    |--------------------------------------------------------------------------
    | Others
    |--------------------------------------------------------------------------
    |
    | Here you may specify the others that the model should have with
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    |
    | Here you may specify the attributes that should be cast to native types.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    |
    | Here you may specify the actions that the model should have with
    |
    */

    protected function setActions(): array
    {
        return [
            'can_view'    => $this->canView(),
            'can_restore' => $this->canRestore(),
            'can_update'  => $this->canUpdate(),
            'can_delete'  => $this->canDelete(),
        ];
    }

    private function canView(): bool
    {
        return true;
    }

    private function canRestore(): bool
    {
        return $this->trashed() && false;
    }

    private function canUpdate(): bool
    {
        return !$this->trashed() && false;
    }

    private function canDelete(): bool
    {
        return !$this->trashed() && false;
    }
}
