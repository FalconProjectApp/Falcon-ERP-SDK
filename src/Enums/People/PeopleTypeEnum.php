<?php

declare(strict_types=1);

namespace FalconERP\Skeleton\Enums\People;

enum PeopleTypeEnum: string
{
    case TYPE_ADMIN          = 'admin';
    case TYPE_FUNCIONARIO    = 'funcionario';
    case TYPE_GERENTE        = 'gerente';
    case TYPE_CLIENTE        = 'cliente';
    case TYPE_FORNECEDOR     = 'fornecedor';
    case TYPE_VENDEDOR       = 'vendedor';
    case TYPE_TRANSPORTADORA = 'transportadora';
    case TYPE_CONTRATANTE    = 'contratante';
    case TYPE_CONTRATADO     = 'contratado';
    case TYPE_CEO            = 'ceo';
    case TYPE_COLABORADOR    = 'colaborador';
}
