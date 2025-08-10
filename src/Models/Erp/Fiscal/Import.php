<?php

namespace FalconERP\Skeleton\Models\Erp\Fiscal;

use FalconERP\Skeleton\Observers\CacheObserver;
use FalconERP\Skeleton\Observers\NotificationObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

#[ObservedBy([
    CacheObserver::class,
    NotificationObserver::class,
])]
class Import extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use SetSchemaTrait;

    protected $fillable = [
        'issuer_people_id',
        'recipient_people_id',
        'access_key',
        'type',
        'value',
        'importer_people_id',
        'status',
        'data',
        'observation',
    ];

    #[Scope]
    public function byStatus($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'status'), fn ($query, $params) => $query->whereIn('status', $params));
    }

    #[Scope]
    public function byIssuerPeopleId($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'issuer_people_id'), fn ($query, $params) => $query->whereIn('issuer_people_id', $params));
    }

    #[Scope]
    public function byRecipientPeopleId($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'recipient_people_id'), fn ($query, $params) => $query->whereIn('recipient_people_id', $params));
    }

    protected function dataStd(): Attribute
    {
        $xml = $this->data ?? null;

        if ($xml) {
            $xml = simplexml_load_string(base64_decode($xml));

            $xml = match (true) {
                isset($xml->Nfse)      => $xml,
                isset($xml->ListaNfse) => $xml->ListaNfse->CompNfse,
                isset($xml->NFe)       => $xml,
                default                => null,
            };
        }

        return new Attribute(
            get: fn () => $xml,
        );
    }

    /**
     * Get the data.
     */
    protected function data(): Attribute
    {
        return new Attribute(
            get: fn ($value) => json_decode($value),
            set: fn ($value) => json_encode($value)
        );
    }
}
