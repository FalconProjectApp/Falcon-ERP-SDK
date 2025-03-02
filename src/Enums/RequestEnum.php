<?php

namespace FalconERP\Skeleton\Enums;

use QuantumTecnology\EnumBasicsExtension\BaseEnum;
use Illuminate\Support\Collection;

abstract class RequestEnum extends BaseEnum
{
    public const REQUEST_TYPE_INPUT   = 'input'; // Requisição de entrada
    public const REQUEST_TYPE_OUTPUT  = 'output'; // Requisição de saída
    public const REQUEST_TYPE_SPECIAL = 'special'; // Requisição especiais que podem ser de entrada e/ou saída
    public const REQUEST_TYPE_NEUTRAL = 'neutral'; // Requisição neutras que não alteram o estoque e serve apenas para controle

    public const TYPE_SYSTEM = 'system'; // Tipo de requisição para quando o sistema cria a requisição
    public const TYPE_CLIENT = 'client'; // Tipo de requisição para quando o cliente cria a requisição

    public const REQUEST_DESCRIPTION_SIMPLE_ENTRY     = 'Entrada Simples'; // Requisição de entrada simples
    public const REQUEST_DESCRIPTION_SIMPLE_EXIT      = 'Saida Simples'; // Requisição de saída simples
    public const REQUEST_DESCRIPTION_STOCK_CONVERSION = 'Conversão de Estoque'; // Requisição utilizada para converter um produto em outro
    public const REQUEST_DESCRIPTION_STOCK_PRODUCTION = 'Produção de Estoque'; // Requisição utilizada para converter matéria prima em produto final
    public const REQUEST_DESCRIPTION_STOCK_TRANSFER   = 'Transferência de Estoque'; // Requisição utilizada para transferir estoque de um local para outro
    public const REQUEST_DESCRIPTION_STOCK_INVENTORY  = 'Inventário de Estoque'; // Requisição utilizada para realizar o inventário do estoque
    public const REQUEST_DESCRIPTION_STOCK_LOSS       = 'Perda de Estoque'; // Requisição utilizada para dar baixa em produtos perdidos
    public const REQUEST_DESCRIPTION_STOCK_FOUND      = 'Achado de Estoque'; // Requisição utilizada para dar entrada em produtos encontrados
    public const REQUEST_DESCRIPTION_STOCK_RETURN     = 'Retorno de Estoque'; // Requisição utilizada para dar entrada em produtos retornados
    public const REQUEST_DESCRIPTION_PURCHASE_ORDER   = 'Ordem de Compra'; // Requisição utilizada para dar entrada em produtos comprados
    public const REQUEST_DESCRIPTION_SALES_ORDER      = 'Ordem de Venda'; // Requisição utilizada para dar saída em produtos vendidos

    public const REQUEST_STATUS_OPEN       = 'open'; // Status da requisição aberta
    public const REQUEST_STATUS_AUTHORIZED = 'authorized'; // Status da requisição autorizada
    public const REQUEST_STATUS_DENIED     = 'denied'; // Status da requisição negada
    public const REQUEST_STATUS_FINISHED   = 'finished'; // Status da requisição finalizada
    public const REQUEST_STATUS_PENDING    = 'pending'; // Status da requisição pendente
    public const REQUEST_STATUS_APPROVED   = 'approved'; // Status da requisição aprovada
    public const REQUEST_STATUS_REJECTED   = 'rejected'; // Status da requisição rejeitada
    public const REQUEST_STATUS_CANCELED   = 'canceled'; // Status da requisição cancelada

    /**
     * Return available types.
     */
    public static function requestTypes(): Collection
    {
        return new Collection(static::filterConstants('REQUEST_TYPE'));
    }

    public static function requestStatus(): Collection
    {
        return new Collection(static::filterConstants('REQUEST_STATUS'));
    }

    public static function requestDescriptions(): Collection
    {
        return new Collection(static::filterConstants('REQUEST_DESCRIPTION'));
    }
}
