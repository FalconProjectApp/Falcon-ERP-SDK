<?php

namespace FalconERP\Skeleton\Enums\Finance;

use QuantumTecnology\EnumBasicsExtension\BaseEnum;
use Illuminate\Support\Collection;

abstract class PaymentEnum extends BaseEnum
{
    public const METHOD_DESCRIPTION_CREDITCARD_1X  = 'Mastercard 1x'; // Metodo de pagamento de cartão de crédito em 1x
    public const METHOD_DESCRIPTION_CREDITCARD_2X  = 'Mastercard 2x'; // Metodo de pagamento de cartão de crédito em 2x
    public const METHOD_DESCRIPTION_CREDITCARD_6X  = 'Mastercard 6x'; // Metodo de pagamento de cartão de crédito em 6x
    public const METHOD_DESCRIPTION_CREDITCARD_12X = 'Mastercard 12x'; // Metodo de pagamento de cartão de crédito em 12x
    public const METHOD_DESCRIPTION_DEBITCARD      = 'Cartão de Débito'; // Metodo de pagamento de cartão de débito
    public const METHOD_DESCRIPTION_PIX            = 'PIX'; // Metodo de pagamento de PIX
    public const METHOD_DESCRIPTION_A_VISTA        = 'À Vista'; // Metodo de pagamento à vista

    public const METHOD_CREDITCARD = 'credit_card'; // Metodo de pagamento de cartão de crédito
    public const METHOD_DEBITCARD  = 'debit_card'; // Metodo de pagamento de cartão de débito
    public const METHOD_PIX        = 'pix'; // Metodo de pagamento de PIX
    public const METHOD_A_VISTA    = 'a_vista'; // Metodo de pagamento à vista

    public const TYPE_SYSTEM = 'system'; // Tipo de requisição para quando o sistema cria a requisição
    public const TYPE_CLIENT = 'client'; // Tipo de requisição para quando o cliente cria a requisição

    public const STATUS_ACTIVE   = 'active'; // Status ativo
    public const STATUS_INACTIVE = 'inactive'; // Status inativo

    public const FLAG_VISA       = 'visa'; // Bandeira Visa
    public const FLAG_MASTERCARD = 'mastercard'; // Bandeira Mastercard
    public const FLAG_AMEX       = 'amex'; // Bandeira Amex
    public const FLAG_ELO        = 'elo'; // Bandeira Elo
    public const FLAG_HIPERCARD  = 'hipercard'; // Bandeira Hipercard
    public const FLAG_HIPER      = 'hiper'; // Bandeira Hiper
    public const FLAG_DINERS     = 'diners'; // Bandeira Diners
    public const FLAG_DISCOVER   = 'discover'; // Bandeira Discover
    public const FLAG_JCB        = 'jcb'; // Bandeira JCB
    public const FLAG_AURA       = 'aura'; // Bandeira Aura
    public const FLAG_SOROCRED   = 'sorocred'; // Bandeira Sorocred
    public const FLAG_BANESCARD  = 'banescard'; // Bandeira Banescard
    public const FLAG_CABAL      = 'cabal'; // Bandeira Cabal
    public const FLAG_MAESTRO    = 'maestro'; // Bandeira Maestro
    public const FLAG_VALECARD   = 'valecard'; // Bandeira Valecard
    public const FLAG_ALELO      = 'alelo'; // Bandeira Alelo
    public const FLAG_SODEXO     = 'sodexo'; // Bandeira Sodexo
    public const FLAG_VR         = 'vr'; // Bandeira VR
    public const FLAG_TICKET     = 'ticket'; // Bandeira Ticket
    public const FLAG_BENEFICIO  = 'beneficio'; // Bandeira Beneficio
    public const FLAG_CREDZ      = 'credz'; // Bandeira Credz
    public const FLAG_CIELO      = 'cielo'; // Bandeira Cielo
    public const FLAG_REDE       = 'rede'; // Bandeira Rede
    public const FLAG_STONE      = 'stone'; // Bandeira Stone
    public const FLAG_GETNET     = 'getnet'; // Bandeira Getnet
    public const FLAG_PAGSEGURO  = 'pagseguro'; // Bandeira Pagseguro

    /**
     * Return available types.
     */
    public static function methods(): Collection
    {
        return new Collection(static::filterConstants('METHOD'));
    }

    public static function flags(): Collection
    {
        return new Collection(static::filterConstants('FLAG'));
    }
}
