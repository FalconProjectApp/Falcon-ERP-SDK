<?php

namespace FalconERP\Skeleton\Models\Erp\Stock;

use FalconERP\Skeleton\Models\Erp\People\PeopleFollow;
use FalconERP\Skeleton\Models\Erp\Shop\Shop;
use FalconERP\Skeleton\Models\Erp\Shop\ShopLinked;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\ActionTrait;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

class Group extends BaseModel implements AuditableContract
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;
    use ActionTrait;
    use Auditable;

    public const ATTRIBUTE_DESCRIPTION = 'description';

    protected $fillable = [
        self::ATTRIBUTE_DESCRIPTION,
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you may specify the relationships that the model should have with
    |
    */

    /**
     * ShopServices function.
     */
    public function shops(): BelongsToMany
    {
        return $this->morphToMany(
            related: Shop::class,
            name: 'linkable',
            table: ShopLinked::class
        )->withTimestamps();
    }

    public function followers()
    {
        return $this
            ->morphToMany(
                static::class,
                'followable',
                PeopleFollow::class,
                'followable_id',
                'follower_people_id'
            )
            ->withTimestamps();
    }

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
    | Scopes
    |--------------------------------------------------------------------------
    |
    | Here you may specify the scopes that the model should have with
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
            'can_view'     => $this->canView(),
            'can_restore'  => $this->canRestore(),
            'can_update'   => $this->canUpdate(),
            'can_delete'   => $this->canDelete(),
            'can_follow'   => $this->canFollow(),
            'can_unfollow' => $this->canUnfollow(),
        ];
    }

    public function canView(): bool
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
        return (!$this->trashed()
            && !$this->followers()->where('follower_people_id', auth()->people()->id)->exists())
            ?? false;
    }

    private function canUnfollow(): bool
    {
        return (!$this->trashed()
            && $this->followers()->where('follower_people_id', auth()->people()->id)->exists())
            ?? false;
    }
}
