<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\Finance;

use FalconERP\Skeleton\Enums\Finance\FinancialAccountEnum;
use FalconERP\Skeleton\Enums\FinancialAccountsTypeEnum;
use FalconERP\Skeleton\Models\Erp\People\People;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\ActionTrait;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

class FinancialAccount extends BaseModel implements AuditableContract
{
    use ActionTrait;
    use Auditable;
    use HasFactory;
    use SetSchemaTrait;
    use SoftDeletes;

    protected $fillable = [
        'description',
        'people_id',
        'status',
        'active',
        'obs',
    ];

    protected $attributes = [
        'type'   => FinancialAccountsTypeEnum::CLIENT_TYPE,
        'status' => FinancialAccountEnum::STATUS_OPENED,
        'active' => true,
    ];

    public function people(): BelongsTo
    {
        return $this->belongsTo(People::class);
    }

    public function financialMovement(): HasMany
    {
        return $this->hasMany(FinancialMovement::class, 'financial_accounts_id');
    }

    public function scopeByPeopleIds($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'people_ids'), fn ($query, $params) => $query->whereIn('people_id', $params));
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
        return $this->trashed();
    }

    private function canUpdate(): bool
    {
        return !$this->trashed();
    }

    private function canDelete(): bool
    {
        return !$this->trashed();
    }

    private function canFollow(): bool
    {
        return true;

        /* return (!$this->trashed()
            && !$this->is_public
            && !$this->followers()->where('follower_people_id', auth()->people()?->id)->exists()
            && $this->id !== auth()->people()?->id) ?? false; */
    }

    private function canUnfollow(): bool
    {
        return true;

        /* return (!$this->trashed()
            && !$this->is_public
            && $this->followers()->where('follower_people_id', auth()->people()?->id)->exists()) ?? false; */
    }
}
