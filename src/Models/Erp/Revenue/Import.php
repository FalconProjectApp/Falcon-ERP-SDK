<?php

namespace FalconERP\Skeleton\Models\Erp\Revenue;

use QuantumTecnology\ModelBasicsExtension\BaseModel;
use QuantumTecnology\ModelBasicsExtension\Traits\SetSchemaTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
