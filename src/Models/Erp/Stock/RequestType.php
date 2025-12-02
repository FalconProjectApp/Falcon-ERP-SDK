<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\Stock;

use FalconERP\Skeleton\Database\Factories\RequestTypeFactory;
use FalconERP\Skeleton\Enums\RequestEnum;
use FalconERP\Skeleton\Models\Erp\Fiscal\NatureOperation;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\ActionTrait;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

class RequestType extends BaseModel
{
    use ActionTrait;
    use HasFactory;
    use SetSchemaTrait;
    use SoftDeletes;

    protected $fillable = [
        'description',
        'request_type',
        'type',
        'active',
    ];

    protected $attributes = [
        'active' => true,
        'type'      => RequestEnum::TYPE_CLIENT,
    ];

    protected static function newFactory()
    {
        return RequestTypeFactory::new();
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    |
    | Here you may specify the attributes that should be cast to native types.
    |
    */

    protected function natureOperationDefault(): Attribute
    {
        return Attribute::make(
            get: fn () => NatureOperation::find(1),
        );
    }

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
            'can_view'     => $this->canView(),
            'can_restore'  => $this->canRestore(),
            'can_update'   => $this->canUpdate(),
            'can_delete'   => $this->canDelete(),
            'can_follow'   => $this->canFollow(),
            'can_unfollow' => $this->canUnfollow(),
        ];
    }

    private function canView(): bool
    {
        return true;
    }

    private function canRestore(): bool
    {
        return $this->trashed()
            && RequestEnum::TYPE_SYSTEM !== $this->type;
    }

    private function canUpdate(): bool
    {
        return !$this->trashed()
            && RequestEnum::TYPE_SYSTEM !== $this->type;
    }

    private function canDelete(): bool
    {
        return !$this->trashed()
            && RequestEnum::TYPE_SYSTEM !== $this->type;
    }

    private function canFollow(): bool
    {
        return true;
    }

    private function canUnfollow(): bool
    {
        return true;
    }
}
