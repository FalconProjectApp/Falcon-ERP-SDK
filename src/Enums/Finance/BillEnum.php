<?php

namespace FalconERP\Skeleton\Enums\Finance;

use QuantumTecnology\EnumBasicsExtension\BaseEnum;
use Illuminate\Support\Collection;

abstract class BillEnum extends BaseEnum
{
    /**
     * Status.
     * Status é o estado de um evento financeiro.
     */
    public const STATUS_OPEN         = 'open'; // Em aberto
    public const STATUS_PAID         = 'paid'; // Pago
    public const STATUS_PAID_PARTIAL = 'paid_partial'; // Pago parcialmente
    public const STATUS_CANCELED     = 'canceled'; // Cancelado

    /**
     * Type.
     * Tipo é a classificação de um evento financeiro.
     */
    public const TYPE_RECEIVE = 'receive'; // Receber
    public const TYPE_PAY     = 'pay'; // Pagar

    /**
     * Periodicity.
     * Periodicidade é o intervalo de tempo entre as ocorrências de um evento recorrente.
     */
    public const PERIODICITY_DAILY   = 'daily'; // Diário
    public const PERIODICITY_WEEKLY  = 'weekly'; // Semanal
    public const PERIODICITY_MONTHLY = 'monthly'; // Mensal
    public const PERIODICITY_ANNUAL  = 'annual'; // Anual

    public const PERIODICITIES_TIMESTAMP = [
        self::PERIODICITY_DAILY   => 'day',
        self::PERIODICITY_WEEKLY  => 'week',
        self::PERIODICITY_MONTHLY => 'month',
        self::PERIODICITY_ANNUAL  => 'year',
    ];

    /**
     * Repetition.
     * Repetição é a ação de repetir um evento recorrente.
     */
    public const REPETITION_NOT_RECURRENT = 'not_recurrent'; // Não recorrente
    public const REPETITION_RECURRENT     = 'recurrent'; // Recorrente
    public const REPETITION_FIXED         = 'fixed'; // Fixo

    /**
     * Return available periodicities.
     */
    public static function periodicities(): Collection
    {
        return new Collection(static::filterConstants('PERIODICITY'));
    }

    /**
     * Return available periodicities.
     */
    public static function repetitions(): Collection
    {
        return new Collection(static::filterConstants('REPETITION'));
    }
}
