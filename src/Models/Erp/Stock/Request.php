<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\Stock;

use OwenIt\Auditing\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use FalconERP\Skeleton\Models\Erp\People\People;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use FalconERP\Skeleton\Models\Erp\Stock\RequestType;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use FalconERP\Skeleton\Models\Erp\People\PeopleFollow;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use FalconERP\Skeleton\Models\Erp\Finance\PaymentMethod;
use FalconERP\Skeleton\Database\Factories\RequestFactory;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use QuantumTecnology\ModelBasicsExtension\Traits\ActionTrait;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;
use FalconERP\Skeleton\Models\Erp\Stock\Traits\Request\RequestNfeTrait;

class Request extends BaseModel implements AuditableContract
{
    use ActionTrait;
    use Auditable;
    use HasFactory;
    use RequestNfeTrait;
    use SetSchemaTrait;
    use SoftDeletes;

    public const ATTRIBUTE_ID              = 'id';
    public const ATTRIBUTE_DESCRIPTION     = 'description';
    public const ATTRIBUTE_OBSERVATIONS    = 'observations';
    public const ATTRIBUTE_STATUS          = 'status';
    public const ATTRIBUTE_REQUEST_TYPE_ID = 'request_type_id';
    public const ATTRIBUTE_RESPONSIBLE_ID  = 'responsible_id';
    public const ATTRIBUTE_THIRD_ID        = 'third_id';
    public const ATTRIBUTE_ALLOWER_ID      = 'allower_id';
    public const ATTRIBUTE_FREIGHT_VALUE   = 'freight_value';
    public const ATTRIBUTE_DISCOUNT_VALUE  = 'discount_value';
    public const ATTRIBUTE_PAYMENT_METHOD  = 'payment_method_id';

    protected $fillable = [
        self::ATTRIBUTE_DESCRIPTION,
        self::ATTRIBUTE_OBSERVATIONS,
        self::ATTRIBUTE_STATUS,
        self::ATTRIBUTE_REQUEST_TYPE_ID,
        self::ATTRIBUTE_RESPONSIBLE_ID,
        self::ATTRIBUTE_THIRD_ID,
        self::ATTRIBUTE_ALLOWER_ID,
        self::ATTRIBUTE_FREIGHT_VALUE,
        self::ATTRIBUTE_DISCOUNT_VALUE,
        self::ATTRIBUTE_PAYMENT_METHOD,
    ];

    protected $casts = [
        self::ATTRIBUTE_ID              => 'integer',
        self::ATTRIBUTE_DESCRIPTION     => 'string',
        self::ATTRIBUTE_OBSERVATIONS    => 'string',
        self::ATTRIBUTE_STATUS          => 'string',
        self::ATTRIBUTE_REQUEST_TYPE_ID => 'integer',
        self::ATTRIBUTE_RESPONSIBLE_ID  => 'integer',
        self::ATTRIBUTE_THIRD_ID        => 'integer',
        self::ATTRIBUTE_ALLOWER_ID      => 'integer',
        self::ATTRIBUTE_FREIGHT_VALUE   => 'integer',
        self::ATTRIBUTE_DISCOUNT_VALUE  => 'integer',
        self::ATTRIBUTE_PAYMENT_METHOD  => 'integer',
    ];

    protected $attributes = [
        self::ATTRIBUTE_FREIGHT_VALUE  => 0,
        self::ATTRIBUTE_DISCOUNT_VALUE => 0,
    ];

    protected static function newFactory()
    {
        return RequestFactory::new();
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you may specify the relationships that the model should have with
    |
    */

    public function itens(): HasMany
    {
        return $this->hasMany(Item::$collectionClass);
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(People::class, self::ATTRIBUTE_RESPONSIBLE_ID);
    }

    public function third(): BelongsTo
    {
        return $this->belongsTo(People::class, self::ATTRIBUTE_THIRD_ID);
    }

    public function allower(): BelongsTo
    {
        return $this->belongsTo(People::class, self::ATTRIBUTE_ALLOWER_ID);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function requestType(): BelongsTo
    {
        return $this->belongsTo(RequestType::class);
    }

    public function followers(): MorphToMany
    {
        return $this
            ->morphToMany(static::class, 'followable', PeopleFollow::class, 'followable_id', 'follower_people_id')
            ->withTimestamps()
            ->withTrashed();
    }

    public function followings(): MorphToMany
    {
        return $this
            ->morphToMany(static::class, 'followable', PeopleFollow::class, 'follower_people_id', 'followable_id')
            ->withTimestamps()
            ->withTrashed();
    }

    public function canView(): bool
    {
        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    |
    | Here you may specify the attributes that should be cast to native types.
    |
    */

    /**
     * ind_pres.
     */
    protected function indPres(): Attribute
    {
        return Attribute::make(
            get: fn () => (int) true, // Acrescentar este input no request
        );
    }

    protected function itensValueTotal(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->itens->sum('value_total'),
        );
    }

    /**
     * itens_value_total_with_discount.
     */
    protected function itensValueTotalWithDiscount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->itens->sum('value_total_with_discount'),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    | Here you may specify the scopes that the model should have with
    |
    */

    protected function scopeByStatus($query): void
    {
        $query->when(request()->filter['status'] ?? false, function ($query, $status) {
            $query->whereIn(self::ATTRIBUTE_STATUS, explode(',', $status));
        });
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
            'can_view'       => $this->canView(),
            'can_restore'    => $this->canRestore(),
            'can_update'     => $this->canUpdate(),
            'can_delete'     => $this->canDelete(),
            'can_follow'     => $this->canFollow(),
            'can_unfollow'   => $this->canUnfollow(),
            'can_issue_nfce' => $this->canIssueNfce(),
        ];
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
        return (
        !$this->trashed()
            && auth()->check()
            && !$this->followers()->where('follower_people_id', auth()->people()->id)->exists()
            ) ?? false;
    }

    private function canUnfollow(): bool
    {
        return (
        !$this->trashed()
            && auth()->check()
            && $this->followers()->where('follower_people_id', auth()->people()->id)->exists()
            ) ?? false;
    }

    private function canIssueNfce(): bool
    {
        $this->loadMissing(
            'requestType',
        );

        return $this->is_nfce
            && !null === $this->tag_emit->crt
            && !null === $this->tag_emit->ie
            && !$this->trashed()
            && $this->itens->count() > 0
            && !$this->has_errors;
    }
}
