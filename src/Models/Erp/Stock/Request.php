<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\Stock;

use Carbon\Carbon;
use FalconERP\Skeleton\Database\Factories\RequestFactory;
use FalconERP\Skeleton\Enums\RequestEnum;
use FalconERP\Skeleton\Models\Erp\Finance\PaymentMethod;
use FalconERP\Skeleton\Models\Erp\People\People;
use FalconERP\Skeleton\Models\Erp\People\PeopleFollow;
use FalconERP\Skeleton\Models\Erp\Stock\Traits\Request\RequestNfeTrait;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\ActionTrait;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

class Request extends BaseModel implements AuditableContract
{
    use ActionTrait;
    use Auditable;
    use HasFactory;
    use RequestNfeTrait;
    use SetSchemaTrait;
    use SoftDeletes;

    public const ATTRIBUTE_ID                = 'id';
    public const ATTRIBUTE_DESCRIPTION       = 'description';
    public const ATTRIBUTE_OBSERVATIONS      = 'observations';
    public const ATTRIBUTE_STATUS            = 'status';
    public const ATTRIBUTE_REQUEST_TYPE_ID   = 'request_type_id';
    public const ATTRIBUTE_RESPONSIBLE_ID    = 'responsible_id';
    public const ATTRIBUTE_THIRD_ID          = 'third_id';
    public const ATTRIBUTE_ALLOWER_ID        = 'allower_id';
    public const ATTRIBUTE_FREIGHT_VALUE     = 'freight_value';
    public const ATTRIBUTE_DISCOUNT_VALUE    = 'discount_value';
    public const ATTRIBUTE_PAYMENT_METHOD    = 'payment_method_id';
    public const V_ATTRIBUTE_CONTINUOUS_DAYS = 'continuous_days';

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
        self::ATTRIBUTE_ID                => 'integer',
        self::ATTRIBUTE_DESCRIPTION       => 'string',
        self::ATTRIBUTE_OBSERVATIONS      => 'string',
        self::ATTRIBUTE_STATUS            => 'string',
        self::ATTRIBUTE_REQUEST_TYPE_ID   => 'integer',
        self::ATTRIBUTE_RESPONSIBLE_ID    => 'integer',
        self::ATTRIBUTE_THIRD_ID          => 'integer',
        self::ATTRIBUTE_ALLOWER_ID        => 'integer',
        self::ATTRIBUTE_FREIGHT_VALUE     => 'integer',
        self::ATTRIBUTE_DISCOUNT_VALUE    => 'integer',
        self::ATTRIBUTE_PAYMENT_METHOD    => 'integer',
        self::V_ATTRIBUTE_CONTINUOUS_DAYS => 'integer',
    ];

    protected $attributes = [
        self::ATTRIBUTE_FREIGHT_VALUE     => 0,
        self::ATTRIBUTE_DISCOUNT_VALUE    => 0,
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you may specify the relationships that the model should have with
    |
    */

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
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

    protected static function newFactory()
    {
        return RequestFactory::new();
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
            get: fn () => $this->items->sum('value_total'),
        );
    }

    /**
     * itens_value_total_with_discount.
     */
    protected function itensValueTotalWithDiscount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->items->sum('value_total_with_discount'),
        );
    }

    /**
     * continuous_days.
     */
    protected function continuousDays(): Attribute
    {
        return Attribute::make(
            get: fn (): int => (int) Carbon::parse($this->attributes['created_at'])
                ->diffInDays(Carbon::now(), false) + 1,
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

    #[Scope]
    protected function byStatus($query): void
    {
        $query->when(request()->filter['status'] ?? false, function ($query, $status) {
            $query->whereIn(self::ATTRIBUTE_STATUS, explode(',', $status));
        });
    }

    #[Scope]
    protected function byResponsibleIds(Builder $query, string | array $params = []): void
    {
        $query->when($this->filtered($params, 'responsible_ids'), function ($query, $params) {
            $query->whereIn(self::ATTRIBUTE_RESPONSIBLE_ID, $params);
        });
    }

    #[Scope]
    protected function byThirdIds(Builder $query, string | array $params = []): void
    {
        $query->when($this->filtered($params, 'third_ids'), function ($query, $params) {
            $query->whereIn(self::ATTRIBUTE_THIRD_ID, $params);
        });
    }

    #[Scope]
    protected function byAllowerIds(Builder $query, string | array $params = []): void
    {
        $query->when($this->filtered($params, 'allower_ids'), function ($query, $params) {
            $query->whereIn(self::ATTRIBUTE_ALLOWER_ID, $params);
        });
    }

    #[Scope]
    protected function byRequestTypeIds(Builder $query, string | array $params = []): void
    {
        $query->when($this->filtered($params, 'request_type_ids'), function ($query, $params) {
            $query->whereIn(self::ATTRIBUTE_REQUEST_TYPE_ID, $params);
        });
    }

    #[Scope]
    protected function byCreatedAtStart(Builder $query, string | array $params = []): void
    {
        $query->when($this->filtered($params, 'created_at_start'), function ($query, $params) {
            $query->whereDate('created_at', '>=', $params);
        });
    }

    #[Scope]
    protected function byCreatedAtEnd(Builder $query, string | array $params = []): void
    {
        $query->when($this->filtered($params, 'created_at_end'), function ($query, $params) {
            $query->whereDate('created_at', '<=', $params);
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
            'can_view'              => $this->canView(),
            'can_restore'           => $this->canRestore(),
            'can_update'            => $this->canUpdate(),
            'can_delete'            => $this->canDelete(),
            'can_follow'            => $this->canFollow(),
            'can_unfollow'          => $this->canUnfollow(),
            'can_issue_nfce'        => $this->canIssueNfce(),
            'can_edit_responsible'  => $this->canEditResponsible(),
            'can_edit_third'        => $this->canEditThird(),
            'can_edit_allower'      => $this->canEditAllower(),
            'can_finish'            => $this->canFinish(),
            'can_authorize'         => $this->canAuthorize(),
            'can_deny'              => $this->canDeny(),
            'can_cancel'            => $this->canCancel(),
            'can_edit_delivery'     => $this->canEditDelivery(),
            'can_edit_payment'      => $this->canEditPayment(),
            'can_edit_request_type' => $this->canEditRequestType(),
            'can_edit_items'        => $this->canEditItems(),
            'can_download_xml'      => $this->canDownloadXml(),
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
            && $this->items->count() > 0
            && !$this->has_errors;
    }

    private function canEditResponsible(): bool
    {
        return
            !$this->trashed()
            && RequestEnum::REQUEST_STATUS_OPEN === $this->status
            && RequestEnum::REQUEST_STATUS_CANCELED !== $this->status
            && RequestEnum::REQUEST_STATUS_DENIED !== $this->status;
    }

    private function canEditThird(): bool
    {
        return
            !$this->trashed()
            && RequestEnum::REQUEST_STATUS_OPEN === $this->status
            && RequestEnum::REQUEST_STATUS_CANCELED !== $this->status
            && RequestEnum::REQUEST_STATUS_DENIED !== $this->status;
    }

    private function canEditAllower(): bool
    {
        return false;
    }

    private function canFinish(): bool
    {
        return
            !$this->trashed()
            && RequestEnum::REQUEST_STATUS_OPEN === $this->status
            && $this->items()->count() > 0
            && !$this->has_errors
            && RequestEnum::REQUEST_STATUS_CANCELED !== $this->status
            && RequestEnum::REQUEST_STATUS_DENIED !== $this->status
            && RequestEnum::REQUEST_STATUS_FINISHED !== $this->status
            && RequestEnum::REQUEST_STATUS_REJECTED !== $this->status;
    }

    private function canAuthorize(): bool
    {
        return
            !$this->trashed()
            && RequestEnum::REQUEST_STATUS_OPEN === $this->status
            && RequestEnum::REQUEST_STATUS_CANCELED !== $this->status
            && RequestEnum::REQUEST_STATUS_DENIED !== $this->status
            && RequestEnum::REQUEST_STATUS_FINISHED !== $this->status
            && RequestEnum::REQUEST_STATUS_REJECTED !== $this->status
            && $this->items()->count() > 0;
    }

    private function canDeny(): bool
    {
        return
            !$this->trashed()
            && RequestEnum::REQUEST_STATUS_OPEN === $this->status
            && RequestEnum::REQUEST_STATUS_CANCELED !== $this->status
            && RequestEnum::REQUEST_STATUS_DENIED !== $this->status
            && $this->items()->count() > 0;
    }

    private function canCancel(): bool
    {
        return
            !$this->trashed()
            && RequestEnum::REQUEST_STATUS_OPEN === $this->status
            && RequestEnum::REQUEST_STATUS_CANCELED !== $this->status
            && RequestEnum::REQUEST_STATUS_DENIED !== $this->status
            && RequestEnum::REQUEST_STATUS_FINISHED !== $this->status
            && $this->items()->count() > 0;
    }

    private function canEditDelivery(): bool
    {
        return
            !$this->trashed()
            && RequestEnum::REQUEST_STATUS_OPEN === $this->status;
    }

    private function canEditPayment(): bool
    {
        return
            !$this->trashed()
            && RequestEnum::REQUEST_STATUS_OPEN === $this->status;
    }

    private function canEditRequestType(): bool
    {
        return
            !$this->trashed()
            && RequestEnum::REQUEST_STATUS_OPEN === $this->status;
    }

    private function canEditItems(): bool
    {
        return
            !$this->trashed()
            && RequestEnum::REQUEST_STATUS_OPEN === $this->status
            && 0 === $this->items()->count();
    }

    private function canDownloadXml(): bool
    {
        return
            !$this->trashed()
            && $this->has_errors === false;
    }
}
