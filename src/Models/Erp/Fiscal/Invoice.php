<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\Fiscal;

use FalconERP\Skeleton\Enums\ArchiveEnum;
use FalconERP\Skeleton\Models\Erp\People\People;
use FalconERP\Skeleton\Observers\NotificationObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Observers\CacheObserver;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;
use QuantumTecnology\ServiceBasicsExtension\Traits\ArchiveModelTrait;

#[ObservedBy([
    CacheObserver::class,
    NotificationObserver::class,
])]
class Invoice extends BaseModel
{
    use ArchiveModelTrait;
    use HasFactory;
    use SetSchemaTrait;
    use SoftDeletes;

    protected $fillable = [
        'batch_id',
        'nature_operation_id',
        'people_issuer_id',
        'people_recipient_id',
        'type_environment',
    ];
    protected $casts = [
        'batch_id'            => 'integer',
        'nature_operation_id' => 'integer',
        'people_issuer_id'    => 'integer',
        'people_recipient_id' => 'integer',
        'type_environment'    => 'string',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    | Here you may specify the relationships that the model should have with
    |
    */

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function natureOperation(): BelongsTo
    {
        return $this->belongsTo(NatureOperation::class);
    }

    public function peopleIssuer(): BelongsTo
    {
        return $this->belongsTo(People::class, 'people_issuer_id');
    }

    public function peopleRecipient(): BelongsTo
    {
        return $this->belongsTo(People::class, 'people_recipient_id');
    }

    /**
     * xml_assign.
     */
    public function xmlAssign(): MorphMany
    {
        return $this->archives()
            ->where('name', ArchiveEnum::NAME_XML_ASSIGN_FILE);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function invoicePayments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class);
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
    public function byBatchId($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'batch_id'), fn ($query, $params) => $query->whereIn('batch_id', $params));
    }

    #[Scope]
    public function byNatureOperationId($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'nature_operation_id'), fn ($query, $params) => $query->whereIn('nature_operation_id', $params));
    }

    #[Scope]
    public function byPeopleIssuerId($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'people_issuer_id'), fn ($query, $params) => $query->whereIn('people_issuer_id', $params));
    }

    #[Scope]
    public function byPeopleRecipientId($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'people_recipient_id'), fn ($query, $params) => $query->whereIn('people_recipient_id', $params));
    }

    #[Scope]
    public function byTypeEnvironment($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'type_environment'), fn ($query, $params) => $query->whereIn('type_environment', $params));
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
}
