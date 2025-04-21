<?php

namespace FalconERP\Skeleton\Enums\People;

use QuantumTecnology\EnumBasicsExtension\BaseEnum;

abstract class PeopleDocumentEnum extends BaseEnum
{
    public const TYPE_CPF                 = 'cpf';
    public const TYPE_CNH                 = 'cnh';
    public const TYPE_PASSPORT            = 'passport';
    public const TYPE_CNPJ                = 'cnpj';
    public const TYPE_IE                  = 'ie';
    public const TYPE_RG                  = 'Rg';
    public const TYPE_TITULO_ELEITORAL    = 'Titulo Eleitoral';
    public const TYPE_CTPS                = 'CTPS';
    public const TYPE_CERTIFICADO_MILITAR = 'Certificado Militar';
    public const TYPE_IM                  = 'im';
}
