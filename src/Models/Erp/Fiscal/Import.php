<?php

declare(strict_types = 1);

namespace FalconERP\Skeleton\Models\Erp\Fiscal;

use FalconERP\Skeleton\Models\Erp\People\People;
use FalconERP\Skeleton\Observers\NotificationObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Observers\CacheObserver;
use QuantumTecnology\ModelBasicsExtension\Traits\ActionTrait;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;

#[ObservedBy([
    CacheObserver::class,
    NotificationObserver::class,
])]
class Import extends BaseModel
{
    use ActionTrait;
    use HasFactory;
    use SetSchemaTrait;
    use SoftDeletes;

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
    public function byStatuses($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'statuses'),
            fn ($query, $params) => $query->whereIn('status', $params));
    }

    #[Scope]
    public function byIssuerPeopleIds($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'issuer_people_ids'),
            fn ($query, $params) => $query->whereIn('issuer_people_id', $params));
    }

    #[Scope]
    public function byRecipientPeopleIds($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'recipient_people_ids'),
            fn ($query, $params) => $query->whereIn('recipient_people_id', $params));
    }

    #[Scope]
    public function byImporterPeopleIds($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'importer_people_ids'),
            fn ($query, $params) => $query->whereIn('importer_people_id', $params));
    }

    #[Scope]
    public function byTypes($query, array $params = [])
    {
        return $query->when($this->filtered($params, 'types'),
            fn ($query, $params) => $query->whereIn('type', $params));
    }

    public function peopleIssuer()
    {
        return $this->belongsTo(
            related: People::class,
            foreignKey: 'issuer_people_id',
        );
    }

    public function peopleRecipient()
    {
        return $this->belongsTo(
            related: People::class,
            foreignKey: 'recipient_people_id',
        );
    }

    public function peopleImporter()
    {
        return $this->belongsTo(
            related: People::class,
            foreignKey: 'importer_people_id',
        );
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
}
